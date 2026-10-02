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

class SemesterTeachingSetupController extends Controller
{
    public function create()
    {
        $curricula = SemesterCurriculum::with(['department', 'program', 'courses'])
            ->where('status', 'approved')->whereNotNull('program_id')
            ->orderBy('program_id')->orderBy('semester')->get();
        $teachers = Teacher::with('user')->whereHas('user')->orderBy('name')->get();

        return view('academic.semester-teaching-setup', [
            'terms' => AcademicTerm::with('academicYear')->where('status', 'active')->orderByDesc('starts_on')->get(),
            'curricula' => $curricula,
            'programs' => Program::with('department')->where('is_active', true)->orderBy('name')->get(),
            'sections' => Section::with('department')->orderBy('name')->get(),
            'teachers' => $teachers,
            'curriculaPayload' => $curricula->mapWithKeys(function (SemesterCurriculum $curriculum) {
                return [$curriculum->id => $curriculum->courses->map(fn ($course) => $course->only([
                    'id', 'course_code', 'course_name', 'type', 'credit_hours',
                    'attendance_marks', 'mid_marks', 'final_marks', 'total_marks',
                ]))->values()];
            }),
            'teachersPayload' => $teachers->map(fn (Teacher $teacher) => $teacher->only(['id', 'name']))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_term_id' => ['required', 'integer', 'exists:academic_terms,id'],
            'semester_curriculum_id' => ['required', 'integer', 'exists:semester_curricula,id'],
            'program_id' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'teacher_ids' => ['required', 'array'],
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
            $courseIds = $curriculum->courses->pluck('id');
            if ($courseIds->isEmpty() || $courseIds->diff(array_keys($data['teacher_ids']))->isNotEmpty()) {
                throw ValidationException::withMessages(['teacher_ids' => 'Assign one teacher to every course in this semester plan.']);
            }
            $teacherIds = collect($data['teacher_ids'])->values()->unique();
            if (Teacher::whereIn('id', $teacherIds)->whereHas('user')->count() !== $teacherIds->count()) {
                throw ValidationException::withMessages(['teacher_ids' => 'Each selected teacher must have a linked Teacher login account.']);
            }
            $existing = CourseOffering::where('academic_term_id', $term->id)->where('program_id', $program?->id)->where('section_id', $section->id)
                ->whereIn('curriculum_course_id', $courseIds)->lockForUpdate()->get();
            if ($existing->isNotEmpty()) {
                throw ValidationException::withMessages(['semester_curriculum_id' => 'This semester teaching setup already has course offerings for: '.$existing->pluck('course_code')->implode(', ').'. Review the existing offerings instead of creating duplicates.']);
            }

            foreach ($curriculum->courses as $course) {
                if (! $course->course?->is_active) {
                    throw ValidationException::withMessages(['semester_curriculum_id' => "{$course->course_code} is inactive and cannot be prepared for teaching."]);
                }
            }

            foreach ($curriculum->courses as $course) {
                $offering = CourseOffering::create($course->only(\App\Models\Academic\CurriculumCourse::SNAPSHOT_FIELDS) + [
                    'academic_term_id' => $term->id,
                    'curriculum_course_id' => $course->id,
                    'department_id' => $section->department_id,
                    'program_id' => $program?->id,
                    'section_id' => $section->id,
                    'semester' => $curriculum->semester,
                    'status' => 'active',
                ]);
                $offering->teachers()->attach($data['teacher_ids'][$course->id]);
            }

            return $curriculum->courses->count();
        });

        return redirect()->route('academic.offerings.index')->with('success', "Semester teaching setup is ready. {$created} courses were activated and assigned to teachers.");
    }
}
