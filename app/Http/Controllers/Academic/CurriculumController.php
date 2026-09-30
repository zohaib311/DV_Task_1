<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CurriculumController extends Controller
{
    public function index()
    {
        return view('academic.curricula', ['curricula' => SemesterCurriculum::with('department')->withCount('courses')->latest()->paginate(20)]);
    }

    public function create()
    {
        return $this->form(new SemesterCurriculum);
    }

    public function edit(SemesterCurriculum $curriculum)
    {
        return $this->form($curriculum->load('courses'));
    }

    private function form(SemesterCurriculum $curriculum)
    {
        return view('academic.curriculum-form', [
            'curriculum' => $curriculum,
            'departments' => Department::orderBy('name')->get(),
            'courses' => Course::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $curriculum = $this->save($request, new SemesterCurriculum);

        return redirect()->route('academic.curricula.edit', $curriculum)->with('success', 'Curriculum draft saved. Review the course plan before approval.');
    }

    public function update(Request $request, SemesterCurriculum $curriculum)
    {
        $this->save($request, $curriculum);

        return back()->with('success', 'Curriculum draft updated successfully.');
    }

    private function save(Request $request, SemesterCurriculum $curriculum): SemesterCurriculum
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'semester' => ['required', Rule::in(array_map(fn ($i) => "Semester $i", range(1, 8)))],
            'version' => ['required', 'string', 'max:60', Rule::unique('semester_curricula')->where('department_id', $request->input('department_id'))->where('semester', $request->input('semester'))->ignore($curriculum->id)],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['required', 'integer', 'distinct', 'exists:courses,id'],
            'elective_ids' => ['nullable', 'array'],
            'elective_ids.*' => ['integer', 'distinct', Rule::in($request->input('course_ids', []))],
        ]);

        return DB::transaction(function () use ($curriculum, $data) {
            if ($curriculum->exists) {
                $curriculum = SemesterCurriculum::lockForUpdate()->findOrFail($curriculum->id);
                $this->requireDraft($curriculum);
            }
            $courses = Course::whereIn('id', $data['course_ids'])->where('is_active', true)->lockForUpdate()->get();
            if ($courses->count() !== count($data['course_ids']) || $courses->contains(fn ($course) => $course->total_marks <= 0 || $course->credit_hours <= 0 || $course->attendance_marks + $course->mid_marks + $course->final_marks !== $course->total_marks)) {
                throw ValidationException::withMessages(['course_ids' => 'Select active courses with positive credit hours and a valid assessment scheme.']);
            }
            $curriculum->fill(collect($data)->only(['department_id', 'semester', 'version'])->all())->save();
            $curriculum->courses()->delete(); // Only unpublished draft rows can be replaced.
            $curriculum->courses()->createMany($courses->map(fn ($course) => [
                'course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
                'credit_hours' => $course->credit_hours, 'total_marks' => $course->total_marks,
                'attendance_marks' => $course->attendance_marks, 'mid_marks' => $course->mid_marks, 'final_marks' => $course->final_marks,
                'type' => in_array($course->id, $data['elective_ids'] ?? []) ? 'elective' : 'required',
            ])->all());

            return $curriculum;
        });
    }

    public function approve(SemesterCurriculum $curriculum)
    {
        DB::transaction(function () use ($curriculum) {
            $curriculum = SemesterCurriculum::lockForUpdate()->findOrFail($curriculum->id);
            $this->requireDraft($curriculum);
            if (! $curriculum->courses()->exists()) {
                throw ValidationException::withMessages(['curriculum' => 'Add courses before approving the curriculum.']);
            }
            $curriculum->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()]);
        });

        return back()->with('success', 'Curriculum approved. This version is locked; create a new version for future changes.');
    }

    private function requireDraft(SemesterCurriculum $curriculum): void
    {
        if ($curriculum->status !== 'draft') {
            throw ValidationException::withMessages(['curriculum' => 'Approved curricula are read-only. Create a new version to change the course plan.']);
        }
    }
}
