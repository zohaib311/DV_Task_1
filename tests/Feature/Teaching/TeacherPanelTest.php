<?php

namespace Tests\Feature\Teaching;

use App\Models\Academic\CourseOffering;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicEnrollmentService;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesAcademicOfferings;
use Tests\TestCase;

class TeacherPanelTest extends TestCase
{
    use CreatesAcademicOfferings, RefreshDatabase;

    private User $teacher;

    private CourseOffering $assigned;

    private CourseOffering $other;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
        $department = Department::create(['name' => 'Computing']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $this->student = Student::create(['name' => 'Visible Student', 'email' => 'private-student@example.com', 'phone' => '03001234567', 'department_id' => $department->id, 'section_id' => $section->id]);
        $course = Course::create(['code' => 'CS101', 'name' => 'Assigned Programming', 'description' => 'Core', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $plan = $this->offeringPlan($this->student, [$course]);
        $this->assigned = CourseOffering::findOrFail($plan['offering_ids'][0]);
        $this->teacher = $this->assigned->teachers()->firstOrFail()->user;
        $this->teacher->assignRole('Teacher');
        app(AcademicEnrollmentService::class)->enroll($plan + ['student_id' => $this->student->id]);
        $otherCourse = $course->replicate();
        $otherCourse->code = 'PRIVATE202';
        $otherCourse->name = 'Unassigned Secret Course';
        $otherCourse->save();
        $otherPlan = $this->offeringPlan($this->student, [$otherCourse]);
        $this->other = CourseOffering::findOrFail($otherPlan['offering_ids'][0]);
        $this->actingAs($this->teacher);
    }

    public function test_dashboard_and_list_show_only_assigned_offerings_and_scoped_counts(): void
    {
        $this->get(route('dashboardView'))->assertRedirect(route('teaching.dashboard'));
        $this->get(route('teaching.dashboard'))->assertOk()->assertSee('Assigned Programming')->assertDontSee('Unassigned Secret Course')
            ->assertViewHas('offeringCount', 1)->assertViewHas('activeCount', 1)->assertViewHas('studentCount', 1)
            ->assertSee('Teacher Panel')->assertDontSee('Academic Setup');
        $this->get(route('teaching.offerings.index'))->assertOk()->assertSee('CS101')->assertDontSee('PRIVATE202');
    }

    public function test_teacher_login_lands_in_workspace_even_without_general_dashboard_permission(): void
    {
        Role::findByName('Teacher')->revokePermissionTo('dashboard.view');
        auth()->logout();
        $this->post(route('login.submit'), ['email' => $this->teacher->email, 'password' => 'password'])
            ->assertRedirect(route('teaching.dashboard'));
        $this->get(route('teaching.dashboard'))->assertOk();
    }

    public function test_distinct_student_count_does_not_double_count_multiple_course_registrations(): void
    {
        $this->other->teachers()->attach($this->assigned->teachers()->first());
        $enrollment = $this->assigned->enrollmentCourses()->first()->enrollment;
        $enrollment->courses()->create($this->other->only(['course_id', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks']) + ['course_offering_id' => $this->other->id]);
        $this->get(route('teaching.dashboard'))->assertOk()->assertViewHas('offeringCount', 2)->assertViewHas('studentCount', 1);
        $this->get(route('teaching.offerings.show', $this->other))->assertOk()->assertViewHas('rosterCount', 1);
    }

    public function test_roster_is_read_only_and_limited_to_this_offering_without_private_contact_details(): void
    {
        $this->get(route('teaching.offerings.show', $this->assigned))->assertOk()->assertSee('Visible Student')
            ->assertDontSee('private-student@example.com')->assertDontSee('03001234567')->assertSee('Read-only roster')->assertViewHas('rosterCount', 1);
        $this->get(route('teaching.offerings.show', $this->other))->assertNotFound();
        $this->get(route('teaching.offerings.show', 999999))->assertNotFound();
        $this->post(route('teaching.offerings.show', $this->assigned))->assertStatus(405);
    }

    public function test_search_and_term_filters_cannot_escape_assignment_scope(): void
    {
        $this->get(route('teaching.offerings.index', ['q' => 'PRIVATE202']))->assertOk()->assertDontSee('Unassigned Secret Course')->assertViewHas('offerings', fn ($rows) => $rows->total() === 0);
        $this->get(route('teaching.offerings.index', ['q' => 'CS101', 'term_id' => $this->assigned->academic_term_id, 'status' => 'active']))->assertOk()->assertViewHas('offerings', fn ($rows) => $rows->total() === 1);
        $this->get(route('teaching.offerings.index', ['term_id' => 999999]))->assertOk()->assertViewHas('offerings', fn ($rows) => $rows->total() === 0);
        $this->get(route('teaching.offerings.show', [$this->assigned, 'q' => 'not-a-student']))->assertOk()->assertSee('No students match your search.')->assertDontSee('Visible Student');
        $this->getJson(route('teaching.offerings.index', ['status' => 'invalid']))->assertUnprocessable();
        $this->getJson(route('teaching.offerings.index', ['q' => ['bad']]))->assertUnprocessable();
    }

    public function test_default_students_and_academic_admins_cannot_enter_teacher_workspace(): void
    {
        foreach (['Student', 'Academic Admin'] as $role) {
            $this->teacher->syncRoles([$role]);
            $this->teacher->unsetRelation('roles');
            foreach (['teaching.dashboard', 'teaching.offerings.index'] as $route) {
                $this->get(route($route))->assertForbidden();
            }
            $this->get(route('teaching.offerings.show', $this->assigned))->assertForbidden();
        }
    }

    public function test_guest_requires_login_and_unlinked_teacher_is_rejected(): void
    {
        auth()->logout();
        $this->get(route('teaching.dashboard'))->assertRedirect(route('login'));
        $this->get(route('teaching.offerings.show', $this->assigned))->assertRedirect(route('login'));
        $this->assigned->teachers()->first()->update(['user_id' => null]);
        $this->actingAs($this->teacher->fresh())->get(route('teaching.dashboard'))->assertForbidden();
    }

    public function test_removing_permission_or_assignment_revokes_access(): void
    {
        Role::findByName('Teacher')->revokePermissionTo('offerings.view-assigned');
        $this->actingAs($this->teacher->fresh())->get(route('teaching.dashboard'))->assertForbidden();
        Role::findByName('Teacher')->givePermissionTo('offerings.view-assigned');
        $this->assigned->teachers()->detach();
        $this->actingAs($this->teacher->fresh())->get(route('teaching.offerings.show', $this->assigned))->assertNotFound();
        $this->get(route('teaching.dashboard'))->assertOk()->assertSee('No assigned courses to display')->assertViewHas('studentCount', 0);
    }

    public function test_co_teachers_share_the_roster_and_history_remains_visible(): void
    {
        $coTeacher = $this->other->teachers()->first();
        $coTeacher->user->assignRole('Teacher');
        $this->assigned->teachers()->attach($coTeacher);
        $this->assigned->update(['status' => 'completed']);
        $this->assigned->enrollmentCourses()->first()->enrollment->update(['status' => 'promoted']);
        $this->actingAs($coTeacher->user)->get(route('teaching.offerings.show', $this->assigned))->assertOk()->assertSee('Visible Student')->assertSee('Promoted')->assertSee('Completed');
    }

    public function test_custom_permission_roles_work_but_super_admin_still_cannot_escape_assignment_scope(): void
    {
        $role = Role::create(['name' => 'Visiting Lecturer', 'guard_name' => 'web']);
        $role->givePermissionTo('offerings.view-assigned');
        $this->teacher->syncRoles([$role]);
        $this->actingAs($this->teacher->fresh())->get(route('teaching.dashboard'))->assertOk();
        $this->teacher->syncRoles(['Super Admin']);
        $this->actingAs($this->teacher->fresh())->get(route('teaching.offerings.show', $this->other))->assertNotFound();
    }

    public function test_teacher_cannot_open_admin_endpoints_or_publish_results(): void
    {
        foreach (['academic.offerings.index', 'academic.curricula.index', 'allStudents', 'allEnrollments', 'allResults'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->postJson(route('result.semester.store'), [])->assertForbidden();
    }

    public function test_roster_pagination_and_registration_search_preserve_scoping(): void
    {
        for ($index = 0; $index < 26; $index++) {
            $student = Student::create(['name' => 'Roster Person '.$index, 'email' => "roster{$index}@example.com", 'phone' => '0311'.str_pad((string) $index, 7, '0', STR_PAD_LEFT), 'department_id' => $this->student->department_id, 'section_id' => $this->student->section_id]);
            $enrollment = $student->semesterEnrollments()->create(['department_id' => $student->department_id, 'section_id' => $student->section_id, 'semester' => 'Semester 2', 'academic_year' => '2026-2027', 'status' => 'active', 'enrolled_at' => today()]);
            $original = $this->assigned->enrollmentCourses()->first();
            $enrollment->courses()->create($original->only(['course_id', 'course_offering_id', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks']));
        }
        $this->get(route('teaching.offerings.show', [$this->assigned, 'page' => 2]))->assertOk()->assertViewHas('roster', fn ($rows) => $rows->total() === 27 && $rows->count() === 2);
        $this->get(route('teaching.offerings.show', [$this->assigned, 'q' => $this->student->registration_no]))->assertOk()->assertViewHas('roster', fn ($rows) => $rows->total() === 1);
    }
}
