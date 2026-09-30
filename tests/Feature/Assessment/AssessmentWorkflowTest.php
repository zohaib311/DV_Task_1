<?php

namespace Tests\Feature\Assessment;

use App\Models\Academic\CourseOffering;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentComponent;
use App\Models\Assessment\AssessmentSubmission;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\User;
use App\Services\Assessment\AssessmentCalculator;
use App\Services\Attendance\AttendanceService;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AssessmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $admin;

    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-01 12:00:00'));
        $this->seed([AccessControlSeeder::class, TeacherPanelDemoSeeder::class]);
        $this->teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->teacher->givePermissionTo(['assessments.manage-assigned', 'marks.manage-assigned', 'results.submit', 'attendance.manage-assigned']);
        $this->admin = User::create(['name' => 'Reviewer', 'email' => 'reviewer@example.test', 'phone' => '03000000888', 'password' => bcrypt('password')]);
        $this->admin->assignRole('Academic Admin');
        $this->offering = CourseOffering::where('course_code', 'DEMO-CS101')->firstOrFail();
    }

    private function allocations(): array
    {
        return ['attendance' => 10, 'assignment' => 10, 'quiz' => 10, 'midterm' => 30, 'final' => 40, 'practical' => 0, 'project' => 0];
    }

    private function approveScheme(): void
    {
        $this->actingAs($this->admin)->post(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $this->allocations()])->assertSessionHasNoErrors();
        $this->offering->refresh();
        $this->actingAs($this->teacher);
    }

    private function definition(string $component = 'assignment', string $title = 'Assignment 1', float $weight = 10): Assessment
    {
        $componentId = $this->offering->assessmentComponents()->where('code', $component)->firstOrFail()->id;
        $this->post(route('teaching.assessments.store', $this->offering), ['component_id' => $componentId, 'title' => $title, 'held_on' => '2026-10-01', 'maximum' => 20, 'weight' => $weight])->assertSessionHasNoErrors();

        return Assessment::where('assessment_component_id', $componentId)->where('title', $title)->firstOrFail();
    }

    private function marksPayload(Assessment $assessment, mixed $score = 15): array
    {
        return ['revision' => $assessment->fresh()->revision, 'reason' => 'Checked against the marked papers', 'marks' => $this->offering->enrollmentCourses()->orderBy('id')->get()->map(fn ($course) => ['course_id' => $course->id, 'obtained' => $score])->all()];
    }

    private function ready(): void
    {
        $this->approveScheme();
        foreach (['assignment' => 10, 'quiz' => 10, 'midterm' => 30, 'final' => 40] as $component => $weight) {
            $assessment = $this->definition($component, ucfirst($component).' 1', $weight);
            $this->put(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment))->assertSessionHasNoErrors();
        }
        $service = app(AttendanceService::class);
        $session = $service->create($this->teacher, $this->offering->id, ['held_on' => '2026-10-01', 'type' => 'lecture', 'slot' => 1]);
        $service->update($this->teacher, $this->offering->id, $session->id, ['revision' => 1, 'records' => $session->records->map(fn ($r) => ['id' => $r->id, 'status' => 'present'])->all()]);
    }

    public function test_end_to_end_weighted_marks_submission_return_resubmit_and_approval(): void
    {
        $this->ready();
        $preview = app(AssessmentCalculator::class)->preview($this->offering);
        $this->assertTrue($preview['complete']);
        $this->assertEquals(77.5, $preview['students'][0]['obtained']);
        foreach (['teaching.assessments.index' => [], 'teaching.assessments.show' => [$this->offering], 'teaching.assessments.edit' => [$this->offering, Assessment::first()]] as $route => $params) {
            $this->get(route($route, $params))->assertOk();
        }
        $this->post(route('teaching.assessments.submit', $this->offering))->assertSessionHasNoErrors();
        $submission = AssessmentSubmission::firstOrFail();
        $snapshot = $submission->snapshot;
        $this->assertSame('marks_submitted', $this->offering->fresh()->status);
        $this->actingAs($this->admin)->get(route('academic.assessment-reviews.index'))->assertOk();
        $this->get(route('academic.assessment-reviews.show', $submission))->assertOk()->assertSee('77.5');
        $this->post(route('academic.assessment-reviews.update', $submission), ['decision' => 'returned', 'reason' => 'Please check assignment marks'])->assertSessionHasNoErrors();
        $this->assertSame('active', $this->offering->fresh()->status);
        $assessment = Assessment::first();
        $this->actingAs($this->teacher)->put(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment, 20))->assertSessionHasNoErrors();
        $this->assertSame($snapshot, $submission->fresh()->snapshot);
        $this->post(route('teaching.assessments.submit', $this->offering))->assertSessionHasNoErrors();
        $second = AssessmentSubmission::latest('id')->first();
        $this->actingAs($this->admin)->post(route('academic.assessment-reviews.update', $second), ['decision' => 'approved', 'reason' => 'All marks and allocations verified'])->assertSessionHasNoErrors();
        $this->assertSame('reviewed', $this->offering->fresh()->status);
        $this->assertEquals(80, $second->fresh()->snapshot['students'][0]['obtained']);
        $this->assertSame(0, SemesterResult::count());
    }

    public function test_scheme_total_attendance_and_immutability_are_enforced(): void
    {
        $data = $this->allocations();
        $data['final'] = 39;
        $this->actingAs($this->admin)->postJson(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $data])->assertUnprocessable();
        $data['final'] = 41;
        $data['attendance'] = 9;
        $this->postJson(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $data])->assertUnprocessable();
        $this->approveScheme();
        $this->actingAs($this->admin)->get(route('academic.assessment-schemes.edit', $this->offering))->assertOk()->assertSee('read-only');
        $this->postJson(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $this->allocations()])->assertUnprocessable();
        $this->assertSame(5, AssessmentComponent::count());
    }

    public function test_multiple_assessments_fit_allocation_and_weighted_scores_are_summed(): void
    {
        $this->approveScheme();
        $first = $this->definition('assignment', 'Assignment 1', 5);
        $second = $this->definition('assignment', 'Assignment 2', 5);
        $this->put(route('teaching.assessments.marks', [$this->offering, $first]), $this->marksPayload($first, 10))->assertSessionHasNoErrors();
        $this->put(route('teaching.assessments.marks', [$this->offering, $second]), $this->marksPayload($second, 20))->assertSessionHasNoErrors();
        $preview = app(AssessmentCalculator::class)->preview($this->offering);
        $this->assertEquals(7.5, $preview['students'][0]['components']['assignment']);
        $this->postJson(route('teaching.assessments.store', $this->offering), ['component_id' => $first->assessment_component_id, 'title' => 'Over budget', 'maximum' => 20, 'weight' => 1, 'held_on' => '2026-10-01'])->assertUnprocessable();
    }

    public function test_invalid_and_foreign_marks_missing_roster_and_stale_writes_are_rejected(): void
    {
        $this->approveScheme();
        $assessment = $this->definition();
        $url = route('teaching.assessments.marks', [$this->offering, $assessment]);
        foreach ([-1, 21, 1.234, 'invalid'] as $score) {
            $this->putJson($url, $this->marksPayload($assessment, $score))->assertUnprocessable();
        }
        $data = $this->marksPayload($assessment);
        $data['marks'][0]['course_id'] = 99999;
        $this->putJson($url, $data)->assertUnprocessable();
        $data = $this->marksPayload($assessment);
        array_pop($data['marks']);
        $this->putJson($url, $data)->assertUnprocessable();
        $data = $this->marksPayload($assessment);
        $this->put($url, $data)->assertSessionHasNoErrors();
        $this->putJson($url, $data)->assertUnprocessable();
        $this->assertSame(6, $assessment->marks()->count());
    }

    public function test_blank_and_zero_are_different_and_corrections_require_reason(): void
    {
        $this->approveScheme();
        $assessment = $this->definition();
        $url = route('teaching.assessments.marks', [$this->offering, $assessment]);
        $this->put($url, $this->marksPayload($assessment, null))->assertSessionHasNoErrors();
        $this->assertNull(app(AssessmentCalculator::class)->preview($this->offering)['students'][0]['components']['assignment']);
        $this->put($url, $this->marksPayload($assessment, 0))->assertSessionHasNoErrors();
        $this->assertEquals(0, app(AssessmentCalculator::class)->preview($this->offering)['students'][0]['components']['assignment']);
        $data = $this->marksPayload($assessment, 5);
        $data['reason'] = '';
        $this->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('reason');
    }

    public function test_submitted_classes_lock_marks_attendance_and_duplicate_submission(): void
    {
        $this->ready();
        $this->post(route('teaching.assessments.submit', $this->offering))->assertSessionHasNoErrors();
        $assessment = Assessment::first();
        $this->putJson(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment))->assertUnprocessable();
        $this->postJson(route('teaching.assessments.submit', $this->offering))->assertUnprocessable();
        $session = $this->offering->attendanceSessions()->first();
        $this->postJson(route('teaching.attendance.cancel', [$this->offering, $session]), ['revision' => 2, 'reason' => 'Attempt cancel'])->assertUnprocessable();
        $this->assertSame(1, AssessmentSubmission::count());
    }

    public function test_incomplete_marks_or_attendance_or_component_allocation_blocks_submission(): void
    {
        $this->approveScheme();
        $this->definition('assignment', 'Half allocation', 5);
        $this->postJson(route('teaching.assessments.submit', $this->offering))->assertUnprocessable()->assertJsonValidationErrors('submission');
        $this->assertSame(0, AssessmentSubmission::count());
    }

    public function test_teacher_cannot_configure_scheme_review_or_access_another_class(): void
    {
        $this->actingAs($this->teacher)->get(route('academic.assessment-schemes.edit', $this->offering))->assertForbidden();
        $this->get(route('academic.assessment-reviews.index'))->assertForbidden();
        $other = CourseOffering::where('course_code', 'DEMO-CS102')->first();
        $this->get(route('teaching.assessments.show', $other))->assertNotFound();
        $this->approveScheme();
        $assessment = $this->definition();
        $this->get(route('teaching.assessments.edit', [$other, $assessment]))->assertNotFound();
        $this->teacher->revokePermissionTo('marks.manage-assigned');
        $this->actingAs($this->teacher->fresh())->putJson(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment))->assertForbidden();
    }

    public function test_assigned_reviewer_cannot_approve_own_class_and_reviews_are_single_use(): void
    {
        $this->ready();
        $this->post(route('teaching.assessments.submit', $this->offering))->assertSessionHasNoErrors();
        $submission = AssessmentSubmission::first();
        $this->teacher->givePermissionTo('assessments.review');
        $this->actingAs($this->teacher->fresh())->postJson(route('academic.assessment-reviews.update', $submission), ['decision' => 'approved', 'reason' => 'Self approval attempt'])->assertForbidden();
        $data = ['decision' => 'approved', 'reason' => 'Independent review passed'];
        $this->actingAs($this->admin)->post(route('academic.assessment-reviews.update', $submission), $data)->assertSessionHasNoErrors();
        $this->postJson(route('academic.assessment-reviews.update', $submission), $data)->assertUnprocessable();
    }

    public function test_legacy_result_entry_cannot_bypass_approved_assessment_scheme(): void
    {
        $this->approveScheme();
        $enrollment = $this->offering->enrollmentCourses()->first()->enrollment;
        $this->actingAs($this->admin)->postJson(route('result.semester.store'), ['student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id, 'action' => 'publish', 'courses' => $enrollment->courses->map(fn ($course) => ['student_enrollment_course_id' => $course->id, 'attendance_obtained_marks' => 10, 'mid_obtained_marks' => 30, 'final_obtained_marks' => 60])->all()])->assertUnprocessable()->assertJsonValidationErrors('assessment_workflow');
        $this->assertSame(0, SemesterResult::count());
    }

    public function test_definition_edits_are_versioned_and_marked_definitions_cannot_change(): void
    {
        $this->approveScheme();
        $assessment = $this->definition();
        $data = ['component_id' => $assessment->assessment_component_id, 'title' => 'Revised assignment', 'held_on' => '2026-10-01', 'maximum' => 25, 'weight' => 10, 'revision' => 1];
        $this->put(route('teaching.assessments.update', [$this->offering, $assessment]), $data)->assertSessionHasNoErrors();
        $this->assertEquals(25, $assessment->fresh()->maximum);
        $this->putJson(route('teaching.assessments.update', [$this->offering, $assessment]), $data)->assertUnprocessable();
        $this->put(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment))->assertSessionHasNoErrors();
        $data['revision'] = $assessment->fresh()->revision;
        $this->putJson(route('teaching.assessments.update', [$this->offering, $assessment]), $data)->assertUnprocessable();
        $this->assertTrue($this->offering->assessmentAudits()->where('action', 'marks_saved')->exists());
    }

    public function test_practical_project_future_dates_and_foreign_components(): void
    {
        $allocations = $this->allocations();
        $allocations['final'] = 20;
        $allocations['practical'] = 10;
        $allocations['project'] = 10;
        $this->actingAs($this->admin)->post(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $allocations])->assertSessionHasNoErrors();
        $this->actingAs($this->teacher);
        $this->definition('practical', 'Lab exam', 10);
        $project = $this->definition('project', 'Capstone', 10);
        $data = ['component_id' => $project->assessment_component_id, 'title' => 'Capstone', 'held_on' => '2026-10-02', 'maximum' => 20, 'weight' => 10, 'revision' => 1];
        $this->put(route('teaching.assessments.update', [$this->offering, $project]), $data)->assertSessionHasNoErrors();
        $this->putJson(route('teaching.assessments.marks', [$this->offering, $project]), $this->marksPayload($project))->assertUnprocessable();
        $data['component_id'] = 99999;
        $this->postJson(route('teaching.assessments.store', $this->offering), $data)->assertNotFound();
        $data['component_id'] = $this->offering->assessmentComponents()->where('code', 'attendance')->first()->id;
        $this->postJson(route('teaching.assessments.store', $this->offering), $data)->assertUnprocessable();
    }

    public function test_existing_results_cannot_be_reconfigured_and_guests_students_cannot_view_marks(): void
    {
        $enrollment = $this->offering->enrollmentCourses()->first()->enrollment;
        $enrollment->semesterResult()->create(['student_id' => $enrollment->student_id, 'status' => 'Draft']);
        $this->actingAs($this->admin)->postJson(route('academic.assessment-schemes.store', $this->offering), ['allocations' => $this->allocations()])->assertUnprocessable();
        $studentUser = User::create(['name' => 'Student', 'email' => 'marks-student@example.test', 'phone' => '03000000887', 'password' => bcrypt('password')]);
        $studentUser->assignRole('Student');
        $this->actingAs($studentUser)->get(route('teaching.assessments.show', $this->offering))->assertForbidden();
        $this->get(route('academic.assessment-reviews.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('teaching.assessments.index'))->assertRedirect(route('login'));
    }

    public function test_submission_with_draft_attendance_and_finalized_enrollment_is_blocked(): void
    {
        $this->ready();
        app(AttendanceService::class)->create($this->teacher, $this->offering->id, ['held_on' => '2026-10-01', 'type' => 'lab', 'slot' => 1]);
        $this->postJson(route('teaching.assessments.submit', $this->offering))->assertUnprocessable();
        $this->offering->enrollmentCourses()->first()->enrollment->update(['status' => 'promoted']);
        $assessment = Assessment::first();
        $this->putJson(route('teaching.assessments.marks', [$this->offering, $assessment]), $this->marksPayload($assessment))->assertUnprocessable();
    }

    public function test_approved_planned_offering_can_activate_but_cannot_change_its_section(): void
    {
        $source = $this->offering;
        $section = Section::create(['name' => 'Planned class', 'department_id' => $source->department_id]);
        $planned = $source->replicate();
        $planned->section_id = $section->id;
        $planned->status = 'planned';
        $planned->save();
        $teacherIds = $source->teachers()->pluck('teachers.id')->all();
        $planned->teachers()->attach($teacherIds);
        $this->offering = $planned;
        $this->approveScheme();
        $otherSection = Section::create(['name' => 'Other class', 'department_id' => $source->department_id]);
        $payload = ['academic_term_id' => $planned->academic_term_id, 'curriculum_course_id' => $planned->curriculum_course_id, 'section_id' => $otherSection->id, 'teacher_ids' => $teacherIds, 'status' => 'active'];
        $this->actingAs($this->admin)->putJson(route('academic.offerings.update', $planned), $payload)->assertUnprocessable();
        $payload['section_id'] = $section->id;
        $this->put(route('academic.offerings.update', $planned), $payload)->assertSessionHasNoErrors();
        $this->assertSame('active', $planned->fresh()->status);
        $this->assertSame(5, $planned->assessmentComponents()->count());
    }
}
