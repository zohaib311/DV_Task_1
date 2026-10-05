<?php

namespace Tests\Feature;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
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
        $this->get(route('editStudentForm', $student))->assertOk()->assertSee('Update Student');
        $this->post(route('addEnrollment'), ['student_id' => $student->id, 'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id])->assertSessionHasNoErrors();

        $enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->assertSame($program->id, $enrollment->program_id);
        $this->assertSame($program->id, $enrollment->courses()->firstOrFail()->offering->program_id);
        $this->get(route('allStudents'))->assertOk()->assertSee('Program');
    }

    public function test_semester_setup_reports_a_catalog_course_conflict_before_the_database_constraint(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::create(['name' => 'Academic Admin', 'email' => 'setup.admin@example.test', 'phone' => '03001110010', 'password' => bcrypt('password')]);
        $admin->assignRole('Academic Admin');
        $department = Department::create(['name' => 'Information Technology']);
        $section = Section::create(['name' => 'IT-A', 'department_id' => $department->id]);
        $program = Program::create(['department_id' => $department->id, 'name' => 'BS Information Technology', 'code' => 'BSIT', 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]);
        $course = Course::create(['code' => 'CS501', 'name' => 'Management', 'description' => 'Shared catalog course', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $otherCourse = Course::create(['code' => 'BBA101', 'name' => 'Principles of Management', 'description' => 'Second course', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-31', 'status' => 'active']);
        $teacher = Teacher::create(['name' => 'Teacher', 'email' => 'setup.teacher@example.test', 'phone' => '03001110011', 'user_id' => $admin->id]);

        $this->actingAs($admin);
        $this->post(route('academic.curricula.store'), ['program_id' => $program->id, 'semester' => 'Semester 1', 'version' => '2026-S1', 'course_ids' => [$course->id, $otherCourse->id]])->assertSessionHasNoErrors();
        $semesterOne = SemesterCurriculum::latest('id')->firstOrFail();
        $this->post(route('academic.curricula.approve', $semesterOne))->assertSessionHasNoErrors();

        $this->post(route('academic.curricula.store'), ['program_id' => $program->id, 'semester' => 'Semester 3', 'version' => '2026-S3', 'course_ids' => [$course->id]])->assertSessionHasNoErrors();
        $semesterThree = SemesterCurriculum::latest('id')->firstOrFail();
        $this->post(route('academic.curricula.approve', $semesterThree))->assertSessionHasNoErrors();

        $existingCourse = $semesterOne->courses()->where('course_id', $course->id)->firstOrFail();
        $missingCourse = $semesterOne->courses()->where('course_id', $otherCourse->id)->firstOrFail();
        $this->post(route('academic.offerings.store'), [
            'academic_term_id' => $term->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'curriculum_course_id' => $existingCourse->id,
            'teacher_ids' => [$teacher->id],
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->post(route('academic.teaching-setup.store'), [
            'academic_term_id' => $term->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'semester_curriculum_id' => $semesterOne->id,
            'teacher_ids' => [$missingCourse->id => $teacher->id],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('course_offerings', 2);

        $this->post(route('academic.teaching-setup.store'), [
            'academic_term_id' => $term->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'semester_curriculum_id' => $semesterOne->id,
        ])->assertSessionHasNoErrors();
        $this->get(route('academic.teaching-setup.create'))->assertOk()->assertSee('Teacher');

        $this->postJson(route('academic.teaching-setup.store'), [
            'academic_term_id' => $term->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'semester_curriculum_id' => $semesterThree->id,
            'teacher_ids' => [$semesterThree->courses()->firstOrFail()->id => $teacher->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('semester_curriculum_id');

        $this->assertDatabaseCount('course_offerings', 2);
        $this->assertTrue(CourseOffering::get()->every(fn (CourseOffering $offering) => $offering->semester === 'Semester 1'));
    }
}
