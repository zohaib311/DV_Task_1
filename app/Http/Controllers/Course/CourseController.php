<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function create()
    {
        return view('course.add-course');
    }

    public function addCourse(Request $request)
    {
        $validated = $request->validate($this->courseRules());
        $this->validateAssessmentScheme($validated);

        Course::create($validated);

        return redirect()
            ->route('allCourses')
            ->with('success', 'Course added successfully!');
    }

    public function allCourses()
    {
        $courses = Course::orderByDesc('is_active')->orderBy('name')->get();

        return view('course.courses', [
            'courses' => $courses,
        ]);
    }

    public function editCourseForm($id)
    {
        $course = Course::findOrFail($id);

        return view('course.edit-course', [
            'course' => $course,
        ]);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate($this->courseRules($course));
        $this->validateAssessmentScheme($validated);

        $course->update($validated);

        return redirect()
            ->route('allCourses')
            ->with('success', 'course updated successfully!');
    }

    private function courseRules(?Course $course = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'credit_hours' => ['required', 'numeric', 'min:0.5', 'max:12'],
            'total_marks' => ['required', 'integer', 'min:1', 'max:1000'],
            'attendance_marks' => ['required', 'integer', 'min:0', 'max:1000'],
            'mid_marks' => ['required', 'integer', 'min:0', 'max:1000'],
            'final_marks' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function validateAssessmentScheme(array $validated): void
    {
        $attendanceMarks = (int) $validated['attendance_marks'];
        $midMarks = (int) $validated['mid_marks'];
        $finalMarks = (int) $validated['final_marks'];
        $totalMarks = (int) $validated['total_marks'];
        $assessmentTotal = $attendanceMarks + $midMarks + $finalMarks;

        if ($assessmentTotal !== $totalMarks) {
            throw ValidationException::withMessages([
                'assessment_scheme' => "Attendance, Midterm and Final marks must equal the course total ({$totalMarks}). Current assessment total is {$assessmentTotal}.",
            ]);
        }
    }

    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);

        // Delete image from storage
        if ($course->image && $course->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/'.$course->image);
        }

        // Delete course from database
        $course->delete();

        return redirect()
            ->route('allCourses')
            ->with('success', 'Course deleted successfully!');
    }
}
