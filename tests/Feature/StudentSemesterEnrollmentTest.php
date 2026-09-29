<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
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
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);
        $secondCourse = Course::create([
            'code' => 'CS102',
            'name' => 'Mathematics',
            'description' => 'Discrete mathematics.',
            'credit_hours' => 4,
            'total_marks' => 150,
            'attendance_marks' => 15,
            'mid_marks' => 45,
            'final_marks' => 90,
            'is_active' => true,
        ]);

        $user = new User([
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
            'attendance_marks' => 15,
            'mid_marks' => 45,
            'final_marks' => 90,
        ]);

        $student->refresh();
        $this->assertSame('Semester 2', $student->semester);
        $this->assertSame([$firstCourse->id, $secondCourse->id], $student->course_ids);

        $this->actingAs($user)
            ->get(route('allStudents'))
            ->assertOk()
            ->assertSee('Semester 2')
            ->assertSee('2026-2027')
            ->assertSee('Programming');
    }

    public function test_a_student_profile_is_created_before_the_first_semester_enrollment(): void
    {
        $department = Department::create(['name' => 'Software Engineering']);
        $section = Section::create(['name' => 'B', 'department_id' => $department->id]);
        $user = new User([
            'name' => 'Academic Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '03007654321',
            'image' => 'default-user.png',
        ]);
        $user->save();

        $response = $this->actingAs($user)->post(route('addStudent'), [
            'name' => 'Sara Khan',
            'email' => 'sara@example.com',
            'phone' => '03001112233',
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);

        $student = Student::where('email', 'sara@example.com')->firstOrFail();

        $response->assertRedirect(route('addEnrollmentForm', ['student_id' => $student->id]));
        $this->assertNull($student->semester);
        $this->assertNull($student->course_ids);
    }

    public function test_a_published_passing_enrollment_can_be_promoted_with_explicit_next_semester_courses(): void
    {
        [$user, $student, $currentEnrollment, $currentCourse, $nextCourse] = $this->makePromotionRecord('Pass');

        $this->actingAs($user)
            ->post(route('promoteEnrollment', $currentEnrollment), [
                'academic_year' => '2026-2027',
                'semester' => 'Semester 2',
                'course_ids' => [$nextCourse->id],
            ])
            ->assertRedirect(route('allEnrollments'));

        $this->assertDatabaseHas('student_semester_enrollments', [
            'id' => $currentEnrollment->id,
            'status' => 'promoted',
        ]);
        $this->assertDatabaseHas('student_semester_enrollments', [
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Semester 2',
            'status' => 'active',
        ]);

        $nextEnrollment = StudentSemesterEnrollment::where('student_id', $student->id)
            ->where('semester', 'Semester 2')
            ->firstOrFail();
        $this->assertDatabaseHas('student_enrollment_courses', [
            'student_semester_enrollment_id' => $nextEnrollment->id,
            'course_id' => $nextCourse->id,
        ]);
        $this->assertDatabaseHas('student_enrollment_courses', [
            'student_semester_enrollment_id' => $currentEnrollment->id,
            'course_id' => $currentCourse->id,
        ]);
        $student->refresh();
        $this->assertSame('Semester 2', $student->semester);
        $this->assertSame([$nextCourse->id], $student->course_ids);
    }

    public function test_a_failed_result_cannot_be_promoted_under_the_default_policy(): void
    {
        [$user, $student, $currentEnrollment, , $nextCourse] = $this->makePromotionRecord('Fail');

        $this->actingAs($user)
            ->post(route('promoteEnrollment', $currentEnrollment), [
                'academic_year' => '2026-2027',
                'semester' => 'Semester 2',
                'course_ids' => [$nextCourse->id],
            ])
            ->assertSessionHasErrors('promotion');

        $this->assertDatabaseCount('student_semester_enrollments', 1);
        $this->assertSame('active', $currentEnrollment->refresh()->status);
    }

    private function makePromotionRecord(string $resultStatus): array
    {
        $department = Department::create(['name' => 'Information Technology']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $user = User::create([
            'name' => 'Academic Admin', 'email' => 'promotion-admin@example.com', 'phone' => '03007654321',
            'password' => bcrypt('password'), 'image' => 'default-user.png',
        ]);
        $student = Student::create([
            'name' => 'Promotion Student', 'email' => 'promotion@example.com', 'phone' => '03001234567',
            'department_id' => $department->id, 'section_id' => $section->id, 'semester' => 'Semester 1',
            'course_ids' => [], 'image' => 'default-user.png',
        ]);
        $currentCourse = Course::create([
            'code' => 'IT101', 'name' => 'Foundations', 'description' => 'Foundation course.', 'credit_hours' => 3,
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);
        $nextCourse = Course::create([
            'code' => 'IT201', 'name' => 'Systems', 'description' => 'Systems course.', 'credit_hours' => 3,
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);
        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $student->id, 'department_id' => $department->id, 'section_id' => $section->id,
            'academic_year' => '2026-2027', 'semester' => 'Semester 1', 'status' => 'active', 'enrolled_at' => today(),
        ]);
        StudentEnrollmentCourse::create([
            'student_semester_enrollment_id' => $enrollment->id, 'course_id' => $currentCourse->id,
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60,
        ]);
        SemesterResult::create([
            'student_semester_enrollment_id' => $enrollment->id, 'student_id' => $student->id,
            'status' => $resultStatus, 'published_at' => now(),
        ]);

        return [$user, $student, $enrollment, $currentCourse, $nextCourse];
    }
}
