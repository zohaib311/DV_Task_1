<?php

namespace App\Http\Controllers;

use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    function create()
    {
        $departments = Department::all();
        $sections    = Section::with('department')->get();

        return view('students.add-student', compact('departments', 'sections'));
    }

    function allstudents(Request $request)
    {
        $students    = Student::with(['department', 'section', 'activeSemesterEnrollment.courses.course'])->get();
        $departments = Department::all();
        $sections    = Section::with('department')->get();

        return view('students.students', compact('students', 'departments', 'sections'));
    }

    function addStudent(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email',
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $fileName = basename($path);
        } else {
            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        $student = Student::create($validated);

        return redirect()
            ->route('addEnrollmentForm', ['student_id' => $student->id])
            ->with('success', 'Student profile created. Create the first semester enrollment next.');
    }

    function editStudentForm($id)
    {
        $student     = Student::findOrFail($id);
        $departments = Department::all();
        $sections    = Section::with('department')->get();
        $activeEnrollment = $student->semesterEnrollments()
            ->with('courses.course')
            ->where('status', 'active')
            ->latest('enrolled_at')
            ->first();

        return view('students.edit-student', compact('student', 'departments', 'sections', 'activeEnrollment'));
    }

    function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'phone'         => 'required|digits:11',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($student->image && $student->image !== 'default-user.png') {
                Storage::disk('public')->delete('images/' . $student->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = basename($path);
        }

        $student->update($validated);

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student updated successfully!');
    }

    function deleteStudent($id)
    {
        $student = Student::findOrFail($id);

        if ($student->image && $student->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $student->image);
        }

        $student->delete();

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student deleted successfully!');
    }
}
