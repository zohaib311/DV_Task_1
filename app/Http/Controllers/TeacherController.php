<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    function create()
    {
        return view('teachers.add-teacher');
    }

    function allTeachers()
    {
        $teachers = Teacher::all();

        return view('teachers.teachers', [
            'teachers' => $teachers
        ]);
    }

    function addTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string',
            'email'  => 'required|email|unique:teachers,email',
            'phone'  => 'required|digits:11',
            'course' => 'required|string|min:2|max:100',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fileName = basename($path);
        } else {

            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        Teacher::create($validated);

        return redirect()
            ->route('allTeachers')
            ->with('success', 'Teacher added successfully!');
    }

    function editTeacherForm($id)
    {
        $teacher = Teacher::findOrFail($id);

        return view('teachers.edit-teacher', [
            'teacher' => $teacher
        ]);
    }

    function updateTeacher(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'required|digits:11',
            'course' => 'string|min:2|max:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $validated['image'] = basename($path);
        }

        $teacher->update($validated);

        return redirect()
            ->route('allTeachers')
            ->with('success', 'Teacher updated successfully!');
    }

    function deleteTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);

        // Delete image from storage
        if ($teacher->image && $teacher->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $teacher->image);
        }

        // Delete teacher from database
        $teacher->delete();

        return redirect()
            ->route('allTeachers')
            ->with('success', 'teacher deleted successfully!');
    }
}
