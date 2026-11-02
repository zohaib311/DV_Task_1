<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Timetable\Room;
use App\Models\Timetable\TimeSlot;
use App\Models\Timetable\TimetableAllocation;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private AcademicTerm $term;
    private Department $department;
    private Section $section;
    private CourseOffering $offering1;
    private CourseOffering $offering2;
    private Room $room1;
    private Room $room2;
    private TimeSlot $slot1;
    private TimeSlot $breakSlot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);

        $this->admin = User::create([
            'name' => 'Timetable Admin',
            'email' => 'admin@timetable.test',
            'phone' => '03009990001',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('Academic Admin');

        $this->department = Department::create(['name' => 'Computer Science']);
        $this->section = Section::create(['name' => 'A', 'department_id' => $this->department->id]);

        $year = AcademicYear::create([
            'name' => '2026-2027',
            'starts_on' => '2026-08-01',
            'ends_on' => '2027-07-31',
        ]);
        $this->term = AcademicTerm::create([
            'academic_year_id' => $year->id,
            'name' => 'Fall 2026',
            'starts_on' => '2026-09-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
        ]);

        $course1 = Course::create([
            'code' => 'CS101',
            'name' => 'Programming Fundamentals',
            'credit_hours' => 3,
            'description' => 'Core programming course',
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);
        $course2 = Course::create([
            'code' => 'CS102',
            'name' => 'Data Structures',
            'credit_hours' => 3,
            'description' => 'Core data structures',
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);

        $curriculum = SemesterCurriculum::create([
            'department_id' => $this->department->id,
            'semester' => 'Semester 1',
            'version' => '2026',
            'status' => 'approved',
        ]);
        $curriculumCourse1 = $curriculum->courses()->create([
            'course_id' => $course1->id,
            'course_code' => $course1->code,
            'course_name' => $course1->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'type' => 'required',
        ]);
        $curriculumCourse2 = $curriculum->courses()->create([
            'course_id' => $course2->id,
            'course_code' => $course2->code,
            'course_name' => $course2->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'type' => 'required',
        ]);

        $this->teacherUser = User::create([
            'name' => 'Dr. Kamran',
            'email' => 'kamran@teacher.test',
            'phone' => '03009990002',
            'password' => bcrypt('password'),
        ]);
        $this->teacherUser->assignRole('Teacher');

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'name' => 'Dr. Kamran',
            'email' => 'kamran@teacher.test',
            'phone' => '03009990002',
            'course' => 'CS101',
        ]);

        $this->offering1 = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
            'curriculum_course_id' => $curriculumCourse1->id,
            'course_id' => $course1->id,
            'semester' => 'Semester 1',
            'course_code' => $course1->code,
            'course_name' => $course1->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'status' => 'active',
        ]);
        $this->offering1->teachers()->attach($this->teacher->id);

        $this->offering2 = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
            'curriculum_course_id' => $curriculumCourse2->id,
            'course_id' => $course2->id,
            'semester' => 'Semester 1',
            'course_code' => $course2->code,
            'course_name' => $course2->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'status' => 'active',
        ]);
        $this->offering2->teachers()->attach($this->teacher->id);

        $this->studentUser = User::create([
            'name' => 'Hamza Tariq',
            'email' => 'hamza@student.test',
            'phone' => '03009990003',
            'password' => bcrypt('password'),
        ]);
        $this->studentUser->assignRole('Student');

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'name' => 'Hamza Tariq',
            'registration_no' => 'BCS-2026-100',
            'email' => 'hamza@student.test',
            'phone' => '03009990003',
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
        ]);

        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $this->student->id,
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
            'academic_term_id' => $this->term->id,
            'semester_curriculum_id' => $curriculum->id,
            'academic_year' => $year->name,
            'semester' => 'Semester 1',
            'status' => 'active',
            'enrolled_at' => today(),
        ]);
        $enrollment->courses()->create([
            'course_offering_id' => $this->offering1->id,
            'course_id' => $course1->id,
            'course_code' => $course1->code,
            'course_name' => $course1->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'registration_type' => 'required',
        ]);

        $this->room1 = Room::create([
            'name' => 'Main Computing Lab',
            'code' => 'CS-LAB-01',
            'building' => 'Block C',
            'type' => 'computer_lab',
            'capacity' => 45,
            'is_active' => true,
        ]);
        $this->room2 = Room::create([
            'name' => 'Lecture Hall 101',
            'code' => 'LH-101',
            'building' => 'Block A',
            'type' => 'lecture_hall',
            'capacity' => 60,
            'is_active' => true,
        ]);

        $this->slot1 = TimeSlot::create([
            'academic_term_id' => $this->term->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:30:00',
            'end_time' => '10:00:00',
            'slot_type' => 'regular',
        ]);

        $this->breakSlot = TimeSlot::create([
            'academic_term_id' => $this->term->id,
            'day_of_week' => 'Monday',
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'slot_type' => 'break',
        ]);
    }

    public function test_admin_can_create_room_and_time_slot(): void
    {
        $this->actingAs($this->admin)
            ->post(route('academic.timetable.rooms.store'), [
                'name' => 'Science Lab 2',
                'code' => 'SCI-LAB-02',
                'building' => 'Block B',
                'type' => 'science_lab',
                'capacity' => 35,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', ['code' => 'SCI-LAB-02', 'capacity' => 35]);

        $this->actingAs($this->admin)
            ->post(route('academic.timetable.time-slots.store'), [
                'academic_term_id' => $this->term->id,
                'day_of_week' => 'Tuesday',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'slot_type' => 'regular',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('time_slots', [
            'academic_term_id' => $this->term->id,
            'day_of_week' => 'Tuesday',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
    }

    public function test_admin_can_allocate_valid_class_slot(): void
    {
        $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $this->offering1->id,
                'room_id' => $this->room1->id,
                'time_slot_id' => $this->slot1->id,
                'teacher_id' => $this->teacher->id,
                'notes' => 'Bring laptop for lab session',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('timetable_allocations', [
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_room_clash_is_rejected(): void
    {
        // 1. Allocate offering1 in room1 at slot1
        TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        // 2. Create another section & offering with a different teacher
        $sectionB = Section::create(['name' => 'B', 'department_id' => $this->department->id]);
        $teacher2User = User::create(['name' => 'Prof. Bilal', 'email' => 'bilal@teacher.test', 'phone' => '03009990005', 'password' => bcrypt('password')]);
        $teacher2 = Teacher::create(['user_id' => $teacher2User->id, 'name' => 'Prof. Bilal', 'email' => 'bilal@teacher.test', 'phone' => '03009990005']);

        $offeringSectionB = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'department_id' => $this->department->id,
            'section_id' => $sectionB->id,
            'curriculum_course_id' => $this->offering1->curriculum_course_id,
            'course_id' => $this->offering1->course_id,
            'semester' => 'Semester 1',
            'course_code' => 'CS101',
            'course_name' => 'Programming Fundamentals',
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'status' => 'active',
        ]);
        $offeringSectionB->teachers()->attach($teacher2->id);

        // 3. Try to book room1 at slot1 for sectionB
        $response = $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $offeringSectionB->id,
                'room_id' => $this->room1->id,
                'time_slot_id' => $this->slot1->id,
                'teacher_id' => $teacher2->id,
            ]);

        $response->assertSessionHasErrors('room_id');
    }

    public function test_teacher_clash_is_rejected(): void
    {
        // 1. Allocate offering1 with teacher in room1 at slot1
        TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        // 2. Create a separate section offering taught by the SAME teacher
        $sectionC = Section::create(['name' => 'C', 'department_id' => $this->department->id]);
        $offeringSectionC = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'department_id' => $this->department->id,
            'section_id' => $sectionC->id,
            'curriculum_course_id' => $this->offering1->curriculum_course_id,
            'course_id' => $this->offering1->course_id,
            'semester' => 'Semester 1',
            'course_code' => 'CS101',
            'course_name' => 'Programming Fundamentals',
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'status' => 'active',
        ]);
        $offeringSectionC->teachers()->attach($this->teacher->id);

        // 3. Try assigning the teacher to room2 at the same slot1
        $response = $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $offeringSectionC->id,
                'room_id' => $this->room2->id,
                'time_slot_id' => $this->slot1->id,
                'teacher_id' => $this->teacher->id,
            ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_cohort_section_clash_is_rejected(): void
    {
        // 1. Allocate offering1 (Section A) with teacher in room1 at slot1
        TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        // 2. Assign another teacher to offering2 (also Section A)
        $teacher2User = User::create(['name' => 'Dr. Zeeshan', 'email' => 'zeeshan@teacher.test', 'phone' => '03009990006', 'password' => bcrypt('password')]);
        $teacher2 = Teacher::create(['user_id' => $teacher2User->id, 'name' => 'Dr. Zeeshan', 'email' => 'zeeshan@teacher.test', 'phone' => '03009990006']);
        $this->offering2->teachers()->attach($teacher2->id);

        // 3. Try scheduling offering2 for Section A in room2 at slot1 (students cannot be in two places at once!)
        $response = $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $this->offering2->id,
                'room_id' => $this->room2->id,
                'time_slot_id' => $this->slot1->id,
                'teacher_id' => $teacher2->id,
            ]);

        $response->assertSessionHasErrors('course_offering_id');
    }

    public function test_break_slot_cannot_be_allocated(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $this->offering1->id,
                'room_id' => $this->room1->id,
                'time_slot_id' => $this->breakSlot->id,
                'teacher_id' => $this->teacher->id,
            ]);

        $response->assertSessionHasErrors('time_slot_id');
    }

    public function test_unassigned_teacher_rejected_for_offering(): void
    {
        $otherTeacherUser = User::create(['name' => 'Dr. External', 'email' => 'ext@teacher.test', 'phone' => '03009990009', 'password' => bcrypt('password')]);
        $otherTeacher = Teacher::create(['user_id' => $otherTeacherUser->id, 'name' => 'Dr. External', 'email' => 'ext@teacher.test', 'phone' => '03009990009']);

        $response = $this->actingAs($this->admin)
            ->post(route('academic.timetable.allocations.store'), [
                'course_offering_id' => $this->offering1->id,
                'room_id' => $this->room1->id,
                'time_slot_id' => $this->slot1->id,
                'teacher_id' => $otherTeacher->id,
            ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_admin_can_view_timetable_management_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('academic.timetable.index'));

        $response->assertOk();
        $response->assertSee('Timetable');
        $response->assertSee('Academic Scheduling');
        $response->assertSee('Section A');
    }

    public function test_teacher_can_view_assigned_weekly_schedule(): void
    {
        TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teaching.timetable.index'));

        $response->assertOk();
        $response->assertSee('Programming Fundamentals');
        $response->assertSee('CS-LAB-01');
        $response->assertSee('Mark Attendance');
    }

    public function test_student_can_view_enrolled_courses_weekly_timetable(): void
    {
        TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.timetable.index'));

        $response->assertOk();
        $response->assertSee('Programming Fundamentals');
        $response->assertSee('CS-LAB-01');
        $response->assertSee('Dr. Kamran');
    }

    public function test_admin_can_remove_allocation(): void
    {
        $allocation = TimetableAllocation::create([
            'course_offering_id' => $this->offering1->id,
            'room_id' => $this->room1->id,
            'time_slot_id' => $this->slot1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('academic.timetable.allocations.destroy', $allocation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('timetable_allocations', ['id' => $allocation->id]);
    }

    public function test_unauthorized_users_cannot_manage_timetable(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('academic.timetable.index'))
            ->assertForbidden();

        $this->actingAs($this->teacherUser)
            ->get(route('academic.timetable.index'))
            ->assertForbidden();
    }
}
