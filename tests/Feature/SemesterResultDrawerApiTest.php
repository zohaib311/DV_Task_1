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

class SemesterResultDrawerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_drawer_apis_return_student_enrollments_and_course_snapshots(): void
    {
        [$user, $student, $enrollment, $firstCourse, $secondCourse] = $this->makeAcademicRecord();

        $this->actingAs($user)
            ->getJson(route('result.student.enrollments', $student))
            ->assertOk()
            ->assertJsonPath('student.registration_no', $student->registration_no)
            ->assertJsonPath('enrollments.0.id', $enrollment->id)
            ->assertJsonPath('enrollments.0.semester', 'Semester 1');

        $this->actingAs($user)
            ->getJson(route('result.enrollment.data', $enrollment))
            ->assertOk()
            ->assertJsonPath('enrollment.academic_year', '2026-2027')
            ->assertJsonCount(2, 'courses')
            ->assertJsonPath('courses.0.attendance_marks', 10)
            ->assertJsonPath('courses.0.mid_marks', 30)
            ->assertJsonPath('courses.0.final_marks', 60)
            ->assertJsonFragment(['course_id' => $firstCourse->id])
            ->assertJsonFragment(['course_id' => $secondCourse->id]);
    }

    public function test_results_list_api_exposes_an_existing_active_semester_result_before_the_drawer_is_opened(): void
    {
        [$user, $student, $enrollment] = $this->makeAcademicRecord();
        SemesterResult::create([
            'student_semester_enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'status' => 'Pass',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson(route('getStudentsBySection', $student->section_id))
            ->assertOk()
            ->assertJsonPath('0.active_semester_enrollment.id', $enrollment->id)
            ->assertJsonPath('0.active_semester_enrollment.semester_result.status', 'Pass');
    }

    public function test_publish_endpoint_creates_the_semester_header_and_server_calculated_items(): void
    {
        [$user, $student, $enrollment, $firstCourse, $secondCourse] = $this->makeAcademicRecord();
        $enrollmentCourses = $enrollment->courses()->orderBy('id')->get();

        $response = $this->actingAs($user)->postJson(route('result.semester.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'action' => 'publish',
            'courses' => [
                [
                    'student_enrollment_course_id' => $enrollmentCourses[0]->id,
                    'attendance_obtained_marks' => 10,
                    'mid_obtained_marks' => 25,
                    'final_obtained_marks' => 50,
                    'percentage' => 0,
                    'grade' => 'F',
                    'grade_point' => 0,
                ],
                [
                    'student_enrollment_course_id' => $enrollmentCourses[1]->id,
                    'attendance_obtained_marks' => 10,
                    'mid_obtained_marks' => 25,
                    'final_obtained_marks' => 45,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('result.status', 'Pass')
            ->assertJsonPath('result.cgpa', '3.85');

        $this->assertDatabaseHas('semester_results', [
            'student_semester_enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'semester_percentage' => 82.50,
            'sgpa' => 3.85,
            'cgpa' => 3.85,
            'status' => 'Pass',
        ]);
        $this->assertDatabaseHas('semester_result_items', [
            'course_id' => $firstCourse->id,
            'obtained_marks' => 85,
            'percentage' => 85,
            'grade' => 'A',
            'grade_point' => 4,
            'status' => 'Pass',
            'attendance_obtained_marks' => 10,
            'mid_obtained_marks' => 25,
            'final_obtained_marks' => 50,
        ]);
        $this->assertDatabaseHas('semester_result_items', [
            'course_id' => $secondCourse->id,
            'obtained_marks' => 80,
            'percentage' => 80,
            'grade' => 'A-',
            'grade_point' => 3.7,
            'status' => 'Pass',
            'attendance_obtained_marks' => 10,
            'mid_obtained_marks' => 25,
            'final_obtained_marks' => 45,
        ]);
    }

    public function test_draft_endpoint_saves_blank_marks_as_a_draft(): void
    {
        [$user, $student, $enrollment] = $this->makeAcademicRecord();
        $enrollmentCourses = $enrollment->courses()->orderBy('id')->get();

        $this->actingAs($user)
            ->postJson(route('result.semester.store'), [
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'action' => 'draft',
                'courses' => [
                    ['student_enrollment_course_id' => $enrollmentCourses[0]->id, 'attendance_obtained_marks' => 7, 'mid_obtained_marks' => 25, 'final_obtained_marks' => null],
                    ['student_enrollment_course_id' => $enrollmentCourses[1]->id, 'attendance_obtained_marks' => null, 'mid_obtained_marks' => null, 'final_obtained_marks' => null],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('result.status', 'Draft');

        $this->assertDatabaseHas('semester_results', [
            'student_semester_enrollment_id' => $enrollment->id,
            'status' => 'Draft',
            'semester_percentage' => null,
            'sgpa' => null,
            'cgpa' => null,
        ]);
    }

    private function makeAcademicRecord(): array
    {
        $user = User::create([
            'name' => 'Academic Admin',
            'email' => 'academic-admin@example.com',
            'phone' => '03007654321',
            'password' => bcrypt('password'),
            'image' => 'default-user.png',
        ]);
        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Ali Raza',
            'email' => 'ali@example.com',
            'phone' => '03001234567',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'image' => 'default-user.png',
        ]);
        $firstCourse = Course::create([
            'code' => 'CS101', 'name' => 'Programming', 'description' => 'Programming fundamentals.',
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);
        $secondCourse = Course::create([
            'code' => 'CS102', 'name' => 'Databases', 'description' => 'Database fundamentals.',
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);
        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $student->id, 'department_id' => $department->id, 'section_id' => $section->id,
            'academic_year' => '2026-2027', 'semester' => 'Semester 1', 'status' => 'active', 'enrolled_at' => today(),
        ]);
        foreach ([$firstCourse, $secondCourse] as $course) {
            StudentEnrollmentCourse::create([
                'student_semester_enrollment_id' => $enrollment->id,
                'course_id' => $course->id,
                'credit_hours' => $course->credit_hours,
                'total_marks' => $course->total_marks,
                'attendance_marks' => $course->attendance_marks,
                'mid_marks' => $course->mid_marks,
                'final_marks' => $course->final_marks,
            ]);
        }

        return [$user, $student, $enrollment, $firstCourse, $secondCourse];
    }
}
