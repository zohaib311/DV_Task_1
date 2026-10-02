<?php

namespace Tests\Feature;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
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

class AutomaticAcademicFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $department;
    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
        $this->admin = $this->user('Academic Admin', 'admin.flow@example.test', '03007000001');
        $this->admin->assignRole('Academic Admin');
        $this->department = Department::create(['name' => 'Automatic Computing']);
        $this->section = Section::create(['name' => 'A', 'department_id' => $this->department->id]);
    }

    public function test_admission_creates_student_portal_account_in_the_same_flow(): void
    {
        $this->actingAs($this->admin)->post(route('addStudent'), [
            'name' => 'New Student', 'email' => 'new.student@example.test', 'phone' => '03007000002',
            'department_id' => $this->department->id, 'section_id' => $this->section->id,
            'create_portal_account' => 1, 'account_password' => 'Student@2026', 'account_password_confirmation' => 'Student@2026',
        ])->assertSessionHasNoErrors();

        $account = User::where('email', 'new.student@example.test')->firstOrFail();
        $this->assertTrue($account->hasRole('Student'));
        $this->assertDatabaseHas('students', ['user_id' => $account->id, 'email' => $account->email]);
        $student = Student::where('user_id', $account->id)->firstOrFail();
        $this->put(route('updateStudent', $student), [
            'user_id' => $account->id, 'name' => 'Ignored browser name', 'email' => 'ignored@example.test', 'phone' => '03009999999',
            'department_id' => $this->department->id, 'section_id' => $this->section->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($account->email, $student->fresh()->email);
    }

    public function test_admin_prepares_semester_once_and_enrollment_assigns_required_courses_automatically(): void
    {
        $term = $this->term();
        $courses = collect([
            $this->course('AUTO101', 'Automatic Programming'),
            $this->course('AUTO102', 'Automatic Mathematics'),
        ]);
        $curriculum = SemesterCurriculum::create(['department_id' => $this->department->id, 'semester' => 'Semester 1', 'version' => '2026 Intake', 'status' => 'approved', 'approved_at' => now()]);
        $curriculum->courses()->createMany($courses->map(fn ($course) => [
            'course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
            'credit_hours' => $course->credit_hours, 'total_marks' => $course->total_marks,
            'attendance_marks' => $course->attendance_marks, 'mid_marks' => $course->mid_marks, 'final_marks' => $course->final_marks, 'type' => 'required',
        ])->all());
        $teacherAccount = $this->user('Teaching Faculty', 'faculty@example.test', '03007000003');
        $teacherAccount->assignRole('Teacher');
        $teacher = Teacher::create(['name' => $teacherAccount->name, 'email' => $teacherAccount->email, 'phone' => $teacherAccount->phone, 'user_id' => $teacherAccount->id]);

        $this->actingAs($this->admin)->post(route('academic.teaching-setup.store'), [
            'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id, 'section_id' => $this->section->id,
            'teacher_ids' => $curriculum->courses->mapWithKeys(fn ($course) => [$course->id => $teacher->id])->all(),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('course_offerings', 2);

        $student = Student::create(['name' => 'Automatic Student', 'email' => 'automatic.student@example.test', 'phone' => '03007000004', 'department_id' => $this->department->id, 'section_id' => $this->section->id]);
        $this->post(route('addEnrollment'), ['student_id' => $student->id, 'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id])
            ->assertSessionHasNoErrors();

        $enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->assertCount(2, $enrollment->courses);
        $this->assertSame(['AUTO101', 'AUTO102'], $enrollment->courses()->orderBy('course_code')->pluck('course_code')->all());
        $this->assertDatabaseCount('course_offerings', 2);
    }

    private function term(): AcademicTerm
    {
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        return AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-31', 'status' => 'active']);
    }

    private function course(string $code, string $name): Course
    {
        return Course::create(['code' => $code, 'name' => $name, 'description' => 'Program course', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
    }

    private function user(string $name, string $email, string $phone): User
    {
        return User::create(['name' => $name, 'email' => $email, 'phone' => $phone, 'password' => bcrypt('password'), 'image' => 'default-user.png']);
    }
}
