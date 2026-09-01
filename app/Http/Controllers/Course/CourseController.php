<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    function create()
    {
        return view('course.add-course');
    }


    function addCourse(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);


        Course::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
        ]);


        return redirect()
            ->route('allCourses')
            ->with('success', 'Course added successfully!');
    }


    function allCourses()
    {
        $courses = Course::all();

        return view('course.courses', [
            'courses' => $courses
        ]);
    }
}
