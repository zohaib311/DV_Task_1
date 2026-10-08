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
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Queue;
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

    public function test_live_notification_feed_is_private_and_supports_safe_actions(): void
    {
        $foreign = User::create(['name' => 'Foreign staff', 'email' => 'foreign.staff@example.test', 'phone' => '03009999774', 'password' => bcrypt('password')]);
        $foreign->assignRole('Academic Admin');
        $this->admin->notify(new AcademicUpdateNotification('Assigned course', 'CS101 was assigned to you.', route('teaching.offerings.index'), 'offering'));
        $foreign->notify(new AcademicUpdateNotification('Private update', 'This belongs to another user.'));

        $response = $this->actingAs($this->admin)->getJson(route('account.notifications.feed'));
        $response->assertOk()->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.title', 'Assigned course')
            ->assertJsonMissing(['title' => 'Private update']);
        $notification = $this->admin->notifications()->firstOrFail();
        $this->actingAs($foreign)->postJson(route('account.notifications.read', $notification))->assertNotFound();

        $this->actingAs($this->admin)->postJson(route('account.notifications.read-all'))->assertOk()->assertJsonPath('unread_count', 0);
        $this->assertSame(0, $this->admin->unreadNotifications()->count());

        $this->admin->notify(new AcademicUpdateNotification('Unsafe link', 'Do not leave the application.', 'https://example.org/untrusted'));
        $unsafe = $this->admin->notifications()->get()->first(fn ($item) => ($item->data['title'] ?? null) === 'Unsafe link');
        $this->assertNotNull($unsafe);
        $this->postJson(route('account.notifications.read', $unsafe))->assertOk()
            ->assertJsonPath('redirect_url', route('account.notifications.index'));
    }

    public function test_academic_notifications_are_broadcast_on_the_users_private_channel(): void
    {
        config(['broadcasting.default' => 'reverb']);
        app(BroadcastManager::class)->forgetDrivers();
        require base_path('routes/channels.php');
        Queue::fake();

        $this->admin->notify(new AcademicUpdateNotification(
            'Realtime update',
            'This notification should use database history and broadcasting.'
        ));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->admin->id,
            'notifiable_type' => User::class,
        ]);
        Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job) {
            $payload = $job->event->broadcastWith();

            return $payload['title'] === 'Realtime update'
                && $payload['type'] === 'academic.update'
                && str_starts_with($payload['read_url'], '/notifications/');
        });

        $this->actingAs($this->admin)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-users.'.$this->admin->id.'.notifications',
        ])->assertOk();
    }

    public function test_users_cannot_subscribe_to_another_users_notification_channel(): void
    {
        config(['broadcasting.default' => 'reverb']);
        app(BroadcastManager::class)->forgetDrivers();
        require base_path('routes/channels.php');
        $other = User::create([
            'name' => 'Other realtime user',
            'email' => 'other.realtime@example.test',
            'phone' => '03009999799',
            'password' => bcrypt('password'),
        ]);
        $other->assignRole('Academic Admin');

        $this->actingAs($other)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-users.'.$this->admin->id.'.notifications',
        ])->assertForbidden();
    }

    public function test_sensitive_publication_requests_are_rate_limited(): void
    {
        $this->actingAs($this->admin);
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertUnprocessable();
        }
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertStatus(429);
    }

    public function test_dashboard_uses_live_counts_and_only_authorized_actions(): void
    {
        $this->actingAs($this->admin)->get(route('dashboardView'))->assertOk()
            ->assertViewHas('statistics', fn ($rows) => $rows->firstWhere('label', 'Students')['count'] === \App\Models\Student::count());
        $limited = User::create(['name' => 'Limited staff', 'email' => 'limited@example.test', 'phone' => '03009999780', 'password' => bcrypt('password')]);
        $limited->givePermissionTo(['dashboard.view', 'courses.manage']);
        $limited->assignRole(\Spatie\Permission\Models\Role::findOrCreate('Limited staff', 'web'));
        $this->actingAs($limited)->get(route('dashboardView'))->assertOk()
            ->assertSee('Add Course')->assertDontSee('Add Student')->assertDontSee('Add Result')
            ->assertViewHas('statistics', fn ($rows) => $rows->pluck('label')->all() === ['Courses']);
    }

    public function test_authenticated_login_redirect_and_unlinked_admin_navigation(): void
    {
        $this->admin->assignRole('Super Admin');
        $this->actingAs($this->admin)->get('/login')->assertRedirect('/');
        $this->get(route('dashboardView'))->assertOk()->assertDontSee('Teacher Panel')->assertDontSee('Student Portal');
        $this->assertFileExists(public_path('css/academic/print.css'));
    }

    public function test_reports_preserve_zero_averages_and_scope_promotions_to_selected_term(): void
    {
        \App\Models\Result\SemesterResult::create([
            'student_semester_enrollment_id' => $this->enrollment->id, 'student_id' => $this->enrollment->student_id,
            'status' => 'Fail', 'published_at' => now(), 'sgpa' => 0, 'cgpa' => 0, 'semester_percentage' => 0,
        ]);
        $this->actingAs($this->admin)->get(route('academic.reports.index'))->assertOk()
            ->assertViewHas('resultSummary', fn ($summary) => $summary['average_sgpa'] === 0.0 && $summary['average_percentage'] === 0.0);
        config(['academic.promotion.require_published_pass_result' => false]);
        $term = \App\Models\Academic\AcademicTerm::findOrFail($this->enrollment->academic_term_id)->replicate();
        $term->name = 'Empty report term';
        $term->save();
        $this->get(route('academic.reports.index', ['term' => $term->id]))->assertOk()
            ->assertViewHas('promotion', fn ($rows) => $rows->isEmpty());
        $limited = User::create(['name' => 'Limited staff', 'email' => 'limited@example.test', 'phone' => '03009999780', 'password' => bcrypt('password')]);
        $limited->givePermissionTo('reports.view');
        $limited->assignRole(\Spatie\Permission\Models\Role::findOrCreate('Limited staff', 'web'));
        $this->actingAs($limited)->get(route('academic.reports.index'))->assertOk()->assertDontSee('Open promotion');
    }

    public function test_audit_search_is_paginated_and_retains_filters(): void
    {
        $offering = CourseOffering::firstOrFail();
        for ($i = 0; $i < 30; $i++) {
            \App\Models\Assessment\AssessmentAudit::create(['course_offering_id' => $offering->id,
                'user_id' => $this->admin->id, 'action' => 'scheme_updated', 'reason' => 'Pagination evidence '.$i, 'after' => []]);
        }
        $this->actingAs($this->admin)->get(route('academic.audits.index', ['type' => 'assessment', 'search' => 'pagination']))
            ->assertOk()->assertViewHas('audits', fn ($rows) => $rows->total() === 30 && $rows->count() === 25)
            ->assertSee('page=2', false);
        $this->get(route('academic.audits.index', ['type' => 'assessment', 'search' => 'pagination', 'page' => 2]))
            ->assertOk()->assertViewHas('audits', fn ($rows) => $rows->count() === 5);
    }

    public function test_notification_only_access_has_navigation_and_safe_redirects(): void
    {
        $this->admin->syncRoles([]);
        $this->admin->givePermissionTo('notifications.view-own');
        $this->admin->assignRole(\Spatie\Permission\Models\Role::findOrCreate('Notifications only', 'web'));
        $this->enrollment->student->update(['user_id' => $this->admin->id]);
        $user = $this->admin->fresh();
        $this->assertSame('student.notifications.index', app(\App\Services\StudentPortal\StudentWorkspace::class)->landing($user));
        $this->actingAs($user)->get(route('student.notifications.index'))->assertOk()->assertSee('Student Portal');
        foreach ([route('student.results.index'), 'https://example.org/untrusted'] as $url) {
            $user->notify(new AcademicUpdateNotification('Academic update', 'Review your update.', $url));
            $notification = $user->notifications()->latest()->firstOrFail();
            $this->post(route('student.notifications.read', $notification))->assertRedirect(route('student.notifications.index'));
            $this->assertNotNull($notification->fresh()->read_at);
        }
    }

    public function test_notifications_open_owned_published_result_and_enrollment_links(): void
    {
        $user = User::create(['name' => 'Linked student', 'email' => 'linked@example.test', 'phone' => '03009999781', 'password' => bcrypt('password')]);
        $user->assignRole('Student');
        $this->enrollment->student->update(['user_id' => $user->id]);
        $result = \App\Models\Result\SemesterResult::create([
            'student_semester_enrollment_id' => $this->enrollment->id, 'student_id' => $this->enrollment->student_id,
            'status' => 'Pass', 'published_at' => now(), 'sgpa' => 4, 'cgpa' => 4, 'semester_percentage' => 90,
        ]);
        $this->actingAs($user);
        foreach ([route('student.results.show', $result), route('student.courses', ['enrollment' => $this->enrollment->id])] as $url) {
            $notice = new AcademicUpdateNotification('Update', 'Saved academic update', $url);
            $notice->id = (string) \Illuminate\Support\Str::uuid();
            $user->notify($notice);
            $this->post(route('student.notifications.read', $notice->id))->assertRedirect($url);
        }
        $result->update(['status' => 'Draft', 'published_at' => null]);
        $notice = new AcademicUpdateNotification('Update', 'Unavailable result', route('student.results.show', $result));
        $notice->id = (string) \Illuminate\Support\Str::uuid();
        $user->notify($notice);
        $this->post(route('student.notifications.read', $notice->id))->assertRedirect(route('student.notifications.index'));
    }
}
