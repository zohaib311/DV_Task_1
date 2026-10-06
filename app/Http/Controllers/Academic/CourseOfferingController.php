<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\Program;
use App\Models\Section\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Notifications\AcademicNotificationService;

class CourseOfferingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['term_id' => ['nullable', 'integer', 'exists:academic_terms,id']]);

        return view('academic.offerings', [
            'offerings' => CourseOffering::with(['term.academicYear', 'department', 'program', 'section', 'teachers', 'curriculumCourse.curriculum'])
                ->withCount('enrollmentCourses')->when($filters['term_id'] ?? null, fn ($query, $id) => $query->where('academic_term_id', $id))->latest()->paginate(20)->withQueryString(),
            'terms' => AcademicTerm::with('academicYear')->orderByDesc('starts_on')->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new CourseOffering);
    }

    public function edit(CourseOffering $offering)
    {
        return $this->form($offering->load(['teachers', 'curriculumCourse.curriculum']));
    }

    private function form(CourseOffering $offering)
    {
        return view('academic.offering-form', [
            'offering' => $offering,
            'terms' => AcademicTerm::with('academicYear')->where('status', '!=', 'closed')->orWhere('id', $offering->academic_term_id)->orderByDesc('starts_on')->get(),
            'curriculumCourses' => CurriculumCourse::with('curriculum.program.department')->whereHas('curriculum', fn ($query) => $query->where('status', 'approved')->whereNotNull('program_id'))->orderBy('course_code')->get(),
            'programs' => Program::with('department')->where('is_active', true)->orderBy('name')->get(),
            'sections' => Section::with('department')->orderBy('name')->get(),
            'teachers' => Teacher::whereHas('user')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->save($request, new CourseOffering);

        return redirect()->route('academic.offerings.index')->with('success', 'Course offering created successfully.');
    }

    public function update(Request $request, CourseOffering $offering)
    {
        $this->save($request, $offering);

        return redirect()->route('academic.offerings.index')->with('success', 'Course offering updated successfully.');
    }

    private function save(Request $request, CourseOffering $offering): void
    {
        $previousTeacherUserIds = $offering->exists ? $offering->teachers()->whereNotNull('user_id')->pluck('user_id') : collect();
        $data = $request->validate([
            'academic_term_id' => ['required', 'integer', 'exists:academic_terms,id'],
            'curriculum_course_id' => ['required', 'integer', 'exists:curriculum_courses,id'],
            'program_id' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['required', 'integer', 'distinct', 'exists:teachers,id'],
            'status' => ['required', Rule::in(['planned', 'active'])],
        ]);
        $saved = DB::transaction(function () use ($offering, $data) {
            $term = AcademicTerm::lockForUpdate()->findOrFail($data['academic_term_id']);
            if ($term->status === 'closed' || ($data['status'] === 'active' && $term->status !== 'active')) {
                throw ValidationException::withMessages(['academic_term_id' => 'An active offering needs an active term. Closed terms cannot accept changes.']);
            }
            $course = CurriculumCourse::with(['curriculum.program', 'course'])->findOrFail($data['curriculum_course_id']);
            $program = ! empty($data['program_id']) ? Program::where('is_active', true)->lockForUpdate()->findOrFail($data['program_id']) : $course->curriculum->program;
            $section = Section::findOrFail($data['section_id']);
            if ($course->curriculum->status !== 'approved' || $course->curriculum->program_id !== $program?->id || ($program?->department_id ?? $course->curriculum->department_id) !== $section->department_id || ! $course->course->is_active) {
                throw ValidationException::withMessages(['curriculum_course_id' => 'Select an active catalog course from this program’s approved curriculum and a section in the same department.']);
            }
            $teacherCount = Teacher::whereIn('id', $data['teacher_ids'])->whereHas('user')->count();
            if ($teacherCount !== count($data['teacher_ids'])) {
                throw ValidationException::withMessages(['teacher_ids' => 'Each assigned teacher must have a linked login account.']);
            }
            if ($offering->exists) {
                $offering = CourseOffering::lockForUpdate()->findOrFail($offering->id);
                if ($offering->status !== 'planned' || $offering->enrollmentCourses()->exists()) {
                    throw ValidationException::withMessages(['offering' => 'An activated or enrolled offering is locked to preserve its course scheme and teacher assignments.']);
                }
                if ($offering->assessment_scheme_approved_at && (
                    $offering->curriculum_course_id !== $course->id || $offering->academic_term_id !== $term->id || $offering->program_id !== $program?->id || $offering->section_id !== $section->id
                )) {
                    throw ValidationException::withMessages(['offering' => 'An approved assessment scheme locks the offering course, term and section. You may activate this planned offering without changing its academic identity.']);
                }
            }
            if (CourseOffering::where('academic_term_id', $term->id)->where('program_id', $program?->id)->where('section_id', $section->id)->where('course_id', $course->course_id)->when($offering->exists, fn ($query) => $query->whereKeyNot($offering->id))->exists()) {
                throw ValidationException::withMessages(['curriculum_course_id' => 'This course already has an offering for the selected term and section.']);
            }
            $offering->fill($course->only(CurriculumCourse::SNAPSHOT_FIELDS) + [
                'academic_term_id' => $term->id, 'curriculum_course_id' => $course->id,
                'department_id' => $section->department_id, 'program_id' => $program?->id, 'section_id' => $section->id,
                'semester' => $course->curriculum->semester, 'status' => $data['status'],
            ])->save();
            $offering->teachers()->sync($data['teacher_ids']);

            return $offering->fresh(['term', 'section', 'teachers.user']);
        });
        $notifications = app(AcademicNotificationService::class);
        $newTeachers = $saved->teachers->pluck('user')->filter()->reject(fn ($user) => $previousTeacherUserIds->contains($user->id));
        $notifications->users($newTeachers, 'New course assigned',
            "{$saved->course_code} — {$saved->course_name} has been assigned to you for {$saved->section->name} in {$saved->term->name}.",
            route('teaching.offerings.show', $saved), 'teaching', ['offering_id' => $saved->id]);
        if (! $offering->wasRecentlyCreated && $newTeachers->isNotEmpty()) {
            $notifications->users($notifications->academicStaff(), 'Teacher assignment updated',
                "Teacher assignment changed for {$saved->course_code}, {$saved->section->name}.",
                route('academic.offerings.index'), 'administration', ['offering_id' => $saved->id], $request->user()->id);
        }
    }
}
