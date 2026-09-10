<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    function create()
    {
        return view('students.add-student');
    }

    function allstudents(Request $request)
    {
        $student = Student::all();
        return view('students.students', ['students' => $student]);
    }

    function addStudent(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|size:11',
            'class' => 'required|string|min:2|max:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fullFileName = explode('/', $path);

            $fileName = $fullFileName[1];
        } else {

            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        Student::create($validated);

        return redirect()->route('allStudents')->with('success', 'User added successfully!');
    }

    function editStudentForm($id)
    {
        $student = Student::findOrFail($id);

        return view('students.edit-student', [
            'student' => $student
        ]);
    }

    function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone' => 'required|digits:11',
            'class' => 'required|string|min:2|max:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {

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

        // Delete image from storage
        if ($student->image && $student->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $student->image);
        }

        // Delete student from database
        $student->delete();

        return redirect()
            ->route('allStudents')
            ->with('success', 'Student deleted successfully!');
    }
}
