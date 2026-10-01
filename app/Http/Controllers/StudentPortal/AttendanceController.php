<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceRecord;
use App\Services\Attendance\AttendanceCalculator;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private function courses(Request $request)
    {
        return app(StudentWorkspace::class)->courses($request->user());
    }

    public function index(Request $request, AttendanceCalculator $calculator)
    {
        $courses = $this->courses($request)->with(['enrollment', 'offering.term', 'course'])->orderByDesc('id')->paginate(15);
        $summaries = $courses->getCollection()->mapWithKeys(fn ($course) => [$course->id => $calculator->summary($course)]);

        return view('student-portal.attendance.index', compact('courses', 'summaries'));
    }

    public function show(Request $request, int $course, AttendanceCalculator $calculator)
    {
        $course = $this->courses($request)->with(['enrollment', 'offering', 'course'])->findOrFail($course);
        $summary = $calculator->summary($course);
        $records = AttendanceRecord::where('student_enrollment_course_id', $course->id)->whereHas('session', fn ($query) => $query->where('status', 'completed'))->with('session')->orderByDesc('id')->paginate(25);

        return view('student-portal.attendance.show', compact('course', 'summary', 'records'));
    }
}
