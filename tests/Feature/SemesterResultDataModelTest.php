<?php

namespace Tests\Feature;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterResultDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_semester_result_preserves_a_course_result_snapshot(): void
    {
        [$student, $course, $enrollmentCourse] = $this->makeEnrollmentCourse();

        $semesterResult = SemesterResult::create([
            'student_semester_enrollment_id' => $enrollmentCourse->student_semester_enrollment_id,
            'student_id' => $student->id,
            'semester_percentage' => 84.00,
            'sgpa' => 3.70,
            'cgpa' => 3.70,
            'status' => 'Pass',
            'published_at' => now(),
        ]);

        $semesterResult->items()->create([
            'student_enrollment_course_id' => $enrollmentCourse->id,
            'course_id' => $course->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'credit_hours' => $enrollmentCourse->credit_hours,
            'total_marks' => $enrollmentCourse->total_marks,
            'obtained_marks' => 84,
            'percentage' => 84,
            'grade' => 'A-',
            'grade_point' => 3.70,
            'status' => 'Pass',
        ]);

        $course->update(['name' => 'Renamed Course', 'credit_hours' => 4]);
        $item = $semesterResult->items()->firstOrFail();

        $this->assertSame('Academic Writing', $item->course_name);
        $this->assertSame('3.0', $item->credit_hours);
        $this->assertSame(100, $item->total_marks);
        $this->assertNull($item->attendance_obtained_marks);
        $this->assertNull($item->mid_obtained_marks);
        $this->assertNull($item->final_obtained_marks);
        $this->assertSame('Pass', $semesterResult->status);
        $this->assertNotNull($semesterResult->published_at);
    }

    public function test_an_enrollment_can_have_only_one_semester_result_header(): void
    {
        [$student, , $enrollmentCourse] = $this->makeEnrollmentCourse();

        SemesterResult::create([
            'student_semester_enrollment_id' => $enrollmentCourse->student_semester_enrollment_id,
            'student_id' => $student->id,
            'status' => 'Draft',
        ]);

        $this->expectException(QueryException::class);

        SemesterResult::create([
            'student_semester_enrollment_id' => $enrollmentCourse->student_semester_enrollment_id,
            'student_id' => $student->id,
            'status' => 'Draft',
        ]);
    }

    private function makeEnrollmentCourse(): array
    {
        $department = Department::create(['name' => 'English']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Hina Malik',
            'email' => 'hina@example.com',
            'phone' => '03001234567',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'image' => 'default-user.png',
        ]);
        $course = Course::create([
            'code' => 'ENG101',
            'name' => 'Academic Writing',
            'description' => 'Writing fundamentals.',
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

        return [$student, $course, $enrollmentCourse];
    }
}
