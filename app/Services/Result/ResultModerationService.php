<?php

namespace App\Services\Result;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use App\Services\AcademicResultCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ResultModerationService
{
    public function __construct(private FlexibleResultCalculator $calculator, private AcademicResultCalculator $academic) {}

    public function preview(StudentSemesterEnrollment $enrollment): array
    {
        $enrollment->load(['courses.offering', 'term']);
        $issues = [];
        $items = [];
        if ($enrollment->status !== 'active' || $enrollment->term?->status !== 'active') {
            $issues[] = 'Initial review requires an active enrollment and academic term.';
        }
        foreach ($enrollment->courses as $course) {
            $offering = $course->offering;
            $submission = $offering?->assessmentSubmissions()->latest('id')->first();
            if (! $offering?->assessment_scheme_approved_at || ! in_array($offering->status, ['reviewed', 'completed'], true) || $submission?->status !== 'approved') {
                $issues[] = $course->course_code.': an approved teacher submission is required.';

                continue;
            }
            $evidence = $submission->snapshot;
            $matches = collect($evidence['students'] ?? [])->where('student_enrollment_course_id', $course->id);
            $student = $matches->first();
            if ($matches->count() !== 1 || (int) $student['student_id'] !== (int) $enrollment->student_id || (int) $student['enrollment_id'] !== (int) $enrollment->id
                || (int) $evidence['offering_id'] !== (int) $offering->id || (int) $evidence['academic_term_id'] !== (int) $enrollment->academic_term_id
                || (int) $evidence['department_id'] !== (int) $enrollment->department_id || (int) $evidence['section_id'] !== (int) $enrollment->section_id
                || (float) $student['maximum'] !== (float) $course->total_marks || (float) $student['credit_hours'] !== (float) $course->credit_hours || ! ($evidence['complete'] ?? false)) {
                $issues[] = $course->course_code.': the submitted evidence does not match this enrollment.';

                continue;
            }
            $snapshot = ['scheme' => $evidence['scheme'], 'student' => $student, 'grading_policy' => $this->calculator->policy(),
                'offering_id' => $offering->id, 'academic_term_id' => $evidence['academic_term_id'], 'scheme_approved_at' => $evidence['scheme_approved_at'],
                'submission_id' => $submission->id, 'submitted_by' => $submission->submitted_by, 'submitted_at' => $submission->created_at->toIso8601String(),
                'reviewed_by' => $submission->reviewed_by, 'reviewed_at' => $submission->reviewed_at?->toIso8601String()];
            try {
                $items[] = array_merge(['student_enrollment_course_id' => $course->id, 'course_id' => $course->course_id, 'course_code' => $course->course_code,
                    'course_name' => $course->course_name, 'credit_hours' => (float) $course->credit_hours, 'assessment_submission_id' => $submission->id],
                    $this->calculator->course($snapshot, $course->total_marks));
            } catch (ValidationException $error) {
                $issues[] = $course->course_code.': '.$error->getMessage();
            }
        }
        if ($enrollment->courses->isEmpty()) {
            $issues[] = 'No enrolled courses were found.';
        }

        return ['items' => $items, 'issues' => $issues, 'summary' => $issues ? null : $this->calculator->semester($items),
            'token' => hash('sha256', json_encode([$enrollment->id, $items, $issues]))];
    }

    public function approve(User $user, StudentSemesterEnrollment $enrollment, string $token, string $reason): SemesterResult
    {
        abort_unless($user->can('results.approve'), 403);

        return DB::transaction(function () use ($user, $enrollment, $token, $reason) {
            $this->lock($enrollment);
            $this->independent($user, $enrollment);
            $this->check(! $enrollment->semesterResult()->exists(), 'A result already exists. Open its saved sheet instead.');
            $preview = $this->preview($enrollment);
            $this->check($preview['issues'] === [], implode(' ', $preview['issues']));
            $this->check(hash_equals($preview['token'], $token), 'The preview changed. Reload and review the latest sheet.');
            $result = SemesterResult::create(array_merge($preview['summary'], ['student_semester_enrollment_id' => $enrollment->id, 'student_id' => $enrollment->student_id,
                'source' => 'teacher', 'status' => 'Draft', 'reviewed_by' => $user->id, 'reviewed_at' => now(), 'revision' => 1]));
            $result->items()->createMany($preview['items']);
            $this->audit($result, $user, 'semester_approved', $reason, null);

            return $result;
        }, 3);
    }

    public function publish(User $user, StudentSemesterEnrollment $enrollment, int $revision): SemesterResult
    {
        abort_unless($user->can('results.publish'), 403);

        return DB::transaction(function () use ($user, $enrollment, $revision) {
            $offerings = $this->lock($enrollment);
            $this->independent($user, $enrollment);
            $result = $this->result($enrollment, $revision);
            $this->check(! $result->published_at && $result->reviewed_at !== null, 'This result is not awaiting publication.');
            $this->check($enrollment->status === 'active' && $enrollment->term?->status === 'active', 'Publication requires an active enrollment and term.');
            $before = $this->snapshot($result);
            $items = $result->items->map(fn ($item) => array_merge($item->toArray(), $this->calculator->course($item->assessment_snapshot, $item->total_marks)))->all();
            $result->update(array_merge($this->calculator->semester($items), ['published_at' => now(), 'revision' => $revision + 1]));
            $this->academic->recalculatePublishedCgpas($enrollment->student_id);
            foreach ($offerings as $offering) {
                if (! $offering->enrollmentCourses()->whereDoesntHave('enrollment.semesterResult', fn ($query) => $query->whereNotNull('published_at'))->exists()) {
                    $offering->update(['status' => 'completed']);
                    $offering->assessmentAudits()->create(['user_id' => $user->id, 'action' => 'offering_completed', 'reason' => 'All enrolled semester results are published.', 'before' => ['status' => 'reviewed'], 'after' => ['status' => 'completed', 'semester_result_id' => $result->id]]);
                }
            }
            $this->audit($result, $user, 'semester_published', 'Published the approved semester sheet.', $before);
            $enrollment->student->user?->notify(new AcademicUpdateNotification(
                'Semester result published',
                "Your {$enrollment->semester} result is now available.",
                route('student.results.show', $result)
            ));

            return $result->refresh();
        }, 3);
    }

    public function correct(User $user, StudentSemesterEnrollment $enrollment, int $revision, array $marks, string $reason): SemesterResult
    {
        return DB::transaction(function () use ($user, $enrollment, $revision, $marks, $reason) {
            $this->lock($enrollment);
            $result = $this->result($enrollment, $revision);
            Gate::forUser($user)->authorize('update', $result);
            abort_unless($user->can('results.publish') && $user->can('results.edit-published'), 403);
            $this->independent($user, $enrollment);
            $this->check($result->published_at !== null, 'Only a published result can use the correction workflow.');
            $this->check(mb_strlen(trim($reason)) >= 5, 'A correction reason is required.');
            $before = $this->snapshot($result);
            $expected = [];
            $changed = false;
            foreach ($result->items as $item) {
                $snapshot = $item->assessment_snapshot;
                foreach ($snapshot['student']['assessments'] as &$assessment) {
                    $key = $item->id.'_'.$assessment['assessment_id'];
                    $expected[] = $key;
                    $this->check(array_key_exists($key, $marks) && is_numeric($marks[$key]), 'Every saved assessment must have a mark.');
                    $changed = $changed || (float) $marks[$key] !== (float) $assessment['obtained'];
                    $assessment['obtained'] = $marks[$key];
                }
                unset($assessment);
                $item->update($this->calculator->course($snapshot, $item->total_marks));
            }
            $this->check(count($expected) === count($marks) && ! array_diff(array_keys($marks), $expected), 'The correction contains an unknown assessment.');
            $this->check($changed, 'No assessment marks changed.');
            $result->load('items');
            $result->update(array_merge($this->calculator->semester($result->items->toArray()), ['revision' => $revision + 1]));
            $this->academic->recalculatePublishedCgpas($enrollment->student_id);
            $this->audit($result, $user, 'published_correction', $reason, $before);
            $enrollment->student->user?->notify(new AcademicUpdateNotification(
                'Published result updated',
                "Your {$enrollment->semester} result has an authorized correction.",
                route('student.results.show', $result)
            ));

            return $result->refresh();
        }, 3);
    }

    /** Same student -> enrollment -> term -> sorted offerings lock order as enrollment. */
    private function lock(StudentSemesterEnrollment $enrollment)
    {
        Student::whereKey($enrollment->student_id)->lockForUpdate()->firstOrFail();
        StudentSemesterEnrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
        AcademicTerm::whereKey($enrollment->academic_term_id)->lockForUpdate()->first();
        $offerings = CourseOffering::whereIn('id', $enrollment->courses()->select('course_offering_id'))->orderBy('id')->lockForUpdate()->get();
        $enrollment->refresh()->load('term');

        return $offerings;
    }

    private function independent(User $user, StudentSemesterEnrollment $enrollment): void
    {
        $teacherId = $user->teacherProfile?->id;
        abort_if($teacherId && CourseOffering::whereIn('id', $enrollment->courses()->select('course_offering_id'))->whereHas('teachers', fn ($query) => $query->where('teachers.id', $teacherId))->exists(), 403, 'Assigned teachers cannot moderate or publish their own classes.');
    }

    private function result(StudentSemesterEnrollment $enrollment, int $revision): SemesterResult
    {
        $result = $enrollment->semesterResult()->lockForUpdate()->first();
        $this->check($result !== null && $result->source === 'teacher', 'An approved teacher-based semester sheet is required.');
        $this->check($result->revision === $revision, 'This result changed. Reload before continuing.');

        return $result->load('items');
    }

    private function snapshot(SemesterResult $result): array
    {
        return $result->fresh('items')->toArray();
    }

    private function audit(SemesterResult $result, User $user, string $action, string $reason, ?array $before): void
    {
        $result->audits()->create(['updated_by' => $user->id, 'action' => $action, 'reason' => $reason, 'before' => $before, 'after' => $this->snapshot($result)]);
    }

    private function check(bool $valid, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['moderation' => $message]);
        }
    }
}
