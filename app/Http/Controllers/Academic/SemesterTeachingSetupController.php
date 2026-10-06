<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Academic\Program;
use App\Models\Section\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Notifications\AcademicNotificationService;

class SemesterTeachingSetupController extends Controller
{
    public function create()
    {
        $curricula = SemesterCurriculum::with(['department', 'program', 'courses'])
            ->where('status', 'approved')->whereNotNull('program_id')
            ->orderBy('program_id')->orderBy('semester')->get();
        $teachers = Teacher::with('user')->whereHas('user')->orderBy('name')->get();
        $terms = AcademicTerm::with('academicYear')->where('status', 'active')->orderByDesc('starts_on')->get();
        $existingOfferings = CourseOffering::with('teachers:id,name')
            ->whereIn('academic_term_id', $terms->pluck('id'))
            ->whereNotNull('program_id')
            ->get();

        return view('academic.semester-teaching-setup', [
            'terms' => $terms,
            'curricula' => $curricula,
            'programs' => Program::with('department')->where('is_active', true)->orderBy('name')->get(),
            'sections' => Section::with('department')->orderBy('name')->get(),
            'teachers' => $teachers,
            'curriculaPayload' => $curricula->mapWithKeys(function (SemesterCurriculum $curriculum) {
                return [$curriculum->id => $curriculum->courses->map(fn ($course) => $course->only([
                    'id', 'course_id', 'course_code', 'course_name', 'type', 'credit_hours',
                    'attendance_marks', 'mid_marks', 'final_marks', 'total_marks',
                ]))->values()];
            }),
            'teachersPayload' => $teachers->map(fn (Teacher $teacher) => $teacher->only(['id', 'name']))->values(),
            'existingOfferingsPayload' => $existingOfferings->mapWithKeys(fn (CourseOffering $offering) => [
                implode(':', [$offering->academic_term_id, $offering->program_id, $offering->section_id, $offering->course_id]) => [
                    'id' => $offering->id,
                    'curriculum_course_id' => $offering->curriculum_course_id,
                    'semester' => $offering->semester,
                    'status' => $offering->status,
                    'teachers' => $offering->teachers->map(fn (Teacher $teacher) => $teacher->only(['id', 'name']))->values(),
                ],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_term_id' => ['required', 'integer', 'exists:academic_terms,id'],
            'semester_curriculum_id' => ['required', 'integer', 'exists:semester_curricula,id'],
            'program_id' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['required', 'integer', 'exists:teachers,id'],
        ]);

        $created = DB::transaction(function () use ($data) {
            $term = AcademicTerm::lockForUpdate()->findOrFail($data['academic_term_id']);
            $curriculum = SemesterCurriculum::with(['courses.course'])->lockForUpdate()->findOrFail($data['semester_curriculum_id']);
            $program = ! empty($data['program_id']) ? Program::where('is_active', true)->lockForUpdate()->findOrFail($data['program_id']) : $curriculum->program;
            $section = Section::lockForUpdate()->findOrFail($data['section_id']);

            if ($term->status !== 'active') {
                throw ValidationException::withMessages(['academic_term_id' => 'Select an active teaching term.']);
            }
            if ($curriculum->status !== 'approved' || $curriculum->program_id !== $program?->id || ($program?->department_id ?? $curriculum->department_id) !== $section->department_id) {
                throw ValidationException::withMessages(['semester_curriculum_id' => 'The selected curriculum must be approved for this program and its section department.']);
            }
            $curriculumCourseIds = $curriculum->courses->pluck('id');
            if ($curriculumCourseIds->isEmpty()) {
                throw ValidationException::withMessages(['semester_curriculum_id' => 'The selected semester plan has no courses.']);
            }
            // A catalog course can appear in different curriculum versions/semesters,
            // so checking only curriculum_course_id misses an existing delivery of
            // the same course and leaves the database unique index to throw a 500.
            $catalogCourseIds = $curriculum->courses->pluck('course_id');
            $existing = CourseOffering::where('academic_term_id', $term->id)
                ->where('program_id', $program?->id)
                ->where('section_id', $section->id)
                ->where(function ($query) use ($curriculumCourseIds, $catalogCourseIds) {
                    $query->whereIn('curriculum_course_id', $curriculumCourseIds)
                        ->orWhereIn('course_id', $catalogCourseIds);
                })
                ->lockForUpdate()
                ->get();
            $existingByCourse = $existing->keyBy('course_id');
            $conflictingCourses = $curriculum->courses->filter(function ($course) use ($existingByCourse) {
                $offering = $existingByCourse->get($course->course_id);

                return $offering && $offering->curriculum_course_id !== $course->id;
            });
            if ($conflictingCourses->isNotEmpty()) {
                $conflicts = $conflictingCourses
                    ->map(function ($course) use ($existingByCourse) {
                        $offering = $existingByCourse->get($course->course_id);

                        return "{$course->course_code} ({$offering->semester})";
                    })
                    ->unique()
                    ->implode(', ');

                throw ValidationException::withMessages([
                    'semester_curriculum_id' => "The selected term, program, and section already contain classes for: {$conflicts}. Select a later teaching term for the new semester, or correct the semester plan if the course should not be repeated.",
                ]);
            }

            $missingCourses = $curriculum->courses->reject(fn ($course) => $existingByCourse->has($course->course_id));
            $teacherAssignments = collect($data['teacher_ids'] ?? []);
            $assignedCurriculumCourseIds = $teacherAssignments->keys()->map(fn ($id) => (int) $id);
            if ($missingCourses->pluck('id')->diff($assignedCurriculumCourseIds)->isNotEmpty()) {
                throw ValidationException::withMessages(['teacher_ids' => 'Assign one teacher to every new course in this semester plan. Existing classes keep their current teacher assignments.']);
            }
            $teacherIds = $missingCourses
                ->map(fn ($course) => $teacherAssignments->get($course->id))
                ->filter()
                ->unique();
            if (Teacher::whereIn('id', $teacherIds)->whereHas('user')->count() !== $teacherIds->count()) {
                throw ValidationException::withMessages(['teacher_ids' => 'Each selected teacher must have a linked Teacher login account.']);
            }

            foreach ($missingCourses as $course) {
                if (! $course->course?->is_active) {
                    throw ValidationException::withMessages(['semester_curriculum_id' => "{$course->course_code} is inactive and cannot be prepared for teaching."]);
                }
            }

            foreach ($missingCourses as $course) {
                $offering = CourseOffering::create($course->only(\App\Models\Academic\CurriculumCourse::SNAPSHOT_FIELDS) + [
                    'academic_term_id' => $term->id,
                    'curriculum_course_id' => $course->id,
                    'department_id' => $section->department_id,
                    'program_id' => $program?->id,
                    'section_id' => $section->id,
                    'semester' => $curriculum->semester,
                    'status' => 'active',
                ]);
                $offering->teachers()->attach($teacherAssignments->get($course->id));
            }

            return [
                'created' => $missingCourses->count(),
                'existing' => $curriculum->courses->count() - $missingCourses->count(),
                'offering_ids' => CourseOffering::where('academic_term_id', $term->id)->where('program_id', $program?->id)
                    ->where('section_id', $section->id)->whereIn('curriculum_course_id', $missingCourses->pluck('id'))->pluck('id')->all(),
            ];
        });

        $notifications = app(AcademicNotificationService::class);
        $newOfferings = CourseOffering::with(['term', 'section', 'teachers.user'])->whereIn('id', $created['offering_ids'])->get();
        foreach ($newOfferings as $offering) {
            $notifications->users($offering->teachers->pluck('user'), 'New course assigned',
                "{$offering->course_code} — {$offering->course_name} has been assigned to you for {$offering->section->name} in {$offering->term->name}.",
                route('teaching.offerings.show', $offering), 'teaching', ['offering_id' => $offering->id]);
        }
        if ($newOfferings->isNotEmpty()) {
            $notifications->users($notifications->academicStaff(), 'Semester classes prepared',
                "{$newOfferings->count()} classes were prepared for {$newOfferings->first()->section->name} in {$newOfferings->first()->term->name}.",
                route('academic.offerings.index'), 'administration', ['offering_ids' => $created['offering_ids']], $request->user()->id);
        }

        $message = $created['created'] === 0
            ? "Semester teaching setup was already ready. {$created['existing']} existing classes and teacher assignments were kept."
            : "Semester teaching setup is ready. {$created['created']} missing classes were activated and assigned to teachers".
                ($created['existing'] ? "; {$created['existing']} existing classes were kept." : '.');

        return redirect()->route('academic.offerings.index')->with('success', $message);
    }
}
