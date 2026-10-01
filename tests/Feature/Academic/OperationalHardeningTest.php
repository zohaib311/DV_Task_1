<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\CourseOffering;
use App\Models\Attendance\AttendanceAudit;
use App\Models\Attendance\AttendanceSession;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private StudentSemesterEnrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);
        $this->admin = User::create(['name' => 'Report administrator', 'email' => 'reports@example.test', 'phone' => '03009999771', 'password' => bcrypt('password')]);
        $this->admin->assignRole('Academic Admin');
        $this->enrollment = StudentSemesterEnrollment::firstOrFail();
    }

    public function test_reports_are_read_only_and_use_saved_operational_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.reports.index'));
        $response->assertOk();
        $response->assertSee('Teacher workload');
        $response->assertSee('Promotion eligible students');
        $response->assertSee('DEMO-CS101');
        $this->get(route('academic.reports.index', ['term' => $this->enrollment->academic_term_id, 'minimum_attendance' => 80]))->assertOk();
        $this->assertSame('active', CourseOffering::first()->status);
    }

    public function test_audit_viewer_is_permission_protected_and_read_only(): void
    {
        $teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->actingAs($teacher)->get(route('academic.reports.index'))->assertForbidden();
        $offering = CourseOffering::firstOrFail();
        $session = AttendanceSession::create(['course_offering_id' => $offering->id, 'teacher_id' => $offering->teachers()->first()->id, 'held_on' => '2026-10-01', 'type' => 'lecture', 'slot' => 19, 'status' => 'completed']);
        AttendanceAudit::create(['attendance_session_id' => $session->id, 'user_id' => $this->admin->id, 'action' => 'attendance_saved', 'reason' => 'Verified correction', 'after' => ['status' => 'completed']]);
        $response = $this->actingAs($this->admin)->get(route('academic.audits.index'));
        $response->assertOk();
        $response->assertSee('Attendance saved');
        $response->assertSee('Verified correction');
        $response->assertSee('Before / after');
        $this->get(route('academic.audits.index', ['type' => 'attendance', 'search' => 'verified']))->assertOk();
        $this->actingAs($teacher)->get(route('academic.audits.index'))->assertForbidden();
    }

    public function test_student_notifications_are_private_and_can_be_marked_read(): void
    {
        $studentUser = User::create(['name' => 'Notice student', 'email' => 'notice.student@example.test', 'phone' => '03009999772', 'password' => bcrypt('password')]);
        $studentUser->assignRole('Student');
        $this->enrollment->student->update(['user_id' => $studentUser->id]);
        $studentUser->notify(new AcademicUpdateNotification('Semester result published', 'Your Semester 1 result is ready.', route('student.results.index')));
        $foreign = User::create(['name' => 'Other student', 'email' => 'notice.other@example.test', 'phone' => '03009999773', 'password' => bcrypt('password')]);
        $foreign->assignRole('Student');
        StudentSemesterEnrollment::whereKeyNot($this->enrollment->id)->firstOrFail()->student->update(['user_id' => $foreign->id]);
        $foreign->notify(new AcademicUpdateNotification('Private', 'Foreign notification.'));
        $response = $this->actingAs($studentUser)->get(route('student.notifications.index'));
        $response->assertOk();
        $response->assertSee('Semester result published');
        $response->assertDontSee('Foreign notification');
        $notification = $studentUser->notifications()->firstOrFail();
        $this->post(route('student.notifications.read', $notification))->assertRedirect(route('student.results.index'));
        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($foreign)->post(route('student.notifications.read', $notification))->assertNotFound();
    }

    public function test_sensitive_publication_requests_are_rate_limited(): void
    {
        $this->actingAs($this->admin);
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertUnprocessable();
        }
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertStatus(429);
    }
}
