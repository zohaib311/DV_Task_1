<?php

namespace App\Services\Assessment;

use App\Models\Academic\CourseOffering;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentComponent;
use App\Models\Assessment\AssessmentSubmission;
use App\Models\Assessment\StudentAssessmentSubmission;
use App\Models\User;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Notifications\AcademicNotificationService;

class AssessmentWorkflow
{
    public function assigned(User $user, int $id): CourseOffering
    {
        $workspace = app(TeacherWorkspace::class);

        return $workspace->offerings($workspace->profile($user))->with('term')->findOrFail($id);
    }

    public function approveScheme(User $user, int $id, array $allocations): void
    {
        abort_unless($user->can('offerings.manage'), 403);
        DB::transaction(function () use ($user, $id, $allocations) {
            $offering = CourseOffering::with('term')->lockForUpdate()->findOrFail($id);
            if ($offering->assessment_scheme_approved_at || ! in_array($offering->status, ['planned', 'active']) || $offering->term->status === 'closed') {
                $this->invalid('scheme', 'This scheme is locked or the offering is no longer configurable.');
            }
            if ($offering->enrollmentCourses()->whereHas('enrollment.semesterResult')->exists()) {
                $this->invalid('scheme', 'An existing semester result uses this offering. Keep its saved scheme and configure a new offering instead.');
            }
            $sum = array_sum(array_map(fn ($value) => (int) round((float) $value * 100), $allocations));
            if ($sum !== (int) round($offering->total_marks * 100)) {
                $this->invalid('allocations', 'All component allocations must equal the saved course total ('.$offering->total_marks.').');
            }
            if ((int) round($allocations['attendance'] * 100) !== (int) round($offering->attendance_marks * 100)) {
                $this->invalid('allocations.attendance', 'Attendance must keep the existing saved allocation ('.$offering->attendance_marks.').');
            }
            foreach (AssessmentComponent::CODES as $code) {
                if ($allocations[$code] > 0) {
                    $offering->assessmentComponents()->create(['code' => $code, 'allocation' => $allocations[$code]]);
                }
            }
            $offering->forceFill(['assessment_scheme_approved_at' => now(), 'assessment_scheme_approved_by' => $user->id])->save();
            $this->audit($offering, $user, 'scheme_approved', null, $allocations);
        });
    }

    public function saveDefinition(User $user, int $offeringId, array $data, ?int $assessmentId = null): Assessment
    {
        abort_unless($user->can('assessments.manage-assigned'), 403);

        return DB::transaction(function () use ($user, $offeringId, $data, $assessmentId) {
            $offering = $this->lockedAssigned($user, $offeringId);
            $this->writable($offering);
            $component = $offering->assessmentComponents()->findOrFail($data['component_id']);
            if ($component->code === 'attendance') {
                $this->invalid('component_id', 'Attendance comes from recorded sessions, not manual assessments.');
            }
            $assessment = $assessmentId ? Assessment::whereHas('component', fn ($q) => $q->where('course_offering_id', $offering->id))->findOrFail($assessmentId) : new Assessment;
            if ($assessment->exists && ($assessment->marks()->whereNotNull('obtained')->exists() || $assessment->revision !== (int) $data['revision'])) {
                $this->invalid('assessment', 'A marked assessment definition is locked. For an unmarked assessment, reload before editing.');
            }
            if ($data['held_on'] < $offering->term->starts_on->toDateString() || $data['held_on'] > $offering->term->ends_on->toDateString()) {
                $this->invalid('held_on', 'The assessment date must be within the teaching term.');
            }
            $data['submission_required'] = (bool) ($data['submission_required'] ?? false);
            $data['submissions_due_at'] = $data['submission_required'] ? ($data['submissions_due_at'] ?? null) : null;
            if ($data['submission_required'] && ! $data['submissions_due_at']) {
                $this->invalid('submissions_due_at', 'Choose a submission deadline.');
            }
            if ($data['submissions_due_at'] && (substr($data['submissions_due_at'], 0, 10) < $offering->term->starts_on->toDateString() || substr($data['submissions_due_at'], 0, 10) > $offering->term->ends_on->toDateString())) {
                $this->invalid('submissions_due_at', 'The submission deadline must be within the teaching term.');
            }
            $used = $component->assessments()->when($assessment->exists, fn ($q) => $q->whereKeyNot($assessment->id))->sum('weight');
            if ((int) round(($used + $data['weight']) * 100) > (int) round($component->allocation * 100)) {
                $this->invalid('weight', 'Assessment weights exceed this component allocation. Remaining: '.round($component->allocation - $used, 2).'.');
            }
            if ($component->assessments()->where('title', $data['title'])->when($assessment->exists, fn ($q) => $q->whereKeyNot($assessment->id))->exists()) {
                $this->invalid('title', 'This component already has an assessment with that title.');
            }
            $before = $assessment->exists ? $assessment->toArray() : null;
            $assessment->fill(collect($data)->only(['title', 'instructions', 'question_file_path', 'question_original_filename', 'question_mime_type',
                'question_file_size', 'held_on', 'maximum', 'weight', 'submission_required', 'submissions_due_at'])->all());
            $assessment->assessment_component_id = $component->id;
            $assessment->revision = $assessment->exists ? $assessment->revision + 1 : 1;
            $assessment->save();
            $this->audit($offering, $user, 'assessment_saved', $before, $assessment->toArray());
            $notifications = app(AcademicNotificationService::class);
            $offering->enrollmentCourses()->with('enrollment.student.user')->get()->each(function ($course) use ($assessment, $offering, $notifications, $before) {
                $notifications->user($course->enrollment->student->user,
                    $before ? 'Assessment updated' : 'New assessment available',
                    "{$assessment->title} for {$offering->course_code} is scheduled on {$assessment->held_on->format('d M Y')}.",
                    route('student.assessments.show', $course), 'assessment', ['assessment_id' => $assessment->id, 'course_offering_id' => $offering->id]);
            });

            return $assessment;
        });
    }

    public function saveMarks(User $user, int $offeringId, int $assessmentId, array $data): void
    {
        abort_unless($user->can('marks.manage-assigned'), 403);
        DB::transaction(function () use ($user, $offeringId, $assessmentId, $data) {
            $offering = $this->lockedAssigned($user, $offeringId);
            $this->writable($offering);
            $assessment = Assessment::whereHas('component', fn ($q) => $q->where('course_offering_id', $offeringId))->findOrFail($assessmentId);
            if ($assessment->held_on->isAfter(today()) || $assessment->revision !== (int) $data['revision']) {
                $this->invalid('assessment', 'Future assessments cannot be marked. If another teacher edited this assessment, reload it before saving.');
            }
            $roster = $offering->enrollmentCourses()->pluck('id');
            $submitted = collect($data['marks'])->keyBy('course_id');
            if ($submitted->count() !== $roster->count() || $roster->diff($submitted->keys())->isNotEmpty()) {
                $this->invalid('marks', 'Submit exactly the current enrolled student roster. Reload if enrollment changed.');
            }
            $before = $assessment->marks()->orderBy('student_enrollment_course_id')->get()->toArray();
            $existing = $assessment->marks()->get()->keyBy('student_enrollment_course_id');
            $correction = false;
            foreach ($submitted as $id => $row) {
                $score = $row['obtained'] ?? null;
                if ($score !== null && (float) $score > (float) $assessment->maximum) {
                    $this->invalid('marks', 'Each score must be between zero and '.$assessment->maximum.'.');
                }
                $prior = $existing->get($id)?->obtained;
                $correction = $correction || ($prior !== null && ($score === null || (float) $prior !== (float) $score));
            }
            $reason = trim($data['reason'] ?? '');
            if ($correction && strlen($reason) < 5) {
                $this->invalid('reason', 'Provide a correction reason of at least five characters when changing saved marks.');
            }
            foreach ($submitted as $id => $row) {
                $assessment->marks()->updateOrCreate(['student_enrollment_course_id' => $id], ['obtained' => $row['obtained'] ?? null]);
                $feedback = trim($row['feedback'] ?? '');
                if ($feedback !== '') {
                    StudentAssessmentSubmission::where('assessment_id', $assessment->id)->where('student_enrollment_course_id', $id)
                        ->update(['teacher_feedback' => $feedback, 'status' => 'reviewed', 'reviewed_at' => now()]);
                }
            }
            $assessment->increment('revision');
            $this->audit($offering, $user, 'marks_saved', $before, ['assessment_id' => $assessment->id, 'marks' => $assessment->marks()->get()->toArray()], $reason ?: null);
        });
    }

    public function releaseMarks(User $user, int $offeringId, int $assessmentId): void
    {
        abort_unless($user->can('marks.manage-assigned'), 403);
        DB::transaction(function () use ($user, $offeringId, $assessmentId) {
            $offering = $this->lockedAssigned($user, $offeringId);
            $this->writable($offering);
            $assessment = Assessment::whereHas('component', fn ($query) => $query->where('course_offering_id', $offering->id))->lockForUpdate()->findOrFail($assessmentId);
            $expected = $offering->enrollmentCourses()->count();
            if ($expected === 0 || $assessment->marks()->whereNotNull('obtained')->count() !== $expected) {
                $this->invalid('assessment', 'Enter marks for every enrolled student before releasing this assessment.');
            }
            if (! $assessment->marks_released_at) {
                $assessment->update(['marks_released_at' => now()]);
                $this->audit($offering, $user, 'assessment_marks_released', null, ['assessment_id' => $assessment->id, 'released_at' => $assessment->marks_released_at]);
                $notifications = app(AcademicNotificationService::class);
                $offering->enrollmentCourses()->with('enrollment.student.user')->get()->each(function ($course) use ($assessment, $offering, $notifications) {
                    $notifications->user($course->enrollment->student->user, 'Assessment marks released',
                        "Marks for {$assessment->title} in {$offering->course_code} are now available.",
                        route('student.assessments.show', $course), 'marks', ['assessment_id' => $assessment->id, 'course_offering_id' => $offering->id]);
                });
            }
        });
    }

    public function submit(User $user, int $id): AssessmentSubmission
    {
        abort_unless($user->can('results.submit'), 403);

        return DB::transaction(function () use ($user, $id) {
            $offering = $this->lockedAssigned($user, $id);
            $this->writable($offering);
            $snapshot = app(AssessmentCalculator::class)->preview($offering);
            if (! $snapshot['complete']) {
                throw ValidationException::withMessages(['submission' => $snapshot['issues']]);
            }
            $submission = $offering->assessmentSubmissions()->create(['submitted_by' => $user->id, 'snapshot' => $snapshot, 'status' => 'submitted']);
            $offering->update(['status' => 'marks_submitted']);
            $this->audit($offering, $user, 'submitted', null, ['submission_id' => $submission->id]);
            $notifications = app(AcademicNotificationService::class);
            $notifications->users($notifications->academicStaff(), 'Course marks awaiting review',
                "{$offering->course_code} marks were submitted for academic review.",
                route('academic.assessment-reviews.show', $submission), 'review', ['submission_id' => $submission->id], $user->id);

            return $submission;
        });
    }

    public function review(User $user, int $submissionId, string $decision, string $reason): void
    {
        abort_unless($user->can('assessments.review'), 403);
        DB::transaction(function () use ($user, $submissionId, $decision, $reason) {
            $submission = AssessmentSubmission::findOrFail($submissionId);
            $offering = CourseOffering::with('term')->lockForUpdate()->findOrFail($submission->course_offering_id);
            $submission->refresh();
            if ($submission->submitted_by === $user->id || $offering->teachers()->where('user_id', $user->id)->exists()) {
                abort(403, 'Assigned teachers cannot review their own class submission.');
            }
            if ($submission->status !== 'submitted' || $offering->status !== 'marks_submitted' || $offering->term->status !== 'active') {
                $this->invalid('review', 'This submission is no longer pending review in an active term.');
            }
            $this->assertEnrollmentHistoryOpen($offering);
            $submission->update(['status' => $decision, 'reviewed_by' => $user->id, 'review_note' => $reason, 'reviewed_at' => now()]);
            $offering->update(['status' => $decision === 'approved' ? 'reviewed' : 'active']);
            $this->audit($offering, $user, $decision, null, ['submission_id' => $submission->id], $reason);
            $notifications = app(AcademicNotificationService::class);
            $notifications->users($notifications->assignedTeachers($offering), 'Course review '.($decision === 'approved' ? 'approved' : 'returned'),
                "{$offering->course_code} marks were {$decision}. {$reason}",
                route('teaching.assessments.show', $offering), 'review', ['submission_id' => $submission->id], $user->id);
        });
    }

    public function writable(CourseOffering $offering): void
    {
        if (! $offering->assessment_scheme_approved_at || $offering->status !== 'active' || $offering->term->status !== 'active') {
            $this->invalid('offering', 'An approved scheme and active offering/term are required. Submitted or reviewed marks are locked.');
        }
        $this->assertEnrollmentHistoryOpen($offering);
    }

    private function assertEnrollmentHistoryOpen(CourseOffering $offering): void
    {
        if ($offering->enrollmentCourses()->whereHas('enrollment', fn ($q) => $q->where('status', '!=', 'active')->orWhereHas('semesterResult', fn ($q) => $q->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])))->exists()) {
            $this->invalid('offering', 'This class includes finalized enrollment history and is read-only.');
        }
    }

    private function lockedAssigned(User $user, int $id): CourseOffering
    {
        $this->assigned($user, $id);

        return CourseOffering::with('term')->lockForUpdate()->findOrFail($id);
    }

    private function audit(CourseOffering $offering, User $user, string $action, ?array $before, array $after, ?string $reason = null): void
    {
        $offering->assessmentAudits()->create(['user_id' => $user->id, 'action' => $action, 'reason' => $reason, 'before' => $before, 'after' => $after]);
    }

    private function invalid(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}
