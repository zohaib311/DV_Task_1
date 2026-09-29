<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Services\AcademicResultCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicResultSubmissionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prepares_only_server_calculated_values_for_an_enrolled_course(): void
    {
        [$student, $enrollment, $enrollmentCourse] = $this->makeEnrollmentCourse();

        $prepared = app(AcademicResultCalculator::class)->prepareEnrollmentResult(
            $enrollment,
            [[
                'student_enrollment_course_id' => $enrollmentCourse->id,
                'obtained_marks' => 82,
                'percentage' => 1,
                'grade' => 'F',
                'grade_point' => 0,
            ]],
            true,
            submittedStudentId: $student->id,
        );

        $this->assertSame('Pass', $prepared['result']['status']);
        $this->assertSame(82.0, $prepared['items'][0]['percentage']);
        $this->assertSame('A-', $prepared['items'][0]['grade']);
        $this->assertSame(3.7, $prepared['items'][0]['grade_point']);
        $this->assertNull($prepared['result']['cgpa']);
    }

    public function test_it_rejects_unknown_courses_invalid_marks_and_incomplete_publish_requests(): void
    {
        [, $enrollment, $enrollmentCourse] = $this->makeEnrollmentCourse();
        $calculator = app(AcademicResultCalculator::class);

        try {
            $calculator->prepareEnrollmentResult($enrollment, [[
                'student_enrollment_course_id' => 99999,
                'obtained_marks' => 80,
            ]], true);
            $this->fail('An unenrolled course should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('courses.0.student_enrollment_course_id', $exception->errors());
        }

        try {
            $calculator->prepareEnrollmentResult($enrollment, [[
                'student_enrollment_course_id' => $enrollmentCourse->id,
                'obtained_marks' => 101,
            ]], true);
            $this->fail('Marks above the enrolled course total should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('courses', $exception->errors());
        }

        try {
            $calculator->prepareEnrollmentResult($enrollment, [], true);
            $this->fail('Publishing without every enrolled course mark should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('courses', $exception->errors());
        }
    }

    public function test_it_blocks_another_result_header_for_the_same_enrollment(): void
    {
        [, $enrollment, $enrollmentCourse] = $this->makeEnrollmentCourse();
        SemesterResult::create([
            'student_semester_enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'status' => 'Draft',
        ]);

        $this->expectException(ValidationException::class);

        app(AcademicResultCalculator::class)->prepareEnrollmentResult($enrollment, [[
            'student_enrollment_course_id' => $enrollmentCourse->id,
            'obtained_marks' => 80,
        ]], false);
    }

    private function makeEnrollmentCourse(): array
    {
        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Ahmed Khan',
            'email' => 'ahmed@example.com',
            'phone' => '03001234567',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'image' => 'default-user.png',
        ]);
        $course = Course::create([
            'code' => 'CS101',
            'name' => 'Programming Fundamentals',
            'description' => 'Core programming course.',
            'credit_hours' => 3,
            'total_marks' => 100,
            'is_active' => true,
        ]);
        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $student->id,
            'department_id' => $department->id,
            'section_id' => $section->id,
            'academic_year' => '2026-2027',
            'semester' => 'Semester 1',
            'status' => 'active',
            'enrolled_at' => today(),
        ]);
        $enrollmentCourse = StudentEnrollmentCourse::create([
            'student_semester_enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'credit_hours' => 3,
            'total_marks' => 100,
        ]);

        return [$student, $enrollment, $enrollmentCourse];
    }
}
