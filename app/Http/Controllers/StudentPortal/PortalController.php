<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function dashboard(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user())->load(['department', 'section', 'activeSemesterEnrollment.term', 'activeSemesterEnrollment.section']);
        $enrollment = $student->activeSemesterEnrollment;
        $courseCount = $enrollment?->courses()->count() ?? 0;
        $latestResult = $request->user()->can('student.result.view-own') ? $workspace->results($request->user())->with('enrollment')->get()
            ->sortByDesc(fn ($result) => $result->enrollment->academic_year.'-'.str_pad(preg_replace('/\D/', '', $result->enrollment->semester), 2, '0', STR_PAD_LEFT))->first() : null;

        return view('student-portal.dashboard', compact('student', 'enrollment', 'courseCount', 'latestResult'));
    }

    public function courses(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $enrollments = $student->semesterEnrollments()->orderByDesc('id')->get();
        $data = $request->validate(['enrollment' => ['nullable', 'integer']]);
        if (isset($data['enrollment'])) {
            $student->semesterEnrollments()->findOrFail($data['enrollment']);
        }
        $courses = $workspace->courses($request->user())->with(['enrollment.term', 'offering.teachers'])
            ->when($data['enrollment'] ?? null, fn ($query, $id) => $query->where('student_semester_enrollment_id', $id))
            ->orderByDesc('student_semester_enrollment_id')->orderBy('course_code')->paginate(20)->withQueryString();

        return view('student-portal.courses', compact('enrollments', 'courses'));
    }

    public function results(Request $request, StudentWorkspace $workspace)
    {
        $results = $workspace->results($request->user())->with('enrollment')->orderByDesc('id')->paginate(15);

        return view('student-portal.results.index', compact('results'));
    }

    public function result(Request $request, int $result, StudentWorkspace $workspace)
    {
        $result = $workspace->results($request->user())->with(['enrollment', 'items'])->findOrFail($result);
        $student = $workspace->student($request->user());

        return view('student-portal.results.show', compact('result', 'student'));
    }

    public function assessments(Request $request, StudentWorkspace $workspace)
    {
        $courses = $workspace->courses($request->user())->with('enrollment')->orderByDesc('id')->paginate(20);

        return view('student-portal.assessments.index', compact('courses'));
    }

    public function assessment(Request $request, int $course, StudentWorkspace $workspace)
    {
        $course = $workspace->courses($request->user())->with(['enrollment.term', 'offering.assessmentComponents.assessments.marks', 'offering.assessmentComponents.assessments.studentSubmissions'])->findOrFail($course);
        $result = $workspace->results($request->user())->where('student_semester_enrollment_id', $course->student_semester_enrollment_id)->first();
        $item = $result?->items()->where('student_enrollment_course_id', $course->id)->first();

        $liveAssessments = $course->offering?->assessmentComponents->flatMap->assessments->sortBy('held_on') ?? collect();

        return view('student-portal.assessments.show', compact('course', 'item', 'liveAssessments'));
    }
}
