<?php

namespace Tests\Feature\Result;

use App\Models\Academic\CourseOffering;
use App\Models\Assessment\AssessmentMark;
use App\Models\Assessment\AssessmentSubmission;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\User;
use App\Services\AcademicEnrollmentService;
use App\Services\AcademicResultCalculator;
use App\Services\Assessment\AssessmentWorkflow;
use App\Services\Attendance\AttendanceService;
use App\Services\Result\ResultModerationService;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ResultModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private StudentSemesterEnrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-01 12:00:00'));
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);
        $this->admin = User::create(['name' => 'Academic reviewer', 'email' => 'moderation@example.test', 'phone' => '03009999991', 'password' => bcrypt('password')]);
        $this->admin->assignRole('Academic Admin');
        $this->enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->actingAs($this->admin);
    }

    private function ready(): void
    {
        $workflow = app(AssessmentWorkflow::class);
        foreach (CourseOffering::all() as $offering) {
            $teacher = User::findOrFail($offering->teachers()->first()->user_id);
            $teacher->givePermissionTo(['assessments.manage-assigned', 'marks.manage-assigned', 'results.submit', 'attendance.manage-assigned']);
            $workflow->approveScheme($this->admin, $offering->id, ['attendance' => 10, 'assignment' => 10, 'quiz' => 10, 'midterm' => 30, 'final' => 40, 'practical' => 0, 'project' => 0]);
            foreach ($offering->assessmentComponents()->where('code', '!=', 'attendance')->get() as $component) {
                $assessment = $workflow->saveDefinition($teacher, $offering->id, ['component_id' => $component->id, 'title' => ucfirst($component->code).' 1', 'held_on' => '2026-10-01', 'maximum' => 100, 'weight' => $component->allocation]);
                $workflow->saveMarks($teacher, $offering->id, $assessment->id, ['revision' => 1, 'marks' => $offering->enrollmentCourses()->get()->map(fn ($course) => ['course_id' => $course->id, 'obtained' => 75])->all()]);
            }
            $attendance = app(AttendanceService::class);
            $session = $attendance->create($teacher, $offering->id, ['held_on' => '2026-10-01', 'type' => 'lecture', 'slot' => 1]);
            $attendance->update($teacher, $offering->id, $session->id, ['revision' => 1, 'records' => $session->records->map(fn ($record) => ['id' => $record->id, 'status' => 'present'])->all()]);
            $submission = $workflow->submit($teacher, $offering->id);
            $workflow->review($this->admin, $submission->id, 'approved', 'Verified all assessment evidence');
        }
    }

    private function approve(?StudentSemesterEnrollment $enrollment = null): SemesterResult
    {
        $enrollment ??= $this->enrollment;
        $preview = app(ResultModerationService::class)->preview($enrollment);
        $this->post(route('results.moderation.approve', $enrollment), ['token' => $preview['token'], 'reason' => 'Semester evidence checked'])->assertSessionHasNoErrors()->assertRedirect();

        return $enrollment->semesterResult()->firstOrFail();
    }

    private function publish(SemesterResult $result): void
    {
        $this->post(route('results.moderation.publish', $result->student_semester_enrollment_id), ['revision' => $result->revision])->assertSessionHasNoErrors()->assertRedirect();
    }

    private function correction(SemesterResult $result, float $score = 100): array
    {
        $marks = [];
        foreach ($result->items as $item) {
            foreach ($item->assessment_snapshot['student']['assessments'] as $assessment) {
                $marks[$item->id.'_'.$assessment['assessment_id']] = $score;
            }
        }

        return ['revision' => $result->revision, 'reason' => 'Verified the original marked papers', 'marks' => $marks];
    }

    public function test_approved_teacher_evidence_is_reviewed_published_and_displayed_without_manual_entry(): void
    {
        $this->ready();
        $this->assertSame(0, SemesterResult::count());
        $this->get(route('results.moderation.index'))->assertOk()->assertSee('Moderation');
        $this->get(route('results.moderation.show', $this->enrollment))->assertOk()->assertSee('77.5');
        $result = $this->approve();
        $this->assertSame('Draft', $result->status);
        $this->assertNull($result->published_at);
        $this->assertEquals(3.3, $result->sgpa);
        $this->assertCount(3, $result->items);
        $this->publish($result);
        $result->refresh();
        $this->assertSame('Pass', $result->status);
        $this->assertEquals(3.3, $result->cgpa);
        $this->assertEquals(77.5, $result->semester_percentage);
        $this->assertSame(2, $result->revision);
        $this->assertCount(2, $result->audits);
        $this->assertSame('active', $this->enrollment->fresh()->status);
        $this->assertTrue(app(AcademicEnrollmentService::class)->promotionEligibility($this->enrollment->fresh())['allowed']);
        $this->assertSame(0, CourseOffering::where('status', 'completed')->count());
        $this->get(route('results.moderation.show', $this->enrollment))->assertOk()->assertSee('Published')->assertSee('Frozen attendance');
        $this->getJson('/result/semester-result/'.$result->id.'/sheet')->assertOk()->assertJsonPath('sheet.items.0.assessment_snapshot.student.components.assignment', 7.5);
        $this->getJson('/result/semester-result/'.$result->id.'/data')->assertForbidden();
    }

    public function test_offerings_complete_only_after_every_enrolled_student_has_a_published_result(): void
    {
        $this->ready();
        foreach (StudentSemesterEnrollment::all() as $enrollment) {
            $this->publish($this->approve($enrollment));
        }
        $this->assertSame(3, CourseOffering::where('status', 'completed')->count());
        $this->assertSame(6, SemesterResult::whereNotNull('published_at')->count());
    }

    public function test_missing_returned_foreign_and_stale_submission_evidence_cannot_be_approved(): void
    {
        $this->get(route('results.moderation.show', $this->enrollment))->assertOk()->assertSee('Not ready');
        $this->postJson(route('results.moderation.approve', $this->enrollment), ['token' => str_repeat('a', 64), 'reason' => 'Review missing evidence'])->assertUnprocessable();
        $this->ready();
        $preview = app(ResultModerationService::class)->preview($this->enrollment);
        $this->postJson(route('results.moderation.approve', $this->enrollment), ['token' => str_repeat('a', 64), 'reason' => 'Stale preview rejected'])->assertUnprocessable();
        $submission = AssessmentSubmission::first();
        $submission->update(['status' => 'returned']);
        $this->postJson(route('results.moderation.approve', $this->enrollment), ['token' => $preview['token'], 'reason' => 'Returned evidence rejected'])->assertUnprocessable();
        $snapshot = $submission->snapshot;
        $snapshot['students'][0]['student_id'] = 99999;
        $submission->update(['status' => 'approved', 'snapshot' => $snapshot]);
        $this->postJson(route('results.moderation.approve', $this->enrollment), ['token' => $preview['token'], 'reason' => 'Foreign roster rejected'])->assertUnprocessable();
        $this->assertSame(0, SemesterResult::count());
    }

    public function test_permission_separation_self_moderation_and_duplicate_publication(): void
    {
        $this->ready();
        $hod = User::create(['name' => 'Coordinator', 'email' => 'hod@example.test', 'phone' => '03009999992', 'password' => bcrypt('password')]);
        $hod->assignRole('HOD / Program Coordinator');
        $this->actingAs($hod);
        $result = $this->approve();
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertForbidden();
        $teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $teacher->givePermissionTo(['results.view-all', 'results.approve', 'results.publish']);
        $this->actingAs($teacher)->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertForbidden();
        $this->actingAs($this->admin);
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 99])->assertUnprocessable();
        $this->publish($result);
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 2])->assertUnprocessable();
        $this->postJson(route('results.moderation.approve', $this->enrollment), ['token' => str_repeat('a', 64), 'reason' => 'Duplicate not allowed'])->assertUnprocessable();
        $this->assertSame(1, SemesterResult::count());
    }

    public function test_snapshots_ignore_live_marks_and_later_policy_changes(): void
    {
        $this->ready();
        $result = $this->approve();
        AssessmentMark::query()->update(['obtained' => 0]);
        config(['academic.grade_scale' => [['minimum_percentage' => 0, 'grade' => 'F', 'grade_point' => 0, 'status' => 'Fail']]]);
        $this->publish($result);
        $result->refresh();
        $this->assertSame('Pass', $result->status);
        $this->assertEquals(3.3, $result->sgpa);
        $this->assertEquals(77.5, $result->items->first()->obtained_marks);
    }

    public function test_corrections_require_permission_valid_marks_reason_revision_and_preserve_original_submission(): void
    {
        $this->ready();
        $result = $this->approve();
        $this->publish($result);
        $result->refresh()->load('items');
        $original = AssessmentSubmission::first()->snapshot;
        $url = route('results.moderation.correct', $this->enrollment);
        $payload = $this->correction($result);
        $this->putJson($url, $payload)->assertForbidden();
        $this->admin->givePermissionTo('results.edit-published');
        $this->putJson($url, array_merge($payload, ['reason' => '']))->assertUnprocessable();
        $bad = $payload;
        $bad['marks'][array_key_first($bad['marks'])] = 101;
        $this->putJson($url, $bad)->assertUnprocessable();
        $bad = $payload;
        $bad['marks']['999_999'] = 1;
        $this->putJson($url, $bad)->assertUnprocessable();
        $this->assertEquals(77.5, $result->items()->first()->obtained_marks);
        $this->put($url, $payload)->assertSessionHasNoErrors();
        $result->refresh();
        $this->assertEquals(4, $result->sgpa);
        $this->assertEquals(4, $result->cgpa);
        $this->assertSame(3, $result->revision);
        $this->assertNotNull($result->published_at);
        $this->assertSame($original, AssessmentSubmission::first()->snapshot);
        $audit = $result->audits()->latest('id')->first();
        $this->assertSame('published_correction', $audit->action);
        $this->assertEquals(3.3, $audit->before['sgpa']);
        $this->assertEquals(4, $audit->after['sgpa']);
        $this->putJson($url, $payload)->assertUnprocessable();
        $this->get(route('results.moderation.show', $this->enrollment))->assertOk()->assertSee('Correct published assessment marks');
    }

    public function test_final_minimum_can_fail_a_course_despite_passing_total(): void
    {
        config(['academic.assessment.final_minimum.enabled' => true, 'academic.assessment.final_minimum.minimum_percentage' => 80]);
        $this->ready();
        $result = $this->approve();
        $this->publish($result);
        $this->assertSame('Fail', $result->fresh()->status);
        $this->assertEquals(0, $result->fresh()->sgpa);
        $this->assertEquals(77.5, $result->fresh()->semester_percentage);
        $this->assertTrue(app(AcademicEnrollmentService::class)->promotionEligibility($this->enrollment->fresh())['allowed']);
    }

    public function test_correction_updates_later_semester_cgpa_without_rewriting_its_course_grades(): void
    {
        $this->ready();
        $result = $this->approve();
        $this->publish($result);
        $result->refresh()->load('items');
        $this->travel(4)->months();
        $laterEnrollment = StudentSemesterEnrollment::create(array_merge($this->enrollment->only(['student_id', 'department_id', 'section_id', 'academic_year']), ['semester' => 'Semester 2', 'status' => 'active', 'enrolled_at' => today()]));
        $laterResult = SemesterResult::create(['student_semester_enrollment_id' => $laterEnrollment->id, 'student_id' => $this->enrollment->student_id, 'status' => 'Pass', 'published_at' => now(), 'sgpa' => 4, 'semester_percentage' => 100]);
        $old = $result->items->first();
        $course = $laterEnrollment->courses()->create(array_merge($old->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks']), ['registration_type' => 'repeat']));
        $laterResult->items()->create(array_merge($old->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks']), ['student_enrollment_course_id' => $course->id, 'obtained_marks' => 100, 'percentage' => 100, 'grade' => 'A', 'grade_point' => 4, 'status' => 'Pass']));
        app(AcademicResultCalculator::class)->recalculatePublishedCgpas($this->enrollment->student_id);
        $this->assertEquals(3.53, $laterResult->fresh()->cgpa);
        $this->admin->givePermissionTo('results.edit-published');
        $this->put(route('results.moderation.correct', $this->enrollment), $this->correction($result))->assertSessionHasNoErrors();
        $this->assertEquals(4, $laterResult->fresh()->cgpa);
        $this->assertEquals(4, $laterResult->items()->first()->grade_point);
        $this->assertEquals(100, $laterResult->items()->first()->obtained_marks);
    }

    public function test_closed_terms_cannot_publish_and_legacy_endpoint_cannot_overwrite_teacher_results(): void
    {
        $this->ready();
        $result = $this->approve();
        $this->enrollment->term->update(['status' => 'closed']);
        $this->postJson(route('results.moderation.publish', $this->enrollment), ['revision' => 1])->assertUnprocessable();
        $this->putJson('/result/semester-result/'.$result->id, ['student_id' => $this->enrollment->student_id, 'student_semester_enrollment_id' => $this->enrollment->id, 'action' => 'publish', 'courses' => []])->assertUnprocessable();
        $this->assertNull($result->fresh()->published_at);
        $this->getJson('/result/semester-result/'.$result->id.'/data')->assertUnprocessable();
        $studentUser = User::where('email', 'demo.teacher2@example.test')->firstOrFail();
        $this->actingAs($studentUser)->get(route('results.moderation.index'))->assertForbidden();
    }
}
