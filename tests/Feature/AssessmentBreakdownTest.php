<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentBreakdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_form_requires_assessment_components_to_equal_course_total(): void
    {
        $user = $this->makeUser();
        $baseCourse = [
            'code' => 'CS201',
            'name' => 'Data Structures',
            'description' => 'Core data structures course.',
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 50,
            'is_active' => 1,
        ];

        $this->actingAs($user)->post(route('addCourse'), $baseCourse)
            ->assertSessionHasErrors('assessment_scheme');

        $baseCourse['final_marks'] = 60;
        $this->actingAs($user)->post(route('addCourse'), $baseCourse)
            ->assertRedirect(route('allCourses'));

        $this->assertDatabaseHas('courses', [
            'code' => 'CS201',
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
        ]);
    }

    public function test_enrollment_and_result_component_snapshots_survive_later_course_changes(): void
    {
        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Snapshot Student', 'email' => 'snapshot@example.com', 'phone' => '03001234567',
            'department_id' => $department->id, 'section_id' => $section->id, 'image' => 'default-user.png',
        ]);
        $course = Course::create([
            'code' => 'CS301', 'name' => 'Algorithms', 'description' => 'Algorithms course.', 'credit_hours' => 3,
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);
        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $student->id, 'department_id' => $department->id, 'section_id' => $section->id,
            'academic_year' => '2026-2027', 'semester' => 'Semester 1', 'status' => 'active', 'enrolled_at' => today(),
        ]);
        $enrollmentCourse = StudentEnrollmentCourse::create([
            'student_semester_enrollment_id' => $enrollment->id, 'course_id' => $course->id,
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60,
        ]);
        $result = SemesterResult::create([
            'student_semester_enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'status' => 'Draft',
        ]);
        $item = $result->items()->create([
            'student_enrollment_course_id' => $enrollmentCourse->id, 'course_id' => $course->id,
            'course_code' => $course->code, 'course_name' => $course->name, 'credit_hours' => 3, 'total_marks' => 100,
            'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60,
            'attendance_obtained_marks' => 8, 'mid_obtained_marks' => 24, 'final_obtained_marks' => 50,
            'obtained_marks' => 82, 'percentage' => 82, 'grade' => 'A-', 'grade_point' => 3.7, 'status' => 'Pass',
        ]);

        $course->update(['attendance_marks' => 20, 'mid_marks' => 20, 'final_marks' => 60]);

        $this->assertSame(10, $enrollmentCourse->refresh()->attendance_marks);
        $this->assertSame(30, $enrollmentCourse->refresh()->mid_marks);
        $this->assertSame(60, $enrollmentCourse->refresh()->final_marks);
        $this->assertSame(10, $item->refresh()->attendance_marks);
        $this->assertSame(8.0, (float) $item->attendance_obtained_marks);
        $this->assertSame(50.0, (float) $item->final_obtained_marks);
    }

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Academic Admin', 'email' => 'course-admin@example.com', 'phone' => '03007654321',
            'password' => bcrypt('password'), 'image' => 'default-user.png',
        ]);
    }
}
