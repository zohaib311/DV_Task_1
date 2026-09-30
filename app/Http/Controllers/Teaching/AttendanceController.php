<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceCalculator;
use App\Services\Attendance\AttendanceService;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $offerings = $workspace->offerings($teacher)->with(['term', 'section'])->orderByDesc('id')->paginate(15);

        return view('teaching.attendance.index', compact('offerings'));
    }

    public function offering(Request $request, int $offering, AttendanceService $service, AttendanceCalculator $calculator)
    {
        $offering = $service->offering($request->user(), $offering);
        $sessions = $offering->attendanceSessions()->with('teacher')->withCount('records')->orderByDesc('held_on')->orderByDesc('id')->paginate(15, ['*'], 'sessions_page');
        $courses = $offering->enrollmentCourses()->with(['enrollment.student', 'offering'])->paginate(25, ['*'], 'students_page');
        $summaries = $courses->getCollection()->mapWithKeys(fn ($course) => [$course->id => $calculator->summary($course)]);

        return view('teaching.attendance.offering', compact('offering', 'sessions', 'courses', 'summaries'));
    }

    public function store(Request $request, int $offering, AttendanceService $service)
    {
        $data = $request->validate(['held_on' => ['required', 'date_format:Y-m-d'], 'type' => ['required', Rule::in(['lecture', 'lab', 'tutorial'])], 'slot' => ['required', 'integer', 'min:1', 'max:20']]);
        $session = $service->create($request->user(), $offering, $data);

        return redirect()->route('teaching.attendance.show', [$offering, $session])->with('success', 'Session created. Mark every student and save to include it in attendance totals.');
    }

    public function show(Request $request, int $offering, int $session, AttendanceService $service)
    {
        $offering = $service->offering($request->user(), $offering);
        $session = $offering->attendanceSessions()->with(['records.enrollmentCourse.enrollment.student', 'records.enrollmentCourse.enrollment.semesterResult'])->findOrFail($session);
        $locked = $session->status === 'cancelled' || $offering->status !== 'active' || $offering->term->status !== 'active'
            || $session->records->contains(function ($record) {
                $enrollment = $record->enrollmentCourse->enrollment;

                return $enrollment->status !== 'active' || ($enrollment->semesterResult?->published_at && in_array($enrollment->semesterResult->status, ['Pass', 'Fail']));
            });
        $audits = $session->audits()->with('user')->latest('id')->paginate(10);

        return view('teaching.attendance.session', compact('offering', 'session', 'locked', 'audits'));
    }

    public function update(Request $request, int $offering, int $session, AttendanceService $service)
    {
        $data = $request->validate([
            'revision' => ['required', 'integer', 'min:1'], 'reason' => ['nullable', 'string', 'max:500'],
            'records' => ['required', 'array', 'min:1'], 'records.*.id' => ['required', 'integer', 'distinct'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
            'records.*.note' => ['nullable', 'string', 'max:500'],
        ]);
        $service->update($request->user(), $offering, $session, $data);

        return back()->with('success', 'Attendance saved. Percentages and calculated marks have been updated.');
    }

    public function cancel(Request $request, int $offering, int $session, AttendanceService $service)
    {
        $data = $request->validate(['revision' => ['required', 'integer', 'min:1'], 'reason' => ['required', 'string', 'min:5', 'max:500']]);
        $service->update($request->user(), $offering, $session, $data, true);

        return back()->with('success', 'Session cancelled and excluded from attendance totals. Its audit history is preserved.');
    }
}
