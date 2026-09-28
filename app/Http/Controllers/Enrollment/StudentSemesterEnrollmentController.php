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
            ->with(['student.department', 'student.section', 'department', 'section'])
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

    private function defaultAcademicYear(): string
    {
        $startYear = now()->month >= 8 ? now()->year : now()->subYear()->year;

        return $startYear . '-' . ($startYear + 1);
    }
}
