<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Teacher;
use App\Models\Timetable\Room;
use App\Models\Timetable\TimeSlot;
use App\Models\Timetable\TimetableAllocation;
use App\Services\Timetable\TimetableService;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(
        protected TimetableService $timetableService
    ) {}

    /**
     * Display the timetable management and weekly scheduling grid.
     */
    public function index(Request $request)
    {
        $terms = AcademicTerm::orderByDesc('id')->get();
        $selectedTermId = $request->input('term_id') ?? AcademicTerm::where('status', 'active')->value('id') ?? $terms->first()?->id;
        $selectedTerm = $terms->firstWhere('id', $selectedTermId);

        $rooms = Room::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $sections = Section::orderBy('name')->get();

        $filters = [
            'room_id' => $request->input('room_id'),
            'teacher_id' => $request->input('teacher_id'),
            'department_id' => $request->input('department_id'),
            'section_id' => $request->input('section_id'),
        ];

        $grid = $selectedTerm
            ? $this->timetableService->getWeeklyGridForTerm($selectedTerm, $filters)
            : ['days' => [], 'time_windows' => collect(), 'matrix' => [], 'total_allocations' => 0, 'all_allocations' => collect()];

        $timeSlots = $selectedTerm
            ? TimeSlot::where('academic_term_id', $selectedTerm->id)->orderBy('day_of_week')->orderBy('start_time')->get()
            : collect();

        $offerings = $selectedTerm
            ? CourseOffering::where('academic_term_id', $selectedTerm->id)
            ->with(['course', 'teachers', 'section', 'department'])
            ->get()
            : collect();

        return view('academic.timetable.index', [
            'terms' => $terms,
            'selectedTerm' => $selectedTerm,
            'rooms' => $rooms,
            'timeSlots' => $timeSlots,
            'offerings' => $offerings,
            'teachers' => $teachers,
            'departments' => $departments,
            'sections' => $sections,
            'grid' => $grid,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a new classroom or lab facility.
     */
    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:rooms,code',
            'building' => 'nullable|string|max:100',
            'type' => 'required|in:lecture_hall,computer_lab,science_lab,auditorium',
            'capacity' => 'required|integer|min:1|max:500',
        ]);

        Room::create($validated);

        return redirect()->back()->with('success', "Room '{$validated['name']}' ({$validated['code']}) has been created.");
    }

    /**
     * Store a new time slot for an academic term.
     */
    public function storeTimeSlot(Request $request)
    {
        $validated = $request->validate([
            'academic_term_id' => 'required|exists:academic_terms,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_type' => 'required|in:regular,lab_double,break',
        ]);

        $exists = TimeSlot::where('academic_term_id', $validated['academic_term_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors([
                'time_slot' => 'A slot with identical day, start time, and end time already exists for this term.',
            ])->withInput();
        }

        TimeSlot::create($validated);

        return redirect()->back()->with('success', "Time slot for {$validated['day_of_week']} ({$validated['start_time']} - {$validated['end_time']}) created.");
    }

    /**
     * Allocate a lecture or lab session.
     */
    public function storeAllocation(Request $request)
    {
        $validated = $request->validate([
            'course_offering_id' => 'required|exists:course_offerings,id',
            'room_id' => 'required|exists:rooms,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'teacher_id' => 'required|exists:teachers,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $allocation = $this->timetableService->allocate($validated, $request->user());

        return redirect()->back()->with('success', "Lecture allocated successfully: {$allocation->courseOffering?->course_name} in Room {$allocation->room?->code}.");
    }

    /**
     * Remove an allocated timetable entry.
     */
    public function destroyAllocation(TimetableAllocation $allocation)
    {
        $name = $allocation->courseOffering?->course_name ?? 'Class';
        $this->timetableService->deallocate($allocation);

        return redirect()->back()->with('success', "Allocation for '{$name}' has been removed.");
    }
}
