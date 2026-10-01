<?php

namespace App\Services;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Section\Section;
use App\Models\Student;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicEnrollmentService
{
    public function availableOfferings(Student $student, AcademicTerm $term, SemesterCurriculum $curriculum): Collection
    {
        $this->validatePlacement($student, $term, $curriculum);
        $previousCourseIds = $this->previousCourseIds($student);

        return CourseOffering::with(['teachers', 'curriculumCourse'])
            ->where('academic_term_id', $term->id)->where('department_id', $student->department_id)
            ->where('section_id', $student->section_id)->where('status', 'active')
            ->whereHas('teachers')->whereHas('course', fn ($query) => $query->where('is_active', true))
            ->where(function ($query) use ($curriculum, $previousCourseIds) {
                $query->whereHas('curriculumCourse', fn ($q) => $q->where('semester_curriculum_id', $curriculum->id))
                    ->orWhereIn('course_id', $previousCourseIds);
            })->orderBy('course_code')->get();
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
            $offerings = CourseOffering::with(['curriculumCourse', 'teachers', 'course'])->whereIn('id', $data['offering_ids'])->orderBy('id')->lockForUpdate()->get();
            if ($offerings->count() !== count($data['offering_ids'])) {
                $this->invalid('offering_ids', 'One or more selected offerings are no longer available.');
            }
            $previousCourseIds = $this->previousCourseIds($student);
            foreach ($offerings as $offering) {
                if ($offering->academic_term_id !== $term->id || $offering->section_id !== $student->section_id ||
                    $offering->department_id !== $student->department_id || $offering->status !== 'active' ||
                    $offering->teachers->isEmpty() || ! $offering->course->is_active) {
                    $this->invalid('offering_ids', 'Select active, teacher-assigned offerings in the student\'s section and selected term.');
                }
                if ($offering->curriculumCourse->semester_curriculum_id !== $curriculum->id && ! $previousCourseIds->contains($offering->course_id)) {
                    $this->invalid('offering_ids', 'A course outside this curriculum must be a previously completed course selected for repeat or improvement.');
                }
            }
            $selectedCurriculumCourseIds = $offerings->pluck('curriculum_course_id');
            $missing = $curriculum->courses->where('type', 'required')->reject(fn ($course) => $selectedCurriculumCourseIds->contains($course->id));
            if ($missing->isNotEmpty()) {
                $this->invalid('offering_ids', 'Include every required curriculum course: '.$missing->pluck('course_code')->implode(', ').'. Create any missing active offerings first.');
            }
            $enrollment = StudentSemesterEnrollment::create([
                'student_id' => $student->id, 'department_id' => $student->department_id, 'section_id' => $student->section_id,
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
        if (config('academic.promotion.require_published_pass_result')) {
            $result = $enrollment->semesterResult;
            if (! $result?->published_at || $result->status !== 'Pass') {
                return ['allowed' => false, 'message' => 'A published passing result is required before promotion. Review failed courses before continuing.'];
            }
        }

        return ['allowed' => true, 'message' => 'Promotion check passed. Select the next term and approved semester curriculum.'];
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

    private function validatePlacement(Student $student, AcademicTerm $term, SemesterCurriculum $curriculum): void
    {
        if (! $student->department_id || ! $student->section_id || ! Section::whereKey($student->section_id)->where('department_id', $student->department_id)->exists()) {
            $this->invalid('student_id', 'The student must belong to a valid department and section before enrollment.');
        }
        if ($term->status !== 'active') {
            $this->invalid('academic_term_id', 'Only active terms accept new enrollments.');
        }
        if ($curriculum->status !== 'approved' || $curriculum->department_id !== $student->department_id) {
            $this->invalid('semester_curriculum_id', 'Select an approved curriculum for the student\'s department.');
        }
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
