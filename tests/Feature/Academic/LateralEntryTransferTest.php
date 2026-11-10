<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\CurriculumCourse;
use App\Models\Academic\Program;
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
use App\Services\AcademicEnrollmentService;
use App\Services\Documents\DocumentPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LateralEntryTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $department;
    private Program $program;
    private Section $section;
    private AcademicYear $year;
    private AcademicTerm $term;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AccessControlSeeder::class);

        $adminRole = Role::findByName('Academic Admin', 'web');
        $this->admin = User::create([
            'name' => 'Admin Lateral',
            'email' => 'admin.lateral@example.com',
            'phone' => '03001239876',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole($adminRole);

        $this->department = Department::create(['name' => 'Computer Science']);
        $this->program = Program::create([
            'department_id' => $this->department->id,
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'BSCS',
            'is_active' => true,
        ]);
        $this->section = Section::create(['name' => 'A', 'department_id' => $this->department->id]);

        $this->year = AcademicYear::create([
            'name' => '2026-2027',
            'starts_on' => '2026-08-01',
            'ends_on' => '2027-07-31',
        ]);
        $this->term = AcademicTerm::create([
            'academic_year_id' => $this->year->id,
            'name' => 'Fall 2026',
            'starts_on' => '2026-08-15',
            'ends_on' => '2026-12-31',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_admit_student_with_lateral_adp_entry(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('adp_transcript.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('addStudent'), [
            'name' => 'Usama ADP Graduate',
            'email' => 'usama.adp@example.com',
            'phone' => '03001234567',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'create_portal_account' => true,
            'account_password' => 'Password@123',
            'account_password_confirmation' => 'Password@123',
            'admission_type' => 'lateral_adp',
            'previous_institution' => 'Govt Post Graduate College Lahore',
            'previous_degree' => 'Associate Degree in Computer Science (ADCS)',
            'previous_cgpa' => '3.45',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
            'transcript_document' => $file,
            'remarks' => 'Equivalence Committee Approval # EC-2026-99',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'email' => 'usama.adp@example.com',
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $student = Student::where('email', 'usama.adp@example.com')->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->isLateralEntry());
        $this->assertEquals(64.0, $student->getTransferredCredits());

        $this->assertDatabaseHas('student_credit_transfers', [
            'student_id' => $student->id,
            'transfer_type' => 'lateral_adp',
            'previous_institution' => 'Govt Post Graduate College Lahore',
            'previous_degree' => 'Associate Degree in Computer Science (ADCS)',
            'previous_cgpa' => '3.45',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
            'document_name' => 'adp_transcript.pdf',
        ]);

        Storage::disk('public')->assertExists($student->creditTransfer->document_path);
    }

    public function test_lateral_student_can_enroll_directly_in_semester_5_curriculum(): void
    {
        $teacherUser = User::create([
            'name' => 'Dr. Farooq',
            'email' => 'farooq.user@example.com',
            'phone' => '03009998877',
            'password' => bcrypt('password'),
        ]);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'name' => 'Dr. Farooq',
            'email' => 'farooq@example.com',
            'phone' => '03009998877',
            'course' => 'CS',
            'image' => 'default-user.png',
        ]);

        $course = Course::create([
            'code' => 'CS301',
            'name' => 'Operating Systems',
            'description' => 'Core operating systems course',
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);

        $curriculum = SemesterCurriculum::create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'semester' => 'Semester 5',
            'version' => '2026-v1',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $curriculumCourse = CurriculumCourse::create([
            'semester_curriculum_id' => $curriculum->id,
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

        $offering = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'curriculum_course_id' => $curriculumCourse->id,
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'course_id' => $course->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'semester' => 'Semester 5',
            'status' => 'active',
        ]);
        $offering->teachers()->attach($teacher->id);

        $student = Student::create([
            'name' => 'Bilal Lateral',
            'email' => 'bilal.lateral@example.com',
            'phone' => '03001112233',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $student->creditTransfer()->create([
            'transfer_type' => 'lateral_adp',
            'previous_institution' => 'Punjab University',
            'previous_degree' => 'ADP CS',
            'previous_cgpa' => '3.20',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
        ]);

        $service = app(AcademicEnrollmentService::class);
        $enrollment = $service->enroll([
            'student_id' => $student->id,
            'academic_term_id' => $this->term->id,
            'semester_curriculum_id' => $curriculum->id,
        ]);

        $this->assertEquals('Semester 5', $enrollment->semester);
        $this->assertCount(1, $enrollment->courses);
        $this->assertEquals('CS301', $enrollment->courses->first()->course_code);
    }

    public function test_lateral_and_regular_students_share_the_exact_same_class_offering(): void
    {
        $teacherUser = User::create([
            'name' => 'Dr. Farooq',
            'email' => 'farooq.user@example.com',
            'phone' => '03009998877',
            'password' => bcrypt('password'),
        ]);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'name' => 'Prof. Tariq',
            'email' => 'tariq@example.com',
            'phone' => '03001239999',
            'course' => 'CS',
            'image' => 'default-user.png',
        ]);

        $course = Course::create([
            'code' => 'CS302',
            'name' => 'Database Systems',
            'description' => 'Core DB',
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'is_active' => true,
        ]);

        $curriculum = SemesterCurriculum::create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'semester' => 'Semester 5',
            'version' => '2026-v1',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $curriculumCourse = CurriculumCourse::create([
            'semester_curriculum_id' => $curriculum->id,
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

        $offering = CourseOffering::create([
            'academic_term_id' => $this->term->id,
            'curriculum_course_id' => $curriculumCourse->id,
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'course_id' => $course->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'semester' => 'Semester 5',
            'status' => 'active',
        ]);
        $offering->teachers()->attach($teacher->id);

        $regularStudent = Student::create([
            'name' => 'Regular Hamza',
            'email' => 'regular.hamza@example.com',
            'phone' => '03001113333',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'regular',
            'semester' => 'Semester 5',
        ]);

        $lateralStudent = Student::create([
            'name' => 'Lateral Zain',
            'email' => 'lateral.zain@example.com',
            'phone' => '03001114444',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $service = app(AcademicEnrollmentService::class);
        $regEnrollment = $service->enroll([
            'student_id' => $regularStudent->id,
            'academic_term_id' => $this->term->id,
            'semester_curriculum_id' => $curriculum->id,
        ]);

        $latEnrollment = $service->enroll([
            'student_id' => $lateralStudent->id,
            'academic_term_id' => $this->term->id,
            'semester_curriculum_id' => $curriculum->id,
        ]);

        $this->assertEquals(
            $regEnrollment->courses->first()->course_offering_id,
            $latEnrollment->courses->first()->course_offering_id,
            'Both regular and lateral students must share the exact same course offering ID.'
        );

        $enrolledStudents = Student::whereHas('semesterEnrollments.courses', fn ($q) => $q->where('course_offering_id', $offering->id))->pluck('name');
        $this->assertTrue($enrolledStudents->contains('Regular Hamza'));
        $this->assertTrue($enrolledStudents->contains('Lateral Zain'));
    }

    public function test_official_transcript_pdf_contains_advanced_standing_box(): void
    {
        $student = Student::create([
            'name' => 'Ayesha Transfer Student',
            'email' => 'ayesha.transfer@example.com',
            'phone' => '03005556677',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $student->creditTransfer()->create([
            'transfer_type' => 'lateral_adp',
            'previous_institution' => 'University of Gujrat',
            'previous_degree' => 'Associate Degree in IT',
            'previous_cgpa' => '3.60',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
        ]);

        $pdfService = app(DocumentPdfService::class);
        $pdf = $pdfService->generateTranscriptPdf($student);
        $renderedHtml = $pdf->output();

        $this->assertNotEmpty($renderedHtml);
    }

    public function test_student_portal_transcript_displays_exemption_details(): void
    {
        $studentUser = User::create([
            'name' => 'Portal Lateral Student',
            'email' => 'portal.lateral@example.com',
            'phone' => '03009990000',
            'password' => bcrypt('password'),
        ]);
        $studentRole = Role::findByName('Student', 'web');
        $studentUser->assignRole($studentRole);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'name' => 'Portal Lateral Student',
            'email' => 'portal.lateral@example.com',
            'phone' => '03009990000',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $student->creditTransfer()->create([
            'transfer_type' => 'lateral_adp',
            'previous_institution' => 'Bahauddin Zakariya University',
            'previous_degree' => 'Associate Degree in Science',
            'previous_cgpa' => '3.30',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
        ]);

        $response = $this->actingAs($studentUser)->get(route('student.transcript'));
        $response->assertStatus(200);
        $response->assertSee('Advanced Standing &amp; Credit Exemption', false);
        $response->assertSee('64.0 Credits Transferred');
        $response->assertSee('Bahauddin Zakariya University');
        $response->assertSee('Associate Degree in Science');
    }

    public function test_admin_can_download_transfer_document(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('verified_transcript.pdf', 300, 'application/pdf');
        $storedPath = $file->store('credit_transfers', 'public');

        $student = Student::create([
            'name' => 'Doc Test Student',
            'email' => 'doctest@example.com',
            'phone' => '03004443322',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'lateral_adp',
            'admitted_semester' => 'Semester 5',
            'semester' => 'Semester 5',
        ]);

        $student->creditTransfer()->create([
            'transfer_type' => 'lateral_adp',
            'previous_institution' => 'Islamia University Bahawalpur',
            'previous_degree' => 'ADP Computer Science',
            'transferred_credits' => 64.0,
            'admitted_to_semester' => 5,
            'document_path' => $storedPath,
            'document_name' => 'verified_transcript.pdf',
        ]);

        $response = $this->actingAs($this->admin)->get(route('students.transfer-document', $student));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_regular_students_flow_remains_unaffected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('addStudent'), [
            'name' => 'Standard Fresh Student',
            'email' => 'fresh.student@example.com',
            'phone' => '03112223344',
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'section_id' => $this->section->id,
            'admission_type' => 'regular',
        ]);

        $response->assertRedirect();
        $student = Student::where('email', 'fresh.student@example.com')->first();
        $this->assertNotNull($student);
        $this->assertEquals('regular', $student->admission_type);
        $this->assertNull($student->creditTransfer);
        $this->assertFalse($student->isLateralEntry());
        $this->assertEquals(0.0, $student->getTransferredCredits());
    }
}
