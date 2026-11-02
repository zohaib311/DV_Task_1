<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Services\Timetable\TimetableService;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(
        protected TimetableService $timetableService
    ) {}

    /**
     * Display weekly schedule for the authenticated teacher.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;
        abort_unless($teacher, 403, 'Your account is not linked to an academic faculty teacher profile.');

        $terms = AcademicTerm::orderByDesc('id')->get();
        $selectedTermId = $request->input('term_id') ?? AcademicTerm::where('status', 'active')->value('id') ?? $terms->first()?->id;
        $selectedTerm = $terms->firstWhere('id', $selectedTermId);

        $schedule = $this->timetableService->getTeacherWeeklySchedule($teacher, $selectedTerm);

        return view('teaching.timetable', [
            'teacher' => $teacher,
            'schedule' => $schedule,
            'terms' => $terms,
            'selectedTerm' => $selectedTerm,
        ]);
    }
}
