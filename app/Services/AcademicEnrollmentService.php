<?php

namespace App\Services;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResultItem;
use App\Models\Section\Section;
use App\Models\Student;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Notifications\AcademicNotificationService;

class AcademicEnrollmentService
{
    public function availableOfferings(Student $student, AcademicTerm $term, SemesterCurriculum $curriculum): Collection
    {
        $this->validatePlacement($student, $term, $curriculum);
        $base = CourseOffering::with(['teachers', 'curriculumCourse', 'course'])
            ->where('academic_term_id', $term->id)->where('department_id', $student->department_id)
            ->when($student->program_id, fn ($query, $programId) => $query->where('program_id', $programId))
            ->where('section_id', $student->section_id)->where('status', 'active')
            ->whereHas('teachers')->whereHas('course', fn ($query) => $query->where('is_active', true));

        $current = (clone $base)->whereHas('curriculumCourse', fn ($query) => $query->where('semester_curriculum_id', $curriculum->id))->get();
        $failedCourseIds = $this->failedCourseIds($student);
        $repeats = $failedCourseIds->isEmpty() ? collect() : (clone $base)
            ->whereIn('course_id', $failedCourseIds)
            ->whereNotIn('course_id', $current->pluck('course_id'))
            ->get();

        return $current->concat($repeats)->sortBy('course_code')->values();
    }

    public function enroll(array $data, ?StudentSemesterEnrollment $promotion = null): StudentSemesterEnrollment
    {
        return DB::transaction(function () use ($data, $promotion) {
            // Serialize enrollment and promotion requests for the same student.
            $student = Student::lockForUpdate()->findOrFail($promotion?->student_id ?? $data['student_id']);
            if ($promotion) {
                $promotion = StudentSemesterEnrollment::with(['semesterResult', 'term'])->lockForUpdate()->findOrFail($promotion->id);
                $eligibility = $this->promotionEligibility($promotion);
                if (! $eligibility['allowed']) {
                    $this->invalid('promotion', $eligibility['message']);
                }
            }
            if (StudentSemesterEnrollment::where('student_id', $student->id)->where('status', 'active')
                ->when($promotion, fn ($query) => $query->whereKeyNot($promotion->id))->exists()) {
                $this->invalid('student_id', 'This student already has an active enrollment. Use promotion after completing the current semester.');
            }
            $term = AcademicTerm::with('academicYear')->lockForUpdate()->findOrFail($data['academic_term_id']);
            $curriculum = SemesterCurriculum::with('courses')->findOrFail($data['semester_curriculum_id']);
            $this->validatePlacement($student, $term, $curriculum);
            if ($promotion && $curriculum->semester !== $this->nextSemester($promotion->semester)) {
                $this->invalid('semester_curriculum_id', 'Promotion must use the next semester curriculum.');
            }
            if ($promotion?->term && $term->starts_on->lte($promotion->term->starts_on)) {
                $this->invalid('academic_term_id', 'Choose a teaching term that starts after the previous enrollment term.');
            }
            if (StudentSemesterEnrollment::where('student_id', $student->id)->where('academic_year', $term->academicYear->name)
                ->where('semester', $curriculum->semester)->exists()) {
                $this->invalid('semester_curriculum_id', 'This student already has an enrollment for this academic year and semester.');
            }
            $selectedOfferingIds = $data['offering_ids'] ?? [];
            if ($selectedOfferingIds) {
                // Backward compatibility for existing integrations and repeat/improvement workflows.
                $offerings = CourseOffering::with(['curriculumCourse', 'teachers', 'course'])->whereIn('id', $selectedOfferingIds)->orderBy('id')->lockForUpdate()->get();
                if ($offerings->count() !== count($selectedOfferingIds)) {
                    $this->invalid('offering_ids', 'One or more selected offerings are no longer available.');
                }
                $previousCourseIds = $this->previousCourseIds($student);
                foreach ($offerings as $offering) {
                    if ($offering->academic_term_id !== $term->id || $offering->section_id !== $student->section_id ||
                        $offering->department_id !== $student->department_id || $offering->program_id !== $student->program_id || $offering->status !== 'active' ||
                        $offering->teachers->isEmpty() || ! $offering->course->is_active) {
                        $this->invalid('offering_ids', 'Select active, teacher-assigned offerings in the student\'s section and selected term.');
                    }
                    if ($offering->curriculumCourse->semester_curriculum_id !== $curriculum->id && ! $previousCourseIds->contains($offering->course_id)) {
                        $this->invalid('offering_ids', 'A course outside this curriculum must be a previously completed course selected for repeat or improvement.');
                    }
                }
            } else {
                // Normal admission never asks an operator to choose a student's required courses.
                $offerings = CourseOffering::with(['curriculumCourse', 'teachers', 'course'])
                    ->where('academic_term_id', $term->id)->where('department_id', $student->department_id)
                    ->when($student->program_id, fn ($query, $programId) => $query->where('program_id', $programId))
                    ->where('section_id', $student->section_id)->where('status', 'active')->whereHas('teachers')
                    ->whereHas('course', fn ($query) => $query->where('is_active', true))
                    ->whereHas('curriculumCourse', fn ($query) => $query->where('semester_curriculum_id', $curriculum->id)->where('type', 'required'))
                    ->orderBy('course_code')->lockForUpdate()->get();
            }
            $maximumCredits = (float) config('academic.enrollment.maximum_credit_hours', 21);
            if ((float) $offerings->sum('credit_hours') > $maximumCredits) {
                $this->invalid('offering_ids', "The selected course load exceeds the {$maximumCredits} credit-hour limit.");
            }
            $selectedCurriculumCourseIds = $offerings->pluck('curriculum_course_id');
            $missing = $curriculum->courses->where('type', 'required')->reject(fn ($course) => $selectedCurriculumCourseIds->contains($course->id));
            if ($missing->isNotEmpty()) {
                if ($selectedOfferingIds) {
                    $this->invalid('offering_ids', 'Include every required curriculum course: '.$missing->pluck('course_code')->implode(', ').'.');
                }
                $this->invalid('semester_curriculum_id', 'The semester teaching setup is incomplete. Missing active, teacher-assigned classes: '.$missing->pluck('course_code')->implode(', ').'.');
            }
            $enrollment = StudentSemesterEnrollment::create([
                'student_id' => $student->id, 'department_id' => $student->department_id, 'program_id' => $student->program_id, 'section_id' => $student->section_id,
                'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id,
                'academic_year' => $term->academicYear->name, 'semester' => $curriculum->semester,
                'status' => 'active', 'enrolled_at' => today(),
            ]);
            $enrollment->courses()->createMany($offerings->map(fn ($offering) => $offering->only(CurriculumCourse::SNAPSHOT_FIELDS) + [
                'course_offering_id' => $offering->id,
                'registration_type' => $offering->curriculumCourse->semester_curriculum_id === $curriculum->id ? $offering->curriculumCourse->type : 'repeat',
            ])->all());
            $promotion?->update(['status' => 'promoted', 'completed_at' => today()]);
            $student->update(['semester' => $curriculum->semester, 'course_ids' => $offerings->pluck('course_id')->all()]);
            if ($student->user) {
                $student->user->notify(new AcademicUpdateNotification(
                    'Semester enrollment updated',
                    "You are enrolled in {$enrollment->semester} for {$enrollment->academic_year}.",
                    route('student.courses', ['enrollment' => $enrollment->id])
                ));
            }
            $notifications = app(AcademicNotificationService::class);
            foreach ($offerings as $offering) {
                $notifications->users($notifications->assignedTeachers($offering), 'Student roster updated',
                    "{$student->name} ({$student->registration_no}) joined {$offering->course_code} in {$term->name}.",
                    route('teaching.offerings.show', $offering), 'enrollment', ['offering_id' => $offering->id, 'student_id' => $student->id]);
            }
            $notifications->users($notifications->academicStaff(), $promotion ? 'Student promoted' : 'Student enrolled',
                "{$student->name} is enrolled in {$curriculum->semester} for {$term->name}.",
                route('allEnrollments'), 'enrollment', ['enrollment_id' => $enrollment->id]);

            return $enrollment;
        });
    }

    public function promotionEligibility(StudentSemesterEnrollment $enrollment): array
    {
        if ($enrollment->status !== 'active') {
            return ['allowed' => false, 'message' => 'Only the current active enrollment can be promoted.'];
        }
        if ($this->nextSemester($enrollment->semester) === null) {
            return ['allowed' => false, 'message' => 'Semester 8 is the final configured semester.'];
        }
        if (config('academic.promotion.require_published_result', true)) {
            $result = $enrollment->semesterResult;
            if (! $result?->published_at || ! in_array($result->status, ['Pass', 'Fail'], true)) {
                return ['allowed' => false, 'message' => 'A published semester result is required before promotion.'];
            }
        }

        $failed = $enrollment->semesterResult?->items()->where('status', 'Fail')->pluck('course_code')->filter()->values() ?? collect();
        $message = $failed->isEmpty()
            ? 'Promotion check passed. Select the next term and approved semester curriculum.'
            : 'Promotion is allowed with backlog courses: '.$failed->implode(', ').'. Prepare their offerings in the next term if the student will repeat them now.';

        return ['allowed' => true, 'message' => $message, 'failed_courses' => $failed->all()];
    }

    public function nextSemester(string $semester): ?string
    {
        preg_match('/\d+/', $semester, $matches);
        $number = (int) ($matches[0] ?? 0);

        return $number >= 1 && $number < 8 ? 'Semester '.($number + 1) : null;
    }

    private function previousCourseIds(Student $student): Collection
    {
        return StudentEnrollmentCourse::whereHas('enrollment', fn ($query) => $query->where('student_id', $student->id)
            ->whereHas('semesterResult', fn ($result) => $result->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])))->pluck('course_id');
    }

    public function failedCourseIds(Student $student): Collection
    {
        return SemesterResultItem::query()
            ->whereHas('semesterResult', fn ($query) => $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])
                ->whereHas('enrollment', fn ($enrollment) => $enrollment->where('student_id', $student->id)))
            ->orderBy('id')->get(['id', 'course_id', 'status'])->keyBy('course_id')
            ->filter(fn ($item) => $item->status === 'Fail')->keys()->values();
    }

    public function optionalRegistrationOfferings(StudentSemesterEnrollment $enrollment): Collection
    {
        $enrollment->loadMissing(['student', 'term', 'curriculum']);
        if ($enrollment->status !== 'active' || $enrollment->term?->status !== 'active') {
            return collect();
        }
        $failed = $this->failedCourseIds($enrollment->student);
        $registered = $enrollment->courses()->pluck('course_id');

        return CourseOffering::with(['teachers', 'curriculumCourse'])
            ->where('academic_term_id', $enrollment->academic_term_id)
            ->where('department_id', $enrollment->department_id)
            ->where('program_id', $enrollment->program_id)
            ->where('section_id', $enrollment->section_id)
            ->where('status', 'active')->whereHas('teachers')
            ->whereHas('course', fn ($query) => $query->where('is_active', true))
            ->whereNotIn('course_id', $registered)
            ->where(function ($query) use ($enrollment, $failed) {
                $query->whereHas('curriculumCourse', fn ($course) => $course
                    ->where('semester_curriculum_id', $enrollment->semester_curriculum_id)->where('type', 'elective'));
                if ($failed->isNotEmpty()) {
                    $query->orWhereIn('course_id', $failed);
                }
            })->orderBy('course_code')->get();
    }

    public function addOptionalCourses(Student $student, StudentSemesterEnrollment $enrollment, array $offeringIds): void
    {
        DB::transaction(function () use ($student, $enrollment, $offeringIds) {
            Student::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $enrollment = StudentSemesterEnrollment::with(['term', 'student'])->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            if ($enrollment->student_id !== $student->id) {
                abort(404);
            }
            if ($enrollment->status !== 'active' || $enrollment->term?->status !== 'active') {
                $this->invalid('courses', 'Course registration is only available for an active enrollment in an active term.');
            }
            $allowed = $this->optionalRegistrationOfferings($enrollment)->keyBy('id');
            $selected = collect($offeringIds)->unique()->map(fn ($id) => $allowed->get((int) $id));
            if ($selected->contains(null) || $selected->isEmpty()) {
                $this->invalid('courses', 'Select only the available elective or backlog course offerings.');
            }
            $credits = (float) $enrollment->courses()->sum('credit_hours') + (float) $selected->sum('credit_hours');
            $maximum = (float) config('academic.enrollment.maximum_credit_hours', 21);
            if ($credits > $maximum) {
                $this->invalid('courses', "The selected course load is {$credits} credit hours; the maximum is {$maximum}.");
            }
            $failed = $this->failedCourseIds($student);
            $enrollment->courses()->createMany($selected->map(fn ($offering) => $offering->only(CurriculumCourse::SNAPSHOT_FIELDS) + [
                'course_offering_id' => $offering->id,
                'registration_type' => $failed->contains($offering->course_id) ? 'repeat' : 'elective',
            ])->all());
            $student->update(['course_ids' => $enrollment->courses()->pluck('course_id')->all()]);
            $notifications = app(AcademicNotificationService::class);
            $codes = $selected->pluck('course_code')->implode(', ');
            $notifications->user($student->user, 'Course registration updated', "Registered courses: {$codes}.",
                route('student.courses', ['enrollment' => $enrollment->id]), 'registration', ['enrollment_id' => $enrollment->id]);
            foreach ($selected as $offering) {
                $notifications->users($notifications->assignedTeachers($offering), 'Student roster updated',
                    "{$student->name} ({$student->registration_no}) registered in {$offering->course_code}.",
                    route('teaching.offerings.show', $offering), 'registration', ['offering_id' => $offering->id, 'student_id' => $student->id]);
            }
            $notifications->users($notifications->academicStaff(), 'Student course registration',
                "{$student->name} registered: {$codes}.", route('allEnrollments'), 'registration', ['enrollment_id' => $enrollment->id]);
        });
    }

    private function validatePlacement(Student $student, AcademicTerm $term, SemesterCurriculum $curriculum): void
    {
        if (! $student->department_id || ! $student->section_id || ! Section::whereKey($student->section_id)->where('department_id', $student->department_id)->exists()) {
            $this->invalid('student_id', 'The student must belong to a valid department and section before enrollment.');
        }
        if ($term->status !== 'active') {
            $this->invalid('academic_term_id', 'Only active terms accept new enrollments.');
        }
        if ($curriculum->status !== 'approved' || $curriculum->department_id !== $student->department_id || $curriculum->program_id !== $student->program_id) {
            $this->invalid('semester_curriculum_id', 'Select an approved curriculum for the student\'s program and department.');
        }
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
