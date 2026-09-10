<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    function editCourseForm($id)
    {
        $course = Course::findOrFail($id);

        return view('course.edit-course', [
            'course' => $course
        ]);
    }

    function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $course->update($validated);

        return redirect()
            ->route('allCourses')
            ->with('success', 'course updated successfully!');
    }

    function deleteCourse($id)
    {
        $course = Course::findOrFail($id);

        // Delete image from storage
        if ($course->image && $course->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $course->image);
        }

        // Delete course from database
        $course->delete();

        return redirect()
            ->route('allCourses')
            ->with('success', 'Course deleted successfully!');
    }
}
