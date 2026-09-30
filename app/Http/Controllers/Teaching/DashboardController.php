<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TeacherWorkspace $workspace)
    {
        $teacher = $workspace->profile($request->user());
        $assigned = $workspace->offerings($teacher);

        return view('teaching.dashboard', [
            'teacher' => $teacher,
            'offeringCount' => (clone $assigned)->count(),
            'activeCount' => (clone $assigned)->where('status', 'active')->count(),
            'studentCount' => StudentEnrollmentCourse::whereIn('course_offering_id', (clone $assigned)->select('course_offerings.id'))
                ->join('student_semester_enrollments', 'student_semester_enrollments.id', '=', 'student_enrollment_courses.student_semester_enrollment_id')
                ->distinct()->count('student_semester_enrollments.student_id'),
            'offerings' => $assigned->with(['term.academicYear', 'section', 'department'])->withCount('enrollmentCourses')
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")->orderByDesc('id')->limit(5)->get(),
        ]);
    }
}
