<?php

namespace Tests\Feature;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesAcademicOfferings;
use Tests\TestCase;

class AcademicSetupTest extends TestCase
{
    use CreatesAcademicOfferings, RefreshDatabase;

    private User $admin;

    private Student $student;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'phone' => '03009999999', 'password' => bcrypt('password')]);
        $this->admin->assignRole('Academic Admin');
        $department = Department::create(['name' => 'Computing']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $this->student = Student::create(['name' => 'Student', 'email' => 'student@test.com', 'phone' => '03001111111', 'department_id' => $department->id, 'section_id' => $section->id]);
        $this->course = Course::create(['code' => 'CS101', 'name' => 'Programming', 'description' => 'Core course', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $this->actingAs($this->admin);
    }

    public function test_admin_can_create_calendar_curriculum_offering_and_enroll_through_http(): void
    {
        $this->post(route('academic.years.store'), ['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31'])->assertSessionHasNoErrors();
        $this->post(route('academic.terms.store'), ['academic_year_id' => AcademicYear::first()->id, 'name' => 'Fall 2026', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-31', 'status' => 'active'])->assertSessionHasNoErrors();
        $this->post(route('academic.curricula.store'), ['department_id' => $this->student->department_id, 'semester' => 'Semester 1', 'version' => '2026', 'course_ids' => [$this->course->id]])->assertSessionHasNoErrors();
        $curriculum = SemesterCurriculum::firstOrFail();
        $this->post(route('academic.curricula.approve', $curriculum))->assertSessionHasNoErrors();
        $teacher = Teacher::create(['name' => 'Teacher', 'email' => 'teacher@test.com', 'phone' => '03002222222', 'course' => 'CS101', 'user_id' => $this->admin->id]);
        $term = AcademicTerm::firstOrFail();
        $this->post(route('academic.offerings.store'), ['academic_term_id' => $term->id, 'curriculum_course_id' => $curriculum->courses()->first()->id, 'section_id' => $this->student->section_id, 'teacher_ids' => [$teacher->id], 'status' => 'active'])->assertSessionHasNoErrors();
        $offering = CourseOffering::firstOrFail();
        $plan = ['student_id' => $this->student->id, 'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id];
        $this->getJson(route('enrollment.offerings', $plan))->assertOk()->assertJsonPath('offerings.0.teachers', 'Teacher')->assertJsonPath('offerings.0.type', 'required');
        $this->post(route('addEnrollment'), $plan + ['offering_ids' => [$offering->id]])->assertSessionHasNoErrors()->assertRedirect(route('allEnrollments'));
        $this->assertDatabaseHas('student_enrollment_courses', ['course_offering_id' => $offering->id, 'course_code' => 'CS101', 'registration_type' => 'required']);

        foreach (['academic.terms.index', 'academic.curricula.index', 'academic.curricula.create', 'academic.offerings.index', 'academic.offerings.create', 'allEnrollments', 'addEnrollmentForm'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('academic.terms.edit', $term))->assertOk();
        $this->get(route('academic.curricula.edit', $curriculum))->assertOk()->assertSee('read-only');
        $this->get(route('academic.offerings.edit', $offering))->assertOk();
        $this->get(route('promoteEnrollmentForm', StudentSemesterEnrollment::first()))->assertOk();
    }

    public function test_used_courses_and_assigned_teachers_cannot_be_deleted(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $offering = CourseOffering::findOrFail($plan['offering_ids'][0]);
        $teacher = $offering->teachers()->firstOrFail();
        $this->delete(route('deleteCourse', $this->course))->assertRedirect(route('allCourses'))->assertSessionHas('error');
        $this->get(route('allCourses'))->assertOk()->assertSee('cannot be deleted');
        $this->delete(route('deleteTeacher', $teacher))->assertRedirect(route('allTeachers'))->assertSessionHas('error');
        $this->get(route('allTeachers'))->assertOk()->assertSee('cannot be deleted');
        $this->assertDatabaseHas('courses', ['id' => $this->course->id]);
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseHas('course_offering_teacher', ['course_offering_id' => $offering->id, 'teacher_id' => $teacher->id]);
    }

    public function test_term_dates_must_fit_the_year_and_duplicate_names_are_rejected(): void
    {
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $payload = ['academic_year_id' => $year->id, 'name' => 'Fall', 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-01', 'status' => 'active'];
        $this->postJson(route('academic.terms.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('starts_on');
        $payload['starts_on'] = '2026-09-01';
        $this->post(route('academic.terms.store'), $payload)->assertSessionHasNoErrors();
        $this->postJson(route('academic.terms.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_snapshots_survive_catalog_edits_and_result_entry_uses_the_saved_course_identity(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $this->course->update(['code' => 'CHANGED', 'name' => 'Changed name', 'credit_hours' => 4, 'attendance_marks' => 20, 'mid_marks' => 20]);
        $this->post(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertSessionHasNoErrors();
        $enrollment = StudentSemesterEnrollment::firstOrFail();
        $this->assertDatabaseHas('student_enrollment_courses', ['course_code' => 'CS101', 'credit_hours' => 3, 'attendance_marks' => 10, 'mid_marks' => 30]);
        $this->getJson(route('result.enrollment.data', $enrollment))->assertOk()->assertJsonPath('courses.0.course_code', 'CS101')->assertJsonPath('courses.0.course_name', 'Programming');
        $this->postJson(route('result.semester.store'), ['student_id' => $this->student->id, 'enrollment_id' => $enrollment->id, 'action' => 'publish', 'courses' => [['student_enrollment_course_id' => $enrollment->courses()->first()->id, 'attendance_obtained_marks' => 10, 'mid_obtained_marks' => 30, 'final_obtained_marks' => 60]]])->assertCreated();
        $this->assertDatabaseHas('semester_result_items', ['course_code' => 'CS101', 'course_name' => 'Programming', 'credit_hours' => 3, 'obtained_marks' => 100]);
    }

    public function test_enrollment_rejects_missing_required_courses_wrong_section_and_inactive_offerings(): void
    {
        $other = $this->course->replicate();
        $other->code = 'CS102';
        $other->save();
        $plan = $this->offeringPlan($this->student, [$this->course, $other]);
        $payload = $plan + ['student_id' => $this->student->id];
        $payload['offering_ids'] = [$plan['offering_ids'][0]];
        $this->postJson(route('addEnrollment'), $payload)->assertUnprocessable()->assertJsonValidationErrors('offering_ids');
        $offering = CourseOffering::find($plan['offering_ids'][1]);
        $offering->update(['status' => 'planned']);
        $this->postJson(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertUnprocessable();
        $section = Section::create(['name' => 'B', 'department_id' => $this->student->department_id]);
        $offering->update(['status' => 'active', 'section_id' => $section->id]);
        $this->postJson(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertUnprocessable();
        $this->assertDatabaseCount('student_semester_enrollments', 0);
    }

    public function test_only_one_active_enrollment_is_allowed_and_legacy_payloads_cannot_bypass_offerings(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $this->postJson(route('addEnrollment'), ['student_id' => $this->student->id, 'academic_year' => '2026-2027', 'semester' => 'Semester 2', 'course_ids' => [$this->course->id]])->assertUnprocessable()->assertJsonValidationErrors(['academic_term_id', 'semester_curriculum_id', 'offering_ids']);
        $this->post(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertSessionHasNoErrors();
        $this->postJson(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertUnprocessable()->assertJsonValidationErrors('student_id');
        $this->assertDatabaseCount('student_semester_enrollments', 1);
    }

    public function test_approved_curriculum_and_active_offering_are_locked(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $this->putJson(route('academic.curricula.update', $plan['semester_curriculum_id']), ['department_id' => $this->student->department_id, 'semester' => 'Semester 2', 'version' => 'Changed', 'course_ids' => [$this->course->id]])->assertUnprocessable()->assertJsonValidationErrors('curriculum');
        $offering = CourseOffering::find($plan['offering_ids'][0]);
        $this->putJson(route('academic.offerings.update', $offering), ['academic_term_id' => $offering->academic_term_id, 'curriculum_course_id' => $offering->curriculum_course_id, 'section_id' => $offering->section_id, 'teacher_ids' => $offering->teachers->pluck('id')->all(), 'status' => 'planned'])->assertUnprocessable()->assertJsonValidationErrors('offering');
        $this->assertSame('active', $offering->fresh()->status);
    }

    public function test_students_and_teachers_cannot_access_academic_admin_endpoints(): void
    {
        foreach (['Student', 'Teacher'] as $role) {
            $this->admin->syncRoles([$role]);
            $this->actingAs($this->admin);
            foreach (['academic.terms.index', 'academic.curricula.index', 'academic.offerings.index', 'academic.offerings.create'] as $route) {
                $this->get(route($route))->assertForbidden();
            }
            $this->postJson(route('academic.terms.store'), [])->assertForbidden();
            $this->postJson(route('academic.curricula.store'), [])->assertForbidden();
            $this->postJson(route('academic.offerings.store'), [])->assertForbidden();
            $this->getJson(route('enrollment.offerings'))->assertForbidden();
        }
    }

    public function test_reseeding_does_not_reset_admin_permission_customizations(): void
    {
        $role = Role::findByName('Academic Admin');
        $role->revokePermissionTo('offerings.manage');
        $this->seed(AccessControlSeeder::class);
        $this->assertFalse($role->fresh()->hasPermissionTo('offerings.manage'));
    }

    public function test_offering_creation_validates_department_teachers_and_duplicate_delivery(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $offering = CourseOffering::find($plan['offering_ids'][0]);
        $payload = ['academic_term_id' => $offering->academic_term_id, 'curriculum_course_id' => $offering->curriculum_course_id, 'section_id' => $offering->section_id, 'teacher_ids' => $offering->teachers->pluck('id')->all(), 'status' => 'planned'];
        $this->postJson(route('academic.offerings.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('curriculum_course_id');
        $otherDepartment = Department::create(['name' => 'Business']);
        $section = Section::create(['name' => 'Business A', 'department_id' => $otherDepartment->id]);
        $this->postJson(route('academic.offerings.store'), array_replace($payload, ['section_id' => $section->id]))->assertUnprocessable()->assertJsonValidationErrors('curriculum_course_id');
        $section->update(['department_id' => $this->student->department_id]);
        $teacher = Teacher::create(['name' => 'Unlinked', 'email' => 'unlinked@test.com', 'phone' => '03003333333', 'course' => 'CS101']);
        $this->postJson(route('academic.offerings.store'), array_replace($payload, ['section_id' => $section->id, 'teacher_ids' => [$teacher->id]]))->assertUnprocessable()->assertJsonValidationErrors('teacher_ids');
        $this->assertDatabaseCount('course_offerings', 1);
    }

    public function test_planned_offering_can_assign_multiple_teachers_and_activate_once(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $offering = CourseOffering::find($plan['offering_ids'][0]);
        $offering->update(['status' => 'planned']);
        $teacher = Teacher::create(['name' => 'Second teacher', 'email' => 'second@test.com', 'phone' => '03003333333', 'course' => 'CS101', 'user_id' => $this->admin->id]);
        $teacherIds = $offering->teachers->pluck('id')->push($teacher->id)->all();
        $this->put(route('academic.offerings.update', $offering), ['academic_term_id' => $offering->academic_term_id, 'curriculum_course_id' => $offering->curriculum_course_id, 'section_id' => $offering->section_id, 'teacher_ids' => $teacherIds, 'status' => 'active'])->assertSessionHasNoErrors();
        $this->assertSame('active', $offering->fresh()->status);
        $this->assertCount(2, $offering->fresh()->teachers);
    }

    public function test_electives_are_optional_and_repeat_courses_need_a_previous_published_attempt(): void
    {
        $elective = $this->course->replicate();
        $elective->code = 'CS102';
        $elective->save();
        $plan = $this->offeringPlan($this->student, [$this->course, $elective]);
        $electiveOffering = CourseOffering::find($plan['offering_ids'][1]);
        $electiveOffering->curriculumCourse->update(['type' => 'elective']);
        $repeat = $this->course->replicate();
        $repeat->code = 'CS099';
        $repeat->save();
        $repeatPlan = $this->offeringPlan($this->student, [$repeat], 'Semester 1');
        $payload = $plan + ['student_id' => $this->student->id];
        $payload['offering_ids'] = [$plan['offering_ids'][0], $repeatPlan['offering_ids'][0]];
        $this->postJson(route('addEnrollment'), $payload)->assertUnprocessable()->assertJsonValidationErrors('offering_ids');

        $previous = StudentSemesterEnrollment::create(['student_id' => $this->student->id, 'department_id' => $this->student->department_id, 'section_id' => $this->student->section_id, 'semester' => 'Semester 1', 'academic_year' => '2025-2026', 'status' => 'completed', 'enrolled_at' => '2025-09-01']);
        StudentEnrollmentCourse::create(['student_semester_enrollment_id' => $previous->id, 'course_id' => $repeat->id, 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60]);
        SemesterResult::create(['student_semester_enrollment_id' => $previous->id, 'student_id' => $this->student->id, 'status' => 'Fail', 'published_at' => now()]);
        $this->post(route('addEnrollment'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_enrollment_courses', ['course_offering_id' => $repeatPlan['offering_ids'][0], 'registration_type' => 'repeat']);
        $this->assertDatabaseMissing('student_enrollment_courses', ['course_offering_id' => $electiveOffering->id]);
    }

    public function test_term_changes_cannot_rewrite_existing_offerings_and_closed_terms_reject_enrollment(): void
    {
        $plan = $this->offeringPlan($this->student, [$this->course]);
        $term = AcademicTerm::find($plan['academic_term_id']);
        $payload = ['academic_year_id' => $term->academic_year_id, 'name' => $term->name, 'starts_on' => $term->starts_on->toDateString(), 'ends_on' => $term->ends_on->toDateString(), 'status' => 'active'];
        $this->putJson(route('academic.terms.update', $term), array_replace($payload, ['name' => 'Changed']))->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->putJson(route('academic.terms.update', $term), array_replace($payload, ['status' => 'closed']))->assertUnprocessable()->assertJsonValidationErrors('status');
        $term->update(['status' => 'closed']);
        $this->postJson(route('addEnrollment'), $plan + ['student_id' => $this->student->id])->assertUnprocessable()->assertJsonValidationErrors('academic_term_id');
        $this->assertDatabaseCount('student_semester_enrollments', 0);
    }

    public function test_promotion_requires_next_semester_later_term_and_preserves_the_previous_offering(): void
    {
        $first = $this->offeringPlan($this->student, [$this->course], 'Semester 1');
        $this->post(route('addEnrollment'), $first + ['student_id' => $this->student->id])->assertSessionHasNoErrors();
        $enrollment = StudentSemesterEnrollment::firstOrFail();
        SemesterResult::create(['student_semester_enrollment_id' => $enrollment->id, 'student_id' => $this->student->id, 'status' => 'Pass', 'published_at' => now()]);
        $nextCourse = $this->course->replicate();
        $nextCourse->code = 'CS201';
        $nextCourse->save();
        $next = $this->offeringPlan($this->student, [$nextCourse], 'Semester 2');
        $this->postJson(route('promoteEnrollment', $enrollment), $next)->assertUnprocessable()->assertJsonValidationErrors('academic_term_id');
        $term = AcademicTerm::create(['academic_year_id' => AcademicYear::first()->id, 'name' => 'Summer 2027', 'starts_on' => '2027-06-20', 'ends_on' => '2027-07-25', 'status' => 'active']);
        $next['academic_term_id'] = $term->id;
        CourseOffering::find($next['offering_ids'][0])->update(['academic_term_id' => $term->id]);
        $curriculum = SemesterCurriculum::find($next['semester_curriculum_id']);
        $curriculum->update(['semester' => 'Semester 3']);
        $this->postJson(route('promoteEnrollment', $enrollment), $next)->assertUnprocessable()->assertJsonValidationErrors('semester_curriculum_id');
        $this->assertSame('active', $enrollment->fresh()->status);
        $curriculum->update(['semester' => 'Semester 2']);
        $this->post(route('promoteEnrollment', $enrollment), $next)->assertSessionHasNoErrors();
        $this->assertSame('promoted', $enrollment->fresh()->status);
        $this->assertSame($first['offering_ids'][0], $enrollment->courses()->first()->course_offering_id);
        $this->assertDatabaseCount('student_semester_enrollments', 2);
        $this->assertSame(1, StudentSemesterEnrollment::where('status', 'active')->count());
    }

    public function test_published_failed_result_allows_promotion_with_an_available_backlog_repeat(): void
    {
        $first = $this->offeringPlan($this->student, [$this->course], 'Semester 1');
        $this->post(route('addEnrollment'), $first + ['student_id' => $this->student->id])->assertSessionHasNoErrors();
        $enrollment = StudentSemesterEnrollment::firstOrFail();
        $enrollmentCourse = $enrollment->courses()->firstOrFail();
        $result = SemesterResult::create(['student_semester_enrollment_id' => $enrollment->id, 'student_id' => $this->student->id,
            'status' => 'Fail', 'published_at' => now(), 'sgpa' => 0, 'cgpa' => 0, 'semester_percentage' => 40]);
        $result->items()->create($enrollmentCourse->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks']) + [
            'student_enrollment_course_id' => $enrollmentCourse->id, 'obtained_marks' => 40, 'percentage' => 40,
            'grade' => 'F', 'grade_point' => 0, 'status' => 'Fail',
        ]);

        $nextCourse = $this->course->replicate();
        $nextCourse->code = 'CS201';
        $nextCourse->name = 'Data Structures';
        $nextCourse->save();
        $next = $this->offeringPlan($this->student, [$nextCourse], 'Semester 2');
        $year = AcademicYear::firstOrFail();
        $later = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Summer 2027', 'starts_on' => '2027-06-20', 'ends_on' => '2027-07-25', 'status' => 'active']);
        $nextOffering = CourseOffering::findOrFail($next['offering_ids'][0]);
        $nextOffering->update(['academic_term_id' => $later->id]);
        $repeat = CourseOffering::create($enrollmentCourse->only(['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks']) + [
            'academic_term_id' => $later->id, 'curriculum_course_id' => CourseOffering::findOrFail($first['offering_ids'][0])->curriculum_course_id,
            'department_id' => $this->student->department_id, 'section_id' => $this->student->section_id, 'semester' => 'Semester 1', 'status' => 'active',
        ]);
        $repeat->teachers()->attach($nextOffering->teachers()->pluck('teachers.id'));
        $next['academic_term_id'] = $later->id;
        $next['offering_ids'] = [$nextOffering->id, $repeat->id];

        $this->get(route('promoteEnrollmentForm', $enrollment))->assertOk()->assertSee('Promotion is allowed with backlog courses: CS101');
        $this->getJson(route('enrollment.offerings', ['student_id' => $this->student->id, 'academic_term_id' => $later->id, 'semester_curriculum_id' => $next['semester_curriculum_id']]))
            ->assertOk()->assertJsonFragment(['course_code' => 'CS101', 'type' => 'repeat']);
        $this->post(route('promoteEnrollment', $enrollment), $next)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_enrollment_courses', ['student_semester_enrollment_id' => StudentSemesterEnrollment::latest('id')->first()->id,
            'course_id' => $this->course->id, 'registration_type' => 'repeat']);
    }
}
