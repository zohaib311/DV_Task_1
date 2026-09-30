<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use App\Services\AcademicEnrollmentService;
use Illuminate\Http\Request;

class StudentSemesterEnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = StudentSemesterEnrollment::with(['student', 'department', 'section', 'semesterResult', 'term', 'curriculum', 'courses.course', 'courses.offering.teachers'])
            ->withCount('courses')->orderByDesc('enrolled_at')->orderByDesc('id')->get();

        return view('enrollments.enrollments', compact('enrollments'));
    }

    public function create()
    {
        return $this->form();
    }

    public function promote(StudentSemesterEnrollment $enrollment)
    {
        return $this->form($enrollment);
    }

    private function form(?StudentSemesterEnrollment $enrollment = null)
    {
        $service = app(AcademicEnrollmentService::class);

        return view('enrollments.add-enrollment', [
            'students' => $enrollment ? collect([$enrollment->student()->with(['department', 'section'])->firstOrFail()]) : Student::with(['department', 'section'])->orderBy('name')->get(),
            'terms' => AcademicTerm::with('academicYear')->where('status', 'active')->orderByDesc('starts_on')->get(),
            'curricula' => SemesterCurriculum::with('department')->where('status', 'approved')->orderBy('semester')->orderByDesc('id')->get(),
            'promotionEnrollment' => $enrollment,
            'promotionEligibility' => $enrollment ? $service->promotionEligibility($enrollment) : null,
            'nextSemester' => $enrollment ? $service->nextSemester($enrollment->semester) : null,
        ]);
    }

    public function offerings(Request $request, AcademicEnrollmentService $service)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'academic_term_id' => ['required', 'integer', 'exists:academic_terms,id'],
            'semester_curriculum_id' => ['required', 'integer', 'exists:semester_curricula,id'],
        ]);
        $curriculum = SemesterCurriculum::with('courses')->findOrFail($data['semester_curriculum_id']);
        $offerings = $service->availableOfferings(Student::findOrFail($data['student_id']), AcademicTerm::findOrFail($data['academic_term_id']), $curriculum);
        $missing = $curriculum->courses->where('type', 'required')->reject(fn ($course) => $offerings->contains('curriculum_course_id', $course->id));

        return response()->json([
            'missing_required' => $missing->pluck('course_code')->values(),
            'offerings' => $offerings->map(fn ($offering) => [
                'id' => $offering->id, 'course_name' => $offering->course_name, 'course_code' => $offering->course_code,
                'credit_hours' => $offering->credit_hours, 'total_marks' => $offering->total_marks,
                'attendance_marks' => $offering->attendance_marks, 'mid_marks' => $offering->mid_marks, 'final_marks' => $offering->final_marks,
                'teachers' => $offering->teachers->pluck('name')->implode(', '),
                'type' => $offering->curriculumCourse->semester_curriculum_id === $curriculum->id ? $offering->curriculumCourse->type : 'repeat',
            ])->values(),
        ]);
    }

    public function store(Request $request, AcademicEnrollmentService $service)
    {
        $service->enroll($request->validate($this->rules() + ['student_id' => ['required', 'integer', 'exists:students,id']]));

        return redirect()->route('allEnrollments')->with('success', 'Semester enrollment created with its assigned course offerings.');
    }

    public function storePromotion(Request $request, StudentSemesterEnrollment $enrollment, AcademicEnrollmentService $service)
    {
        $created = $service->enroll($request->validate($this->rules()), $enrollment);

        return redirect()->route('allEnrollments')->with('success', "{$created->student->name} has been promoted to {$created->semester}.");
    }

    private function rules(): array
    {
        return [
            'academic_term_id' => ['required', 'integer', 'exists:academic_terms,id'],
            'semester_curriculum_id' => ['required', 'integer', 'exists:semester_curricula,id'],
            'offering_ids' => ['required', 'array', 'min:1'],
            'offering_ids.*' => ['required', 'integer', 'distinct', 'exists:course_offerings,id'],
        ];
    }
}
