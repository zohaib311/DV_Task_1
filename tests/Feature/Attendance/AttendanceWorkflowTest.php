<?php

namespace Tests\Feature\Attendance;

use App\Models\Academic\CourseOffering;
use App\Models\Attendance\AttendanceSession;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Result\SemesterResult;
use App\Models\User;
use App\Services\Attendance\AttendanceCalculator;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-01 12:00:00'));
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);
        $this->teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->teacher->givePermissionTo('attendance.manage-assigned');
        $this->offering = CourseOffering::where('course_code', 'DEMO-CS101')->firstOrFail();
        $this->actingAs($this->teacher);
    }

    private function createSession(int $slot = 1): AttendanceSession
    {
        $this->post(route('teaching.attendance.store', $this->offering), ['held_on' => '2026-10-01', 'type' => 'lecture', 'slot' => $slot])->assertSessionHasNoErrors();

        return $this->offering->attendanceSessions()->where('slot', $slot)->firstOrFail();
    }

    private function payload(AttendanceSession $session, string $status = 'present'): array
    {
        return ['revision' => $session->fresh()->revision, 'reason' => 'Reviewed class register', 'records' => $session->records()->get()->map(fn ($row) => ['id' => $row->id, 'status' => $status, 'note' => 'Private teacher note'])->all()];
    }

    public function test_complete_attendance_flow_renders_screens_calculates_and_audits_corrections(): void
    {
        $session = $this->createSession();
        $this->assertSame(6, $session->records()->count());
        foreach (['teaching.attendance.index' => [], 'teaching.attendance.offering' => [$this->offering], 'teaching.attendance.show' => [$this->offering, $session]] as $route => $params) {
            $this->get(route($route, $params))->assertOk();
        }
        $course = $session->records()->first()->enrollmentCourse;
        $this->assertNull(app(AttendanceCalculator::class)->summary($course)['percentage']);
        $this->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session))->assertSessionHasNoErrors();
        $this->assertEquals(100, app(AttendanceCalculator::class)->summary($course)['percentage']);
        $this->assertEquals(10, app(AttendanceCalculator::class)->summary($course)['marks']);
        $this->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session, 'late'))->assertSessionHasNoErrors();
        $this->assertEquals(5, app(AttendanceCalculator::class)->summary($course)['marks']);
        $this->assertSame(3, $session->audits()->count());
        $audit = $session->audits()->latest('id')->first();
        $this->assertSame('present', $audit->before['records'][0]['status']);
        $this->assertSame('late', $audit->after['records'][0]['status']);
        $this->get(route('teaching.attendance.show', [$this->offering, $session]))->assertOk()->assertSee('Reviewed class register');
    }

    public function test_duplicate_future_out_of_term_and_pre_enrollment_dates_are_rejected(): void
    {
        $this->createSession();
        foreach (['2026-10-01', '2026-10-02', '2026-07-01', '2026-09-30'] as $date) {
            $this->postJson(route('teaching.attendance.store', $this->offering), ['held_on' => $date, 'type' => 'lecture', 'slot' => 1])->assertUnprocessable();
        }
        $this->createSession(2);
        $this->assertSame(2, AttendanceSession::count());
    }

    public function test_missing_invalid_and_foreign_records_are_rejected_without_partial_updates(): void
    {
        $session = $this->createSession();
        $data = $this->payload($session);
        array_pop($data['records']);
        $this->putJson(route('teaching.attendance.update', [$this->offering, $session]), $data)->assertUnprocessable();
        $data = $this->payload($session);
        $data['records'][0]['id'] = 99999;
        $this->putJson(route('teaching.attendance.update', [$this->offering, $session]), $data)->assertUnprocessable();
        $data = $this->payload($session);
        $data['records'][0]['status'] = 'unknown';
        $this->putJson(route('teaching.attendance.update', [$this->offering, $session]), $data)->assertUnprocessable();
        $this->assertSame(0, $session->records()->whereNotNull('status')->count());
    }

    public function test_correction_reason_and_revision_are_required_and_cancellation_excludes_session(): void
    {
        $session = $this->createSession();
        $data = $this->payload($session);
        $url = route('teaching.attendance.update', [$this->offering, $session]);
        $this->put($url, $data)->assertSessionHasNoErrors();
        $this->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('revision');
        $data = $this->payload($session, 'absent');
        $data['reason'] = '';
        $this->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->post(route('teaching.attendance.cancel', [$this->offering, $session]), ['revision' => 2, 'reason' => 'Class entered by mistake'])->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $session->fresh()->status);
        $this->assertNull(app(AttendanceCalculator::class)->summary($session->records()->first()->enrollmentCourse)['percentage']);
        $this->putJson($url, $this->payload($session))->assertUnprocessable();
    }

    public function test_only_assigned_teachers_with_permission_can_read_or_change_sessions(): void
    {
        $session = $this->createSession();
        $other = CourseOffering::where('course_code', 'DEMO-CS102')->firstOrFail();
        $this->get(route('teaching.attendance.offering', $other))->assertNotFound();
        $this->putJson(route('teaching.attendance.update', [$other, $session]), $this->payload($session))->assertNotFound();
        $shared = CourseOffering::where('course_code', 'DEMO-CS103')->firstOrFail();
        $this->get(route('teaching.attendance.show', [$shared, $session]))->assertNotFound();
        $this->teacher->revokePermissionTo('attendance.manage-assigned');
        $this->actingAs($this->teacher->fresh())->get(route('teaching.attendance.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('teaching.attendance.index'))->assertRedirect(route('login'));
    }

    public function test_policy_is_snapshotted_and_excused_sessions_do_not_reduce_percentage(): void
    {
        foreach (['present', 'late', 'absent', 'excused'] as $index => $status) {
            $session = $this->createSession($index + 1);
            $this->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session, $status))->assertSessionHasNoErrors();
        }
        config(['attendance.policy.late_credit' => 0]);
        $course = $this->offering->enrollmentCourses()->first();
        $summary = app(AttendanceCalculator::class)->summary($course);
        $this->assertSame(3, $summary['valid_sessions']);
        $this->assertEquals(50, $summary['percentage']);
        $this->assertEquals(5, $summary['marks']);
    }

    public function test_finalized_enrollments_and_closed_classes_are_read_only(): void
    {
        $session = $this->createSession();
        $enrollment = $session->records()->first()->enrollmentCourse->enrollment;
        $enrollment->update(['status' => 'promoted']);
        $this->putJson(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session))->assertUnprocessable();
        $this->get(route('teaching.attendance.show', [$this->offering, $session]))->assertOk()->assertSee('read-only');
        $this->offering->update(['status' => 'completed']);
        $this->postJson(route('teaching.attendance.store', $this->offering), ['held_on' => '2026-10-01', 'type' => 'lab', 'slot' => 1])->assertUnprocessable();
    }

    public function test_student_only_sees_own_completed_records_not_drafts_notes_or_other_students(): void
    {
        $session = $this->createSession();
        $this->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session))->assertSessionHasNoErrors();
        $this->createSession(2);
        $course = $session->records()->first()->enrollmentCourse;
        $user = User::create(['name' => 'Student Login', 'email' => 'attendance.student@example.test', 'phone' => '03000000999', 'password' => bcrypt('password')]);
        $user->assignRole('Student');
        $course->enrollment->student->update(['user_id' => $user->id]);
        $this->actingAs($user)->get(route('student.attendance.index'))->assertOk()->assertSee('100%');
        $this->get(route('student.attendance.show', $course))->assertOk()->assertSee('Present')->assertDontSee('Private teacher note')->assertViewHas('records', fn ($records) => $records->total() === 1);
        $foreign = StudentEnrollmentCourse::where('student_semester_enrollment_id', '!=', $course->student_semester_enrollment_id)->first();
        $this->get(route('student.attendance.show', $foreign))->assertNotFound();
        $this->get(route('teaching.attendance.index'))->assertForbidden();
    }

    public function test_published_result_values_remain_unchanged_and_attendance_is_locked(): void
    {
        $session = $this->createSession();
        $this->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session))->assertSessionHasNoErrors();
        $enrollment = $session->records()->first()->enrollmentCourse->enrollment;
        $admin = User::create(['name' => 'Exam Admin', 'email' => 'exam@example.test', 'phone' => '03000000998', 'password' => bcrypt('password')]);
        $admin->assignRole('Academic Admin');
        $this->actingAs($admin)->postJson(route('result.semester.store'), [
            'student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id, 'action' => 'publish',
            'courses' => $enrollment->courses->map(fn ($course) => ['student_enrollment_course_id' => $course->id, 'attendance_obtained_marks' => 8, 'mid_obtained_marks' => 28, 'final_obtained_marks' => 55])->all(),
        ])->assertCreated();
        $result = SemesterResult::with('items')->firstOrFail();
        $before = $result->toArray();
        $this->actingAs($this->teacher)->putJson(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session, 'absent'))->assertUnprocessable();
        $this->postJson(route('teaching.attendance.cancel', [$this->offering, $session]), ['revision' => 2, 'reason' => 'Attempted cancellation'])->assertUnprocessable();
        $this->assertSame($before, $result->fresh('items')->toArray());
    }

    public function test_student_with_attendance_history_cannot_be_deleted(): void
    {
        $session = $this->createSession();
        $student = $session->records()->first()->enrollmentCourse->enrollment->student;
        $this->teacher->assignRole('Academic Admin');
        $this->actingAs($this->teacher->fresh())->delete(route('deleteStudent', $student))->assertRedirect(route('allStudents'))->assertSessionHas('error');
        $this->assertDatabaseHas('students', ['id' => $student->id]);
        $this->assertSame(6, $session->records()->count());
    }

    public function test_co_teacher_can_save_shared_class_but_late_joiners_are_not_added_to_saved_roster(): void
    {
        $this->offering = CourseOffering::where('course_code', 'DEMO-CS103')->firstOrFail();
        $lateEnrollment = $this->offering->enrollmentCourses()->first()->enrollment;
        $lateEnrollment->update(['enrolled_at' => '2026-10-02']);
        $session = $this->createSession();
        $this->assertSame(5, $session->records()->count());
        $lateEnrollment->update(['enrolled_at' => '2026-10-01']);
        $otherTeacher = User::where('email', 'demo.teacher2@example.test')->firstOrFail();
        $otherTeacher->givePermissionTo('attendance.manage-assigned');
        $this->actingAs($otherTeacher)->put(route('teaching.attendance.update', [$this->offering, $session]), $this->payload($session, 'excused'))->assertSessionHasNoErrors();
        $this->assertSame(5, $session->records()->count());
        $this->assertNull(app(AttendanceCalculator::class)->summary($session->records()->first()->enrollmentCourse)['marks']);
    }
}
