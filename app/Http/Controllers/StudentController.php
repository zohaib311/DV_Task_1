<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    function create()
    {
        return view('add-student');
    }

    function allUsers(Request $request)
    {
        $student = Student::all();
        return view('students', ['students' => $student]);
    }

    function addUser(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|digits:11',
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

        return redirect()->back()->with('success', 'User added successfully!');
    }
}
