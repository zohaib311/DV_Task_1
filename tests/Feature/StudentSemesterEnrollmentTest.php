<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSemesterEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_semester_enrollment_preserves_course_snapshots_and_syncs_the_current_student_profile(): void
    {
        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Ali Raza',
            'email' => 'ali@example.com',
            'phone' => '03001234567',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'semester' => 'Semester 1',
            'course_ids' => [],
            'image' => 'default-user.png',
        ]);
        $firstCourse = Course::create([
            'code' => 'CS101',
            'name' => 'Programming',
            'description' => 'Programming fundamentals.',
            'credit_hours' => 3,
            'total_marks' => 100,
            'is_active' => true,
        ]);
        $secondCourse = Course::create([
            'code' => 'CS102',
            'name' => 'Mathematics',
            'description' => 'Discrete mathematics.',
            'credit_hours' => 4,
            'total_marks' => 150,
            'is_active' => true,
        ]);

        $user = new \App\Models\User([
            'name' => 'Academic Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '03007654321',
            'image' => 'default-user.png',
        ]);
        $user->save();

        $response = $this->actingAs($user)->post(route('addEnrollment'), [
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Semester 2',
            'course_ids' => [$firstCourse->id, $secondCourse->id],
        ]);

        $response->assertRedirect(route('allEnrollments'));
        $this->assertDatabaseHas('student_semester_enrollments', [
            'student_id' => $student->id,
            'department_id' => $department->id,
            'section_id' => $section->id,
            'academic_year' => '2026-2027',
            'semester' => 'Semester 2',
            'status' => 'active',
        ]);

        $enrollment = StudentSemesterEnrollment::with('courses')->firstOrFail();
        $this->assertCount(2, $enrollment->courses);
        $this->assertDatabaseHas('student_enrollment_courses', [
            'student_semester_enrollment_id' => $enrollment->id,
            'course_id' => $secondCourse->id,
            'credit_hours' => 4,
            'total_marks' => 150,
        ]);

        $student->refresh();
        $this->assertSame('Semester 2', $student->semester);
        $this->assertSame([$firstCourse->id, $secondCourse->id], $student->course_ids);
    }
}
