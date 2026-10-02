<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Department\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProgramController extends Controller
{
    public function index()
    {
        return view('academic.programs.index', [
            'programs' => Program::with('department')->withCount(['curricula', 'offerings', 'students'])->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Program::create($this->validated($request));

        return back()->with('success', 'Program created. Build its eight-semester curriculum next.');
    }

    public function update(Request $request, Program $program)
    {
        if ($program->offerings()->exists() && $request->integer('department_id') !== $program->department_id) {
            throw ValidationException::withMessages(['department_id' => 'A program with course offerings cannot be moved to another department.']);
        }
        $program->update($this->validated($request, $program));

        return back()->with('success', 'Program updated successfully.');
    }

    private function validated(Request $request, ?Program $program = null): array
    {
        return $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', Rule::unique('academic_programs', 'code')->ignore($program)],
            'duration_years' => ['required', 'integer', 'between:1,8'],
            'total_semesters' => ['required', 'integer', 'between:1,16'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
