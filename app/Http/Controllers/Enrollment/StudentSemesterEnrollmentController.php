<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentSemesterEnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = StudentSemesterEnrollment::query()
            ->with(['student.department', 'student.section', 'department', 'section', 'semesterResult'])
            ->withCount('courses')
            ->orderByDesc('enrolled_at')
            ->orderByDesc('id')
            ->get();

        return view('enrollments.enrollments', compact('enrollments'));
    }

    public function create()
    {
        $students = Student::with(['department', 'section'])
            ->orderBy('name')
            ->get();
        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('enrollments.add-enrollment', [
            'students' => $students,
            'courses' => $courses,
            'defaultAcademicYear' => $this->defaultAcademicYear(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'academic_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['required', 'in:Semester 1,Semester 2,Semester 3,Semester 4,Semester 5,Semester 6,Semester 7,Semester 8'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['required', 'integer', 'distinct', 'exists:courses,id'],
        ]);

        $student = Student::with(['department', 'section'])->findOrFail($validated['student_id']);

        if (! $student->department_id || ! $student->section_id) {
            throw ValidationException::withMessages([
                'student_id' => 'The selected student must have a department and section before enrollment.',
            ]);
        }

        $courseIds = collect($validated['course_ids'])->map(fn ($id) => (int) $id)->values();
        $courses = Course::query()
            ->whereIn('id', $courseIds)
            ->where('is_active', true)
            ->get();

        if ($courses->count() !== $courseIds->count()) {
            throw ValidationException::withMessages([
                'course_ids' => 'Only active courses can be assigned to a semester enrollment.',
            ]);
        }

        if ($courses->contains(fn (Course $course) => ($course->attendance_marks + $course->mid_marks + $course->final_marks) !== $course->total_marks)) {
            throw ValidationException::withMessages([
                'course_ids' => 'Every selected course must have a valid Attendance + Midterm + Final assessment scheme before enrollment.',
            ]);
        }

        if (StudentSemesterEnrollment::query()
            ->where('student_id', $student->id)
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester'])
            ->exists()) {
            throw ValidationException::withMessages([
                'semester' => 'This student is already enrolled in the selected academic year and semester.',
            ]);
        }

        DB::transaction(function () use ($student, $validated, $courseIds, $courses) {
            $enrollment = StudentSemesterEnrollment::create([
                'student_id' => $student->id,
                'department_id' => $student->department_id,
                'section_id' => $student->section_id,
                'academic_year' => $validated['academic_year'],
                'semester' => $validated['semester'],
                'status' => 'active',
                'enrolled_at' => today(),
            ]);

            $enrollment->courses()->createMany(
                $courses->map(fn (Course $course) => [
                    'course_id' => $course->id,
                    'credit_hours' => $course->credit_hours,
                    'total_marks' => $course->total_marks,
                    'attendance_marks' => $course->attendance_marks,
                    'mid_marks' => $course->mid_marks,
                    'final_marks' => $course->final_marks,
                ])->all()
            );

            // Keep the existing student screens functional while history is stored separately.
            $student->update([
                'semester' => $validated['semester'],
                'course_ids' => $courseIds->all(),
            ]);
        });

        return redirect()
            ->route('allEnrollments')
            ->with('success', 'Semester enrollment created successfully.');
    }

    /**
     * Reuse the normal enrollment form for a controlled next-semester promotion.
     */
    public function promote(StudentSemesterEnrollment $enrollment)
    {
        $enrollment->load([
            'student.department',
            'student.section',
            'department',
            'section',
            'courses.course',
            'semesterResult',
        ]);

        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('enrollments.add-enrollment', [
            'students' => collect([$enrollment->student]),
            'courses' => $courses,
            'defaultAcademicYear' => $enrollment->academic_year,
            'promotionEnrollment' => $enrollment,
            'promotionEligibility' => $this->promotionEligibility($enrollment),
            'nextSemester' => $this->nextSemester($enrollment->semester),
            'promotionSelectedCourses' => $enrollment->courses->pluck('course_id')->all(),
        ]);
    }

    /**
     * Create the next enrollment while retaining the completed enrollment and
     * its result as immutable academic history.
     */
    public function storePromotion(Request $request, StudentSemesterEnrollment $enrollment)
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['required', 'in:Semester 1,Semester 2,Semester 3,Semester 4,Semester 5,Semester 6,Semester 7,Semester 8'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['required', 'integer', 'distinct', 'exists:courses,id'],
        ]);

        $createdEnrollment = DB::transaction(function () use ($enrollment, $validated) {
            $currentEnrollment = StudentSemesterEnrollment::query()
                ->with(['student.department', 'student.section', 'semesterResult', 'courses'])
                ->lockForUpdate()
                ->findOrFail($enrollment->id);

            $eligibility = $this->promotionEligibility($currentEnrollment);
            if (! $eligibility['allowed']) {
                throw ValidationException::withMessages([
                    'promotion' => $eligibility['message'],
                ]);
            }

            $expectedSemester = $this->nextSemester($currentEnrollment->semester);
            if ($expectedSemester === null || $validated['semester'] !== $expectedSemester) {
                throw ValidationException::withMessages([
                    'semester' => $expectedSemester
                        ? "Promotion must continue to {$expectedSemester}."
                        : 'Semester 8 is the final configured semester and cannot be promoted automatically.',
                ]);
            }

            $student = $currentEnrollment->student;
            $courseIds = collect($validated['course_ids'])->map(fn ($id) => (int) $id)->values();
            $courses = $this->validatedCourses($courseIds);

            if (StudentSemesterEnrollment::query()
                ->where('student_id', $student->id)
                ->where('academic_year', $validated['academic_year'])
                ->where('semester', $validated['semester'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'semester' => 'This student already has an enrollment for the selected academic year and semester.',
                ]);
            }

            $newEnrollment = StudentSemesterEnrollment::create([
                'student_id' => $student->id,
                'department_id' => $student->department_id,
                'section_id' => $student->section_id,
                'academic_year' => $validated['academic_year'],
                'semester' => $validated['semester'],
                'status' => 'active',
                'enrolled_at' => today(),
            ]);

            $newEnrollment->courses()->createMany($this->courseSnapshots($courses));
            $currentEnrollment->update([
                'status' => 'promoted',
                'completed_at' => today(),
            ]);
            $student->update([
                'semester' => $validated['semester'],
                'course_ids' => $courseIds->all(),
            ]);

            return $newEnrollment;
        });

        return redirect()
            ->route('allEnrollments')
            ->with('success', "{$createdEnrollment->student->name} has been promoted to {$createdEnrollment->semester}.");
    }

    /** @return array{allowed: bool, message: string} */
    private function promotionEligibility(StudentSemesterEnrollment $enrollment): array
    {
        if ($enrollment->status !== 'active') {
            return ['allowed' => false, 'message' => 'Only the student’s current active enrollment can be promoted.'];
        }

        if (! config('academic.promotion.require_published_pass_result')) {
            return ['allowed' => true, 'message' => 'Promotion is allowed by the current academic policy.'];
        }

        $result = $enrollment->semesterResult;
        if ($result === null || $result->published_at === null) {
            return ['allowed' => false, 'message' => 'A published semester result is required before promotion.'];
        }

        if ($result->status !== 'Pass') {
            return ['allowed' => false, 'message' => 'Promotion is blocked because the published semester result is not a pass. Review repeat or improvement requirements first.'];
        }

        return ['allowed' => true, 'message' => 'Published passing result verified. Select the next semester courses to continue.'];
    }

    private function nextSemester(string $semester): ?string
    {
        preg_match('/\d+/', $semester, $matches);
        $number = (int) ($matches[0] ?? 0);

        return $number >= 1 && $number < 8 ? 'Semester '.($number + 1) : null;
    }

    /** @param \Illuminate\Support\Collection<int, int> $courseIds */
    private function validatedCourses($courseIds)
    {
        $courses = Course::query()
            ->whereIn('id', $courseIds)
            ->where('is_active', true)
            ->get();

        if ($courses->count() !== $courseIds->count()) {
            throw ValidationException::withMessages([
                'course_ids' => 'Only active courses can be assigned to a semester enrollment.',
            ]);
        }

        if ($courses->contains(fn (Course $course) => ($course->attendance_marks + $course->mid_marks + $course->final_marks) !== $course->total_marks)) {
            throw ValidationException::withMessages([
                'course_ids' => 'Every selected course must have a valid Attendance, Midterm, and Final assessment scheme before enrollment.',
            ]);
        }

        return $courses;
    }

    /** @param \Illuminate\Support\Collection<int, Course> $courses */
    private function courseSnapshots($courses): array
    {
        return $courses->map(fn (Course $course) => [
            'course_id' => $course->id,
            'credit_hours' => $course->credit_hours,
            'total_marks' => $course->total_marks,
            'attendance_marks' => $course->attendance_marks,
            'mid_marks' => $course->mid_marks,
            'final_marks' => $course->final_marks,
        ])->all();
    }

    private function defaultAcademicYear(): string
    {
        $startYear = now()->month >= 8 ? now()->year : now()->subYear()->year;

        return $startYear.'-'.($startYear + 1);
    }
}
