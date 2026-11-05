<?php

namespace Tests\Feature\StudentPortal;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentComponent;
use App\Models\Assessment\AssessmentMark;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Lms\CourseAnnouncement;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\StudentQuizAttempt;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnlineQuizTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;
    private Student $student;
    private User $otherStudentUser;
    private Student $otherStudent;
    private CourseOffering $offering;
    private StudentEnrollmentCourse $enrollmentCourse;
    private Quiz $quiz;
    private Assessment $assessment;

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

        $teacherUser = User::create(['name' => 'Dr. Kamran', 'email' => 'kamran@teacher.test', 'phone' => '03001234567', 'password' => bcrypt('password')]);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'name' => 'Dr. Kamran', 'email' => 'kamran@teacher.test', 'phone' => '03001234567']);
        $teacherUser->assignRole('Teacher');

        $this->studentUser = User::create(['name' => 'Hamza Ali', 'email' => 'hamza@student.test', 'phone' => '03005551122', 'password' => bcrypt('password')]);
        $this->student = Student::create(['user_id' => $this->studentUser->id, 'name' => 'Hamza Ali', 'email' => 'hamza@student.test', 'phone' => '03005551122', 'department_id' => $dept->id, 'section_id' => $sec->id, 'semester' => 'Semester 1']);
        $this->studentUser->assignRole('Student');

        $this->otherStudentUser = User::create(['name' => 'Sara Khan', 'email' => 'sara@student.test', 'phone' => '03009998877', 'password' => bcrypt('password')]);
        $this->otherStudent = Student::create(['user_id' => $this->otherStudentUser->id, 'name' => 'Sara Khan', 'email' => 'sara@student.test', 'phone' => '03009998877', 'department_id' => $dept->id, 'section_id' => $sec->id, 'semester' => 'Semester 1']);
        $this->otherStudentUser->assignRole('Student');

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
        $this->offering->teachers()->attach($teacher->id);

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

        // Create linked assessment component for auto-grading test
        $component = AssessmentComponent::create([
            'course_offering_id' => $this->offering->id,
            'code' => 'quiz',
            'allocation' => 15.00,
        ]);

        $this->assessment = Assessment::create([
            'assessment_component_id' => $component->id,
            'title' => 'Quiz 1 (Online)',
            'held_on' => now()->toDateString(),
            'maximum' => 10.00,
            'weight' => 5.00,
        ]);

        // Create Quiz with questions
        $this->quiz = Quiz::create([
            'course_offering_id' => $this->offering->id,
            'assessment_id' => $this->assessment->id,
            'title' => 'Midterm Review Quiz',
            'description' => 'Test your basics.',
            'duration_minutes' => 10,
            'total_points' => 10.00,
            'is_published' => true,
        ]);

        QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'What is 5 + 5?',
            'option_a' => '8',
            'option_b' => '10',
            'option_c' => '12',
            'option_d' => '15',
            'correct_option' => 'b',
            'points' => 5.00,
        ]);

        QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Which keyword defines a constant in PHP?',
            'option_a' => 'const',
            'option_b' => 'var',
            'option_c' => 'let',
            'option_d' => 'val',
            'correct_option' => 'a',
            'points' => 5.00,
        ]);
    }

    public function test_enrolled_student_can_view_course_lms_materials_and_announcements(): void
    {
        CourseAnnouncement::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'Welcome to Class',
            'content' => 'First lecture slides posted.',
            'priority' => 'normal',
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms', $this->enrollmentCourse));

        $response->assertOk();
        $response->assertSee('Programming Fundamentals');
        $response->assertSee('Welcome to Class');
        $response->assertSee('Midterm Review Quiz');
    }

    public function test_enrolled_student_can_download_published_material(): void
    {
        $file = UploadedFile::fake()->create('lecture1.pdf', 300, 'application/pdf');
        $path = $file->store('course-materials', 'local');

        $material = CourseMaterial::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'Lecture 1 PDF',
            'week_number' => 1,
            'file_path' => $path,
            'file_name' => 'lecture1.pdf',
            'file_type' => 'pdf',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.materials.download', [$this->enrollmentCourse, $material]));

        $response->assertOk();
    }

    public function test_unenrolled_student_cannot_access_course_lms_or_download_material(): void
    {
        $file = UploadedFile::fake()->create('secret.pdf', 200, 'application/pdf');
        $path = $file->store('course-materials', 'local');

        $material = CourseMaterial::create([
            'course_offering_id' => $this->offering->id,
            'title' => 'Secret Material',
            'file_path' => $path,
            'file_name' => 'secret.pdf',
            'is_published' => true,
        ]);

        // Other student is not enrolled in this enrollment course
        $response = $this->actingAs($this->otherStudentUser)
            ->get(route('student.courses.lms', $this->enrollmentCourse));

        $response->assertForbidden();

        $downloadResponse = $this->actingAs($this->otherStudentUser)
            ->get(route('student.courses.lms.materials.download', [$this->enrollmentCourse, $material]));

        $downloadResponse->assertForbidden();
    }

    public function test_student_can_start_timed_quiz_attempt(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.quizzes.take', [$this->enrollmentCourse, $this->quiz]));

        $response->assertOk();
        $response->assertSee('Midterm Review Quiz');
        $response->assertSee('What is 5 + 5?');

        $this->assertDatabaseHas('student_quiz_attempts', [
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_student_can_submit_quiz_and_receive_auto_graded_score(): void
    {
        // 1. Start quiz
        $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.quizzes.take', [$this->enrollmentCourse, $this->quiz]));

        $q1 = $this->quiz->questions->first();
        $q2 = $this->quiz->questions->last();

        // 2. Submit: Q1 correct ('b'), Q2 wrong ('b' instead of 'a')
        $submitResponse = $this->actingAs($this->studentUser)
            ->post(route('student.courses.lms.quizzes.submit', [$this->enrollmentCourse, $this->quiz]), [
                'answers' => [
                    $q1->id => 'b',
                    $q2->id => 'b',
                ],
            ]);

        $submitResponse->assertRedirect(route('student.courses.lms.quizzes.result', [$this->enrollmentCourse, $this->quiz]));

        // Q1 is 5 points, Q2 is 0 -> Total obtained = 5.00 out of 10.00
        $this->assertDatabaseHas('student_quiz_attempts', [
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'score_obtained' => 5.00,
            'status' => 'submitted',
        ]);
    }

    public function test_quiz_score_auto_syncs_to_assessment_marks_gradebook(): void
    {
        // Start quiz
        $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.quizzes.take', [$this->enrollmentCourse, $this->quiz]));

        $q1 = $this->quiz->questions->first();
        $q2 = $this->quiz->questions->last();

        // Submit: Both correct ('b' and 'a') -> 10.00 / 10.00
        $this->actingAs($this->studentUser)
            ->post(route('student.courses.lms.quizzes.submit', [$this->enrollmentCourse, $this->quiz]), [
                'answers' => [
                    $q1->id => 'b',
                    $q2->id => 'a',
                ],
            ]);

        // Verify AssessmentMark was created automatically for this assessment!
        $this->assertDatabaseHas('assessment_marks', [
            'assessment_id' => $this->assessment->id,
            'student_enrollment_course_id' => $this->enrollmentCourse->id,
            'obtained' => 10.00,
        ]);
    }

    public function test_student_can_view_quiz_result_breakdown(): void
    {
        StudentQuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'started_at' => now()->subMinutes(8),
            'completed_at' => now(),
            'score_obtained' => 10.00,
            'total_points' => 10.00,
            'status' => 'submitted',
            'answers' => [1 => 'b', 2 => 'a'],
        ]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.quizzes.result', [$this->enrollmentCourse, $this->quiz]));

        $response->assertOk();
        $response->assertSee('Quiz Passed');
        $response->assertSee('10');
    }

    public function test_unpublished_or_closed_quiz_cannot_be_started(): void
    {
        $this->quiz->update(['is_published' => false]);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.courses.lms.quizzes.take', [$this->enrollmentCourse, $this->quiz]));

        $response->assertSessionHasErrors('quiz');
    }
}
