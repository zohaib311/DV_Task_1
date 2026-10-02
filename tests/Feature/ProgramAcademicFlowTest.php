<?php

namespace Tests\Feature;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Program;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramAcademicFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_scopes_curriculum_offerings_and_student_enrollment(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::create(['name' => 'Academic Admin', 'email' => 'program.admin@example.test', 'phone' => '03001110000', 'password' => bcrypt('password')]);
        $admin->assignRole('Academic Admin');
        $department = Department::create(['name' => 'Computing']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $program = Program::create(['department_id' => $department->id, 'name' => 'BS Computer Science', 'code' => 'BSCS', 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]);
        $course = Course::create(['code' => 'CS101', 'name' => 'Programming Fundamentals', 'description' => 'Core course', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-31', 'status' => 'active']);

        $this->actingAs($admin)->post(route('academic.curricula.store'), ['program_id' => $program->id, 'semester' => 'Semester 1', 'version' => '2026 Intake', 'course_ids' => [$course->id]])->assertSessionHasNoErrors();
        $curriculum = SemesterCurriculum::firstOrFail();
        $this->assertSame($program->id, $curriculum->program_id);
        $this->post(route('academic.curricula.approve', $curriculum))->assertSessionHasNoErrors();

        $teacher = Teacher::create(['name' => 'Teacher', 'email' => 'program.teacher@example.test', 'phone' => '03001110001', 'user_id' => $admin->id]);
        $this->post(route('academic.offerings.store'), ['academic_term_id' => $term->id, 'program_id' => $program->id, 'curriculum_course_id' => $curriculum->courses()->firstOrFail()->id, 'section_id' => $section->id, 'teacher_ids' => [$teacher->id], 'status' => 'active'])->assertSessionHasNoErrors();

        $student = Student::create(['name' => 'Student', 'email' => 'program.student@example.test', 'phone' => '03001110002', 'department_id' => $department->id, 'program_id' => $program->id, 'section_id' => $section->id]);
        $this->post(route('addEnrollment'), ['student_id' => $student->id, 'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id])->assertSessionHasNoErrors();

        $enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->assertSame($program->id, $enrollment->program_id);
        $this->assertSame($program->id, $enrollment->courses()->firstOrFail()->offering->program_id);
        $this->get(route('allStudents'))->assertOk()->assertSee('Program');
    }
}
