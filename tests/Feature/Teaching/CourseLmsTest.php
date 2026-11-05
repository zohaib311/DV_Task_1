<?php

namespace Tests\Feature\Teaching;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Lms\CourseAnnouncement;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseLmsTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $studentUser;
    private Student $student;
    private CourseOffering $offering;
    private StudentEnrollmentCourse $enrollmentCourse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
        Storage::fake('local');

        $dept = Department::create(['name' => 'Computer Science']);
        $sec = Section::create(['name' => 'A', 'department_id' => $dept->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-06-30']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall 2026', 'starts_on' => '2026-08-15', 'ends_on' => '2026-12-20', 'status' => 'active']);
        $course = Course::create([
            'code' => 'CS101',
            'name' => 'Programming Fundamentals',
            'credit_hours' => 3,
            'description' => 'Core programming course',
            'total_marks' => 100,
            'department_id' => $dept->id,
            'attendance_weight' => 10,
            'mid_weight' => 30,
            'final_weight' => 50,
            'practical_weight' => 10,
        ]);

        $curriculum = \App\Models\Academic\SemesterCurriculum::create([
            'department_id' => $dept->id,
            'semester' => 'Semester 1',
            'version' => '2026',
            'status' => 'approved',
        ]);
        $curriculumCourse = $curriculum->courses()->create([
            'course_id' => $course->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'type' => 'required',
        ]);

        $this->teacherUser = User::create(['name' => 'Dr. Kamran', 'email' => 'kamran@teacher.test', 'phone' => '03001234567', 'password' => bcrypt('password')]);
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'name' => 'Dr. Kamran', 'email' => 'kamran@teacher.test', 'phone' => '03001234567']);
        $this->teacherUser->assignRole('Teacher');

        $this->otherTeacherUser = User::create(['name' => 'Dr. Sara', 'email' => 'sara@teacher.test', 'phone' => '03009876543', 'password' => bcrypt('password')]);
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'name' => 'Dr. Sara', 'email' => 'sara@teacher.test', 'phone' => '03009876543']);
        $this->otherTeacherUser->assignRole('Teacher');

        $this->studentUser = User::create(['name' => 'Hamza Ali', 'email' => 'hamza@student.test', 'phone' => '03005551122', 'password' => bcrypt('password')]);
        $this->student = Student::create(['user_id' => $this->studentUser->id, 'name' => 'Hamza Ali', 'email' => 'hamza@student.test', 'phone' => '03005551122', 'department_id' => $dept->id, 'section_id' => $sec->id, 'semester' => 'Semester 1']);
        $this->studentUser->assignRole('Student');

        $this->offering = CourseOffering::create([
            'academic_term_id' => $term->id,
            'department_id' => $dept->id,
            'section_id' => $sec->id,
            'curriculum_course_id' => $curriculumCourse->id,
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'semester' => 'Semester 1',
            'status' => 'active',
        ]);
        $this->offering->teachers()->attach($this->teacher->id);

        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $this->student->id,
            'department_id' => $dept->id,
            'section_id' => $sec->id,
            'academic_term_id' => $term->id,
            'semester' => 'Semester 1',
            'academic_year' => '2026-2027',
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $this->enrollmentCourse = StudentEnrollmentCourse::create([
            'student_semester_enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'course_offering_id' => $this->offering->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'registration_type' => 'required',
        ]);
    }

    public function test_teacher_can_view_lms_hub_for_assigned_offering(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teaching.offerings.lms.index', $this->offering));

        $response->assertOk();
        $response->assertSee('LMS & Class Materials');
        $response->assertSee('Programming Fundamentals');
    }

    public function test_teacher_cannot_access_lms_for_unassigned_offering(): void
    {
        $response = $this->actingAs($this->otherTeacherUser)
            ->get(route('teaching.offerings.lms.index', $this->offering));

        $response->assertNotFound();
    }

    public function test_teacher_can_upload_and_download_course_material(): void
    {
        $file = UploadedFile::fake()->create('week1_lecture.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teaching.offerings.lms.materials.store', $this->offering), [
                'title' => 'Week 1 - Intro to Programming Slides',
                'week_number' => 1,
                'description' => 'Read slides before lecture.',
                'file' => $file,
                'is_published' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_materials', [
            'course_offering_id' => $this->offering->id,
            'title' => 'Week 1 - Intro to Programming Slides',
            'week_number' => 1,
            'file_name' => 'week1_lecture.pdf',
            'file_type' => 'pdf',
        ]);

        $material = CourseMaterial::first();
        Storage::disk('local')->assertExists($material->file_path);

        $downloadResponse = $this->actingAs($this->teacherUser)
            ->get(route('teaching.offerings.lms.materials.download', [$this->offering, $material]));

        $downloadResponse->assertOk();
    }

    public function test_teacher_can_delete_course_material(): void
    {
        $file = UploadedFile::fake()->create('to_delete.pdf', 100, 'application/pdf');
        $path = $file->store('course-materials', 'local');

        $material = CourseMaterial::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'Temporary Material',
            'file_path' => $path,
            'file_name' => 'to_delete.pdf',
            'file_type' => 'pdf',
            'is_published' => true,
        ]);

        $this->actingAs($this->teacherUser)
            ->delete(route('teaching.offerings.lms.materials.destroy', [$this->offering, $material]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('course_materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_teacher_can_post_announcement_and_notify_enrolled_students(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teaching.offerings.lms.announcements.store', $this->offering), [
                'title' => 'Class Venue Changed',
                'content' => 'Please note that Monday lecture is moved to Software Lab 1.',
                'priority' => 'urgent',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_announcements', [
            'course_offering_id' => $this->offering->id,
            'title' => 'Class Venue Changed',
            'priority' => 'urgent',
        ]);

        Notification::assertSentTo($this->studentUser, AcademicUpdateNotification::class);
    }

    public function test_teacher_can_create_mcq_quiz_with_questions(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->post(route('teaching.offerings.lms.quizzes.store', $this->offering), [
                'title' => 'Quiz 1: Programming Basics',
                'description' => 'Test your knowledge on data types and variables.',
                'duration_minutes' => 10,
                'is_published' => 1,
                'questions' => [
                    [
                        'question_text' => 'What is the size of integer in standard 32-bit architecture?',
                        'option_a' => '2 bytes',
                        'option_b' => '4 bytes',
                        'option_c' => '8 bytes',
                        'option_d' => '1 byte',
                        'correct_option' => 'b',
                        'points' => 2.0,
                    ],
                    [
                        'question_text' => 'Which of the following is not a primitive type?',
                        'option_a' => 'int',
                        'option_b' => 'char',
                        'option_c' => 'String',
                        'option_d' => 'float',
                        'correct_option' => 'c',
                        'points' => 3.0,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_offering_id' => $this->offering->id,
            'title' => 'Quiz 1: Programming Basics',
            'duration_minutes' => 10,
            'total_points' => 5.0,
        ]);

        $this->assertDatabaseCount('quiz_questions', 2);
    }

    public function test_teacher_can_view_quiz_details(): void
    {
        $quiz = Quiz::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'Sample Quiz',
            'duration_minutes' => 15,
            'total_points' => 10,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teaching.offerings.lms.quizzes.show', [$this->offering, $quiz]));

        $response->assertOk();
        $response->assertSee('Sample Quiz');
        $response->assertSee('15');
    }

    public function test_teacher_can_delete_quiz(): void
    {
        $quiz = Quiz::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'To Be Deleted',
            'duration_minutes' => 10,
            'total_points' => 5,
        ]);

        $this->actingAs($this->teacherUser)
            ->delete(route('teaching.offerings.lms.quizzes.destroy', [$this->offering, $quiz]))
            ->assertRedirect();

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }
}
