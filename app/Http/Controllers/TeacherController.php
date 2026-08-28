<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    function create()
    {
        return view('add-teacher');
    }

    function allTeachers()
    {
        $teachers = Teacher::all();

        return view('teachers', [
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
}
