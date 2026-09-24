<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Result\Result;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function allResults()
    {
        $results = Result::with(['student', 'course', 'section.department'])->get();
        return view('results.results', compact('results'));
    }

    public function create()
    {
        $departments = Department::all();
        $courses     = Course::all();

        return view('results.add-result', compact('departments', 'courses'));
    }

    public function getSectionsByDepartment($department_id)
    {
        $sections = Section::where('department_id', $department_id)->get();
        return response()->json($sections);
    }

    public function getStudentsBySection($section_id)
    {
        $students = Student::where('section_id', $section_id)->get();
        return response()->json($students);
    }

    public function addResult(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id'  => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
            'percentage' => 'required|numeric|min:0|max:100',
            'gpa'        => 'required|numeric|min:0|max:4.0',
            'cgpa'       => 'required|numeric|min:0|max:4.0',
            'grade'      => 'required|string|max:10',
            'status'     => 'required|in:Pass,Fail',
        ]);

        Result::create($validated);

        return redirect()
            ->route('allResults')
            ->with('success', 'Result added successfully!');
    }

    public function editResultForm($id)
    {
        $result   = Result::findOrFail($id);
        $students = Student::all();
        $courses  = Course::all();
        $sections = Section::with('department')->get();

        return view('results.edit-result', compact('result', 'students', 'courses', 'sections'));
    }

    public function updateResult(Request $request, $id)
    {
        $result = Result::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id'  => 'required|exists:courses,id',
            'section_id' => 'required|exists:sections,id',
            'percentage' => 'required|numeric|min:0|max:100',
            'gpa'        => 'required|numeric|min:0|max:4.0',
            'cgpa'       => 'required|numeric|min:0|max:4.0',
            'grade'      => 'required|string|max:10',
            'status'     => 'required|in:Pass,Fail',
        ]);

        $result->update($validated);

        return redirect()
            ->route('allResults')
            ->with('success', 'Result updated successfully!');
    }

    public function deleteResult($id)
    {
        $result = Result::findOrFail($id);
        $result->delete();

        return redirect()
            ->route('allResults')
            ->with('success', 'Result deleted successfully!');
    }
}
