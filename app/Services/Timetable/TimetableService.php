<?php

namespace App\Services\Timetable;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Timetable\Room;
use App\Models\Timetable\TimeSlot;
use App\Models\Timetable\TimetableAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableService
{
    public const DAYS_OF_WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * Allocate a timetable lecture slot with strict clash prevention.
     *
     * @throws ValidationException
     */
    public function allocate(array $data, ?User $user = null): TimetableAllocation
    {
        $offering = CourseOffering::with(['teachers', 'section', 'term'])->findOrFail($data['course_offering_id']);
        $room = Room::findOrFail($data['room_id']);
        $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);
        $teacher = Teacher::findOrFail($data['teacher_id']);

        // 1. Room active status check
        if (! $room->is_active) {
            throw ValidationException::withMessages([
                'room_id' => "Room '{$room->name}' is currently inactive.",
            ]);
        }

        // 2. Break slot check
        if ($timeSlot->slot_type === 'break') {
            throw ValidationException::withMessages([
                'time_slot_id' => 'Classes cannot be scheduled during break or recess time slots.',
            ]);
        }

        // 3. Term match check
        if ($timeSlot->academic_term_id !== $offering->academic_term_id) {
            throw ValidationException::withMessages([
                'time_slot_id' => 'Selected time slot does not belong to the academic term of this course offering.',
            ]);
        }

        // 4. Room Double-Booking Clash Check
        $roomClash = TimetableAllocation::where('room_id', $room->id)
            ->where('time_slot_id', $timeSlot->id)
            ->with(['courseOffering'])
            ->first();

        if ($roomClash) {
            throw ValidationException::withMessages([
                'room_id' => "Room is already booked at this time slot for '{$roomClash->courseOffering?->course_name}'.",
            ]);
        }

        // 5. Teacher Schedule Clash Check
        $teacherClash = TimetableAllocation::where('teacher_id', $teacher->id)
            ->where('time_slot_id', $timeSlot->id)
            ->with(['courseOffering'])
            ->first();

        if ($teacherClash) {
            throw ValidationException::withMessages([
                'teacher_id' => "Teacher has a schedule conflict at this time slot with '{$teacherClash->courseOffering?->course_name}'.",
            ]);
        }

        // 6. Course Offering Double-Booking Check
        $offeringClash = TimetableAllocation::where('course_offering_id', $offering->id)
            ->where('time_slot_id', $timeSlot->id)
            ->first();

        if ($offeringClash) {
            throw ValidationException::withMessages([
                'course_offering_id' => 'This course offering is already scheduled at this time slot.',
            ]);
        }

        // 7. Student Cohort / Section Clash Check
        // Ensures the same section doesn't have 2 simultaneous classes
        if ($offering->section_id) {
            $sectionClash = TimetableAllocation::where('time_slot_id', $timeSlot->id)
                ->whereHas('courseOffering', function ($query) use ($offering) {
                    $query->where('section_id', $offering->section_id)
                        ->where('academic_term_id', $offering->academic_term_id);
                })
                ->with(['courseOffering'])
                ->first();

            if ($sectionClash) {
                $sectionName = $offering->section?->name ?? 'A';
                throw ValidationException::withMessages([
                    'course_offering_id' => "Student Section '{$sectionName}' already has another class scheduled at this time slot ('{$sectionClash->courseOffering?->course_name}').",
                ]);
            }
        }

        // 8. Teacher Assignment Check
        $isTeacherAssigned = $offering->teachers()->where('teachers.id', $teacher->id)->exists();
        if (! $isTeacherAssigned) {
            throw ValidationException::withMessages([
                'teacher_id' => "Teacher '{$teacher->name}' is not assigned to teach '{$offering->course_name}'. Please assign the teacher first in Course Offerings.",
            ]);
        }

        return DB::transaction(function () use ($offering, $room, $timeSlot, $teacher, $user, $data) {
            return TimetableAllocation::create([
                'course_offering_id' => $offering->id,
                'room_id' => $room->id,
                'time_slot_id' => $timeSlot->id,
                'teacher_id' => $teacher->id,
                'created_by' => $user?->id,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Remove an existing allocation.
     */
    public function deallocate(TimetableAllocation $allocation): bool
    {
        return DB::transaction(function () use ($allocation) {
            return (bool) $allocation->delete();
        });
    }

    /**
     * Build weekly scheduling grid for an academic term.
     */
    public function getWeeklyGridForTerm(AcademicTerm $term, array $filters = []): array
    {
        $timeSlots = TimeSlot::where('academic_term_id', $term->id)
            ->orderBy('start_time')
            ->get();

        // Get unique time windows (e.g. 08:30 AM - 10:00 AM)
        $uniqueWindows = $timeSlots->map(fn($slot) => [
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'label' => $slot->time_window,
        ])->unique('label')->values();

        $allocationsQuery = TimetableAllocation::whereHas('timeSlot', fn($q) => $q->where('academic_term_id', $term->id))
            ->with([
                'courseOffering.course',
                'courseOffering.section',
                'courseOffering.department',
                'room',
                'timeSlot',
                'teacher',
            ]);

        if (! empty($filters['room_id'])) {
            $allocationsQuery->where('room_id', $filters['room_id']);
        }
        if (! empty($filters['teacher_id'])) {
            $allocationsQuery->where('teacher_id', $filters['teacher_id']);
        }
        if (! empty($filters['section_id'])) {
            $allocationsQuery->whereHas('courseOffering', fn($q) => $q->where('section_id', $filters['section_id']));
        }
        if (! empty($filters['department_id'])) {
            $allocationsQuery->whereHas('courseOffering', fn($q) => $q->where('department_id', $filters['department_id']));
        }

        $allocations = $allocationsQuery->get();

        // Build 2D matrix: [day][time_window] = allocations collection
        $matrix = [];
        foreach (self::DAYS_OF_WEEK as $day) {
            $matrix[$day] = [];
            foreach ($uniqueWindows as $window) {
                $matrix[$day][$window['label']] = $allocations->filter(function ($item) use ($day, $window) {
                    return $item->timeSlot?->day_of_week === $day
                        && $item->timeSlot?->time_window === $window['label'];
                })->values();
            }
        }

        return [
            'days' => self::DAYS_OF_WEEK,
            'time_windows' => $uniqueWindows,
            'matrix' => $matrix,
            'total_allocations' => $allocations->count(),
            'all_allocations' => $allocations,
        ];
    }

    /**
     * Get weekly schedule for a teacher.
     */
    public function getTeacherWeeklySchedule(Teacher $teacher, ?AcademicTerm $term = null): Collection
    {
        $query = TimetableAllocation::where('teacher_id', $teacher->id)
            ->with([
                'courseOffering.course',
                'courseOffering.section',
                'courseOffering.department',
                'room',
                'timeSlot.term',
            ]);

        if ($term) {
            $query->whereHas('timeSlot', fn($q) => $q->where('academic_term_id', $term->id));
        } else {
            // Default to active term or latest
            $query->whereHas('timeSlot.term', fn($q) => $q->where('status', 'active'));
        }

        $dayOrder = array_flip(self::DAYS_OF_WEEK);

        return $query->get()->sort(function ($a, $b) use ($dayOrder) {
            $dayA = $dayOrder[$a->timeSlot?->day_of_week] ?? 99;
            $dayB = $dayOrder[$b->timeSlot?->day_of_week] ?? 99;

            if ($dayA !== $dayB) {
                return $dayA <=> $dayB;
            }

            return strcmp((string) $a->timeSlot?->start_time, (string) $b->timeSlot?->start_time);
        })->values();
    }

    /**
     * Get weekly schedule for a student.
     */
    public function getStudentWeeklySchedule(Student $student, ?AcademicTerm $term = null): Collection
    {
        $enrollment = $term
            ? $student->semesterEnrollments()->where('academic_term_id', $term->id)->first()
            : $student->activeSemesterEnrollment;

        if (! $enrollment) {
            return new Collection();
        }

        $offeringIds = $enrollment->courses()->pluck('course_offering_id')->filter()->unique();

        if ($offeringIds->isEmpty()) {
            return new Collection();
        }

        $query = TimetableAllocation::whereIn('course_offering_id', $offeringIds)
            ->with([
                'courseOffering.course',
                'courseOffering.section',
                'courseOffering.department',
                'room',
                'timeSlot.term',
                'teacher',
            ]);

        $dayOrder = array_flip(self::DAYS_OF_WEEK);

        return $query->get()->sort(function ($a, $b) use ($dayOrder) {
            $dayA = $dayOrder[$a->timeSlot?->day_of_week] ?? 99;
            $dayB = $dayOrder[$b->timeSlot?->day_of_week] ?? 99;

            if ($dayA !== $dayB) {
                return $dayA <=> $dayB;
            }

            return strcmp((string) $a->timeSlot?->start_time, (string) $b->timeSlot?->start_time);
        })->values();
    }
}
