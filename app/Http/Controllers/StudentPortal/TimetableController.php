<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Services\StudentPortal\StudentWorkspace;
use App\Services\Timetable\TimetableService;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(
        protected TimetableService $timetableService
    ) {}

    /**
     * Display personal weekly timetable for the enrolled student.
     */
    public function index(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $enrollment = $student->activeSemesterEnrollment;
        $schedule = $this->timetableService->getStudentWeeklySchedule($student);

        return view('student-portal.timetable', [
            'student' => $student,
            'enrollment' => $enrollment,
            'schedule' => $schedule,
        ]);
    }
}
