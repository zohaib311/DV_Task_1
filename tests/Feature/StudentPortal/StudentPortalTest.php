<?php

namespace Tests\Feature\StudentPortal;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Academic\CourseOffering;
use App\Models\Course\Course;
use App\Models\Result\SemesterResult;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private StudentSemesterEnrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);
        $this->user = User::create(['name' => 'Portal student', 'email' => 'portal@example.test', 'phone' => '03009999981', 'password' => bcrypt('PortalPassword!')]);
        $this->user->assignRole('Student');
        $this->enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->enrollment->student->update(['user_id' => $this->user->id]);
        $this->actingAs($this->user);
    }

    private function createResult(bool $published = true, ?StudentSemesterEnrollment $enrollment = null, bool $flexible = true): SemesterResult
    {
        $enrollment ??= $this->enrollment;
        $result = SemesterResult::create(['student_id' => $enrollment->student_id, 'student_semester_enrollment_id' => $enrollment->id,
            'status' => $published ? 'Pass' : 'Draft', 'published_at' => $published ? now() : null, 'sgpa' => 4, 'cgpa' => 4, 'semester_percentage' => 90]);
        $course = $enrollment->courses->first();
        $snapshot = ['scheme' => [['code' => 'assignment', 'allocation' => 100]], 'reviewed_by' => 'PRIVATE_REVIEWER', 'student' => [
            'components' => ['assignment' => 90], 'attendance' => null, 'assessments' => [['title' => 'Released Assignment One', 'component' => 'assignment', 'held_on' => '2026-10-01', 'maximum' => 20, 'weight' => 100, 'obtained' => 18]],
        ]];
        $result->items()->create(array_merge($course->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks']), [
            'student_enrollment_course_id' => $course->id, 'obtained_marks' => 90, 'percentage' => 90, 'grade' => 'A', 'grade_point' => 4, 'status' => 'Pass',
            'assessment_snapshot' => $flexible ? $snapshot : null,
        ]));
        $result->audits()->create(['action' => 'semester_approved', 'reason' => 'PRIVATE_AUDIT_REASON']);

        return $result;
    }

    public function test_dashboard_courses_and_existing_attendance_are_owned_and_read_only(): void
    {
        $this->get(route('student.dashboard'))->assertOk()->assertSee($this->enrollment->student->registration_no)->assertSee('Semester 1');
        $this->get(route('student.courses'))->assertOk()->assertViewHas('courses', fn ($courses) => $courses->total() === 3)->assertSee('Ayesha');
        $this->get(route('student.attendance.index'))->assertOk();
        $this->get(route('student.assessments.index'))->assertOk()->assertViewHas('courses', fn ($courses) => $courses->total() === 3);
        $this->get(route('student.results.index'))->assertOk()->assertSee('No published semester results');
        $this->get(route('dashboardView'))->assertRedirect(route('student.dashboard'));
        $this->get('/')->assertRedirect(route('student.dashboard'));
        $this->post(route('student.dashboard'), ['student_id' => 99])->assertMethodNotAllowed();
    }

    public function test_drafts_are_hidden_from_all_student_surfaces_including_direct_urls(): void
    {
        $result = $this->createResult(false);
        $result->update(['reviewed_at' => now()]);
        $course = $result->items->first()->student_enrollment_course_id;
        $this->get(route('student.dashboard'))->assertOk()->assertSee('No published results yet')->assertDontSee('PRIVATE_AUDIT_REASON');
        $this->get(route('student.results.index'))->assertOk()->assertViewHas('results', fn ($results) => $results->total() === 0);
        $this->get(route('student.results.show', $result))->assertNotFound();
        $this->get(route('student.assessments.show', $course))->assertOk()->assertSee('Marks have not been released')->assertDontSee('Released Assignment One');
        $result->update(['status' => 'Pass']);
        $this->get(route('student.results.show', $result))->assertNotFound();
        $result->update(['status' => 'Draft', 'published_at' => now()]);
        $this->get(route('student.results.show', $result))->assertNotFound();
    }

    public function test_published_result_and_raw_marks_show_without_private_moderation_metadata(): void
    {
        $result = $this->createResult();
        $this->get(route('student.results.index'))->assertOk()->assertViewHas('results', fn ($results) => $results->total() === 1);
        $this->get(route('student.results.show', $result))->assertOk()->assertSee('Assignment: 90')->assertDontSee('PRIVATE_REVIEWER')->assertDontSee('PRIVATE_AUDIT_REASON');
        $this->get(route('student.assessments.show', $result->items->first()->student_enrollment_course_id))->assertOk()->assertSee('Released Assignment One')->assertSee('18 / 20')->assertDontSee('PRIVATE_REVIEWER');
        $this->get(route('student.dashboard'))->assertOk()->assertSee('Open result sheet');
    }

    public function test_other_students_records_are_not_accessible_even_with_super_admin_gate_bypass(): void
    {
        $foreign = StudentSemesterEnrollment::whereKeyNot($this->enrollment->id)->first();
        $result = $this->createResult(true, $foreign);
        $course = $foreign->courses->first();
        foreach ([false, true] as $super) {
            if ($super) {
                $this->user->assignRole('Super Admin');
            }
            $this->get(route('student.results.show', $result))->assertNotFound();
            $this->get(route('student.assessments.show', $course))->assertNotFound();
            $this->get(route('student.attendance.show', $course))->assertNotFound();
            $this->get(route('student.courses', ['enrollment' => $foreign->id]))->assertNotFound();
        }
    }

    public function test_unlinked_accounts_and_missing_permissions_are_rejected(): void
    {
        $this->enrollment->student->update(['user_id' => null]);
        $this->user->unsetRelation('studentProfile');
        foreach (['student.dashboard', 'student.courses', 'student.assessments.index', 'student.results.index', 'student.attendance.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->actingAs($teacher)->get(route('student.dashboard'))->assertForbidden();
    }

    public function test_granular_permissions_control_routes_and_navigation(): void
    {
        $this->user->syncRoles([]);
        $this->user->givePermissionTo('student.profile.view-own');
        $this->get(route('student.dashboard'))->assertOk()->assertDontSee('My Courses')->assertDontSee('Released Marks')->assertDontSee('Latest published semester');
        foreach (['student.courses', 'student.assessments.index', 'student.results.index', 'student.attendance.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_student_cannot_access_admin_teacher_or_mutation_endpoints(): void
    {
        $this->get(route('results.moderation.index'))->assertForbidden();
        $this->get(route('teaching.assessments.index'))->assertForbidden();
        $this->get(route('allStudents'))->assertForbidden();
        $this->post(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertForbidden();
    }

    public function test_legacy_published_results_and_empty_enrollment_profiles_render(): void
    {
        $result = $this->createResult(true, null, false);
        $this->get(route('student.results.show', $result))->assertOk()->assertSee('Historical total-only result');
        $this->get(route('student.assessments.show', $result->items->first()->student_enrollment_course_id))->assertOk()->assertSee('not available for this historical result');
        $this->enrollment->update(['status' => 'promoted']);
        $this->get(route('student.dashboard'))->assertOk()->assertSee('Not enrolled');
    }

    public function test_login_lands_on_student_portal_and_guests_cannot_enter(): void
    {
        auth()->logout();
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        session()->forget('url.intended');
        $this->post(route('login.submit'), ['email' => $this->user->email, 'password' => 'PortalPassword!'])->assertRedirect(route('student.dashboard'));
    }

    public function test_publication_release_and_saved_corrections_are_reflected_without_exposing_audits(): void
    {
        $result = $this->createResult(false);
        $item = $result->items->first();
        $url = route('student.assessments.show', $item->student_enrollment_course_id);
        $this->get($url)->assertOk()->assertDontSee('Released Assignment One');
        $result->update(['published_at' => now(), 'status' => 'Pass']);
        $this->get($url)->assertOk()->assertSee('18 / 20');
        $snapshot = $item->assessment_snapshot;
        $snapshot['student']['assessments'][0]['obtained'] = 19;
        $snapshot['student']['components']['assignment'] = 95;
        $item->update(['assessment_snapshot' => $snapshot, 'obtained_marks' => 95]);
        $this->get($url)->assertOk()->assertSee('19 / 20')->assertSee('95 / 100')->assertDontSee('PRIVATE_AUDIT_REASON');
    }

    public function test_student_can_register_an_available_elective_within_the_credit_limit(): void
    {
        $source = $this->enrollment->courses()->firstOrFail()->offering;
        $course = Course::create(['code' => 'DEMO-EL101', 'name' => 'Portal Elective', 'description' => 'Elective', 'credit_hours' => 3,
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $entry = $this->enrollment->curriculum->courses()->create(['course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'type' => 'elective']);
        $offering = CourseOffering::create($entry->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks']) + [
            'academic_term_id' => $this->enrollment->academic_term_id, 'curriculum_course_id' => $entry->id,
            'department_id' => $this->enrollment->department_id, 'program_id' => $this->enrollment->program_id,
            'section_id' => $this->enrollment->section_id, 'semester' => $this->enrollment->semester, 'status' => 'active']);
        $offering->teachers()->attach($source->teachers()->pluck('teachers.id'));

        $this->get(route('student.registration.index'))->assertOk()->assertSee('Portal Elective');
        $this->post(route('student.registration.store'), ['offering_ids' => [$offering->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_enrollment_courses', ['student_semester_enrollment_id' => $this->enrollment->id,
            'course_offering_id' => $offering->id, 'registration_type' => 'elective']);
    }
}
