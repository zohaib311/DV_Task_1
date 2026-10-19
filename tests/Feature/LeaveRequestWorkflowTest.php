<?php

namespace Tests\Feature;

use App\Models\Academic\CourseOffering;
use App\Models\Leave\StudentLeaveRequest;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeaveRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $teacher;
    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-01 12:00:00'));
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);

        $this->teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->teacher->givePermissionTo('leaves.review-assigned');
        $this->offering = CourseOffering::where('course_code', 'DEMO-CS101')->firstOrFail();
        $course = $this->offering->enrollmentCourses()->firstOrFail();
        $this->student = User::create(['name' => 'Leave Student', 'email' => 'leave.student@example.test', 'phone' => '03000000001', 'password' => bcrypt('password')]);
        $this->student->assignRole('Student');
        $course->enrollment->student->update(['user_id' => $this->student->id]);
    }

    private function payload(): array
    {
        return [
            'student_enrollment_course_id' => $this->offering->enrollmentCourses()->firstOrFail()->id,
            'starts_on' => '2026-10-02', 'ends_on' => '2026-10-03',
            'leave_type' => 'medical', 'reason' => 'Medical rest recommended by my doctor.',
        ];
    }

    public function test_student_can_request_a_course_specific_leave_and_assigned_teacher_is_notified(): void
    {
        $this->actingAs($this->student);
        $this->get(route('student.leaves.index'))->assertOk()->assertSee('DEMO-CS101');
        $this->post(route('student.leaves.store'), $this->payload())->assertRedirect(route('student.leaves.index'));

        $leave = StudentLeaveRequest::firstOrFail();
        $this->assertSame('pending', $leave->status);
        $this->assertSame($this->student->id, $leave->requested_by);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->teacher->id]);

        $this->actingAs($this->teacher)->get(route('teaching.leaves.show', $leave))
            ->assertOk()->assertSee('DEMO-CS101')->assertSee('Medical rest recommended');
    }

    public function test_only_assigned_teacher_can_approve_and_student_receives_the_decision(): void
    {
        $this->actingAs($this->student)->post(route('student.leaves.store'), $this->payload())->assertSessionHasNoErrors();
        $leave = StudentLeaveRequest::firstOrFail();
        $this->actingAs($this->teacher)->put(route('teaching.leaves.review', $leave), ['decision' => 'approved', 'teacher_remark' => 'Approved. Please take care.'])
            ->assertRedirect(route('teaching.leaves.show', $leave));

        $this->assertDatabaseHas('student_leave_requests', ['id' => $leave->id, 'status' => 'approved', 'reviewed_by' => $this->teacher->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->student->id]);

        $other = User::where('email', 'demo.teacher2@example.test')->firstOrFail();
        $other->givePermissionTo('leaves.review-assigned');
        $this->actingAs($other)->get(route('teaching.leaves.show', $leave))->assertNotFound();
    }

    public function test_overlapping_requests_and_reviews_without_a_rejection_reason_are_rejected(): void
    {
        $this->actingAs($this->student)->post(route('student.leaves.store'), $this->payload())->assertSessionHasNoErrors();
        $this->postJson(route('student.leaves.store'), $this->payload())->assertUnprocessable();
        $leave = StudentLeaveRequest::firstOrFail();
        $this->actingAs($this->teacher)->putJson(route('teaching.leaves.review', $leave), ['decision' => 'rejected', 'teacher_remark' => ''])->assertUnprocessable();
        $this->assertSame('pending', $leave->fresh()->status);
    }
}
