<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Services\AcademicEnrollmentService;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;

class CourseRegistrationController extends Controller
{
    public function index(Request $request, StudentWorkspace $workspace, AcademicEnrollmentService $service)
    {
        $student = $workspace->student($request->user())->load('activeSemesterEnrollment.term');
        $enrollment = $student->activeSemesterEnrollment;
        $offerings = $enrollment ? $service->optionalRegistrationOfferings($enrollment) : collect();
        $registeredCredits = (float) ($enrollment?->courses()->sum('credit_hours') ?? 0);
        $maximumCredits = (float) config('academic.enrollment.maximum_credit_hours', 21);

        return view('student-portal.registration', compact('student', 'enrollment', 'offerings', 'registeredCredits', 'maximumCredits'));
    }

    public function store(Request $request, StudentWorkspace $workspace, AcademicEnrollmentService $service)
    {
        $data = $request->validate(['offering_ids' => ['required', 'array', 'min:1'], 'offering_ids.*' => ['required', 'integer', 'distinct', 'exists:course_offerings,id']]);
        $student = $workspace->student($request->user());
        $enrollment = $student->activeSemesterEnrollment;
        abort_unless($enrollment, 404);
        $service->addOptionalCourses($student, $enrollment, $data['offering_ids']);

        return redirect()->route('student.registration.index')->with('success', 'Selected courses were added to your active semester enrollment.');
    }
}
