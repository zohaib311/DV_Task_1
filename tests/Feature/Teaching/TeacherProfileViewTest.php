<?php

namespace Tests\Feature\Teaching;

use App\Models\Academic\CourseOffering;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicOfferings;
use Tests\TestCase;

class TeacherProfileViewTest extends TestCase
{
    use CreatesAcademicOfferings, RefreshDatabase;

    private User $admin;
    private Teacher $teacher;
    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '03001112233',
            'password' => bcrypt('password'),
        ]);
        $this->admin->givePermissionTo('teachers.manage');

        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'CS-A', 'department_id' => $department->id]);
        $student = Student::create([
            'name' => 'Enrolled Student',
            'email' => 'student@test.com',
            'phone' => '03009988776',
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);

        $course = Course::create([
            'code' => 'CS-201',
            'name' => 'Data Structures & Algorithms',
            'description' => 'Core computing',
            'credit_hours' => 4,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);

        $plan = $this->offeringPlan($student, [$course]);
        $this->offering = CourseOffering::findOrFail($plan['offering_ids'][0]);
        $this->teacher = $this->offering->teachers()->firstOrFail();
        $this->teacher->update([
            'course' => 'Computer Science & Software Systems',
        ]);
    }

    /**
     * Test teachers list table displays View action button and modal markup.
     */
    public function test_teacher_list_contains_view_action_button_and_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('allTeachers'));

        $response->assertOk();
        $response->assertSee('action__info');
        $response->assertSee('bi-eye');
        $response->assertSee('infoTeacherModal' . $this->teacher->id);
        $response->assertSee('Data Structures &amp; Algorithms', false);
        $response->assertSee($this->teacher->name);
        $response->assertSee($this->teacher->email);
        $response->assertSee($this->teacher->phone);
        $response->assertSee('Joined University:');
    }

    /**
     * Test standalone teacher view route renders full profile and assigned subjects.
     */
    public function test_standalone_teacher_view_route_renders_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('viewTeacher', $this->teacher->id));

        $response->assertOk();
        $response->assertSee($this->teacher->name);
        $response->assertSee($this->teacher->email);
        $response->assertSee('CS-201');
        $response->assertSee('Data Structures &amp; Algorithms', false);
        $response->assertSee('Faculty Member Identity &amp; Appointment', false);
        $response->assertSee('Assigned Subjects &amp; Teaching Portfolio', false);
        $response->assertSee('Joining / Appointment Date');
    }

    /**
     * Test unauthorized user without teachers.manage cannot access teacher view route.
     */
    public function test_unauthorized_user_cannot_access_teacher_view(): void
    {
        $studentUser = User::create([
            'name' => 'Regular Student',
            'email' => 'student.user@test.com',
            'phone' => '03005556677',
            'password' => bcrypt('password'),
        ]);
        $studentUser->assignRole('Student');

        $response = $this->actingAs($studentUser)->get(route('viewTeacher', $this->teacher->id));

        $response->assertForbidden();
    }
}
