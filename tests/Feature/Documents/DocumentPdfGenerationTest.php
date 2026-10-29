<?php

namespace Tests\Feature\Documents;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\SemesterCurriculum;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Fee\StudentFeeInvoice;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;
    private Student $student;
    private StudentSemesterEnrollment $enrollment;
    private SemesterResult $result;
    private StudentFeeInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);

        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);

        $this->studentUser = User::create([
            'name' => 'Ali Khan',
            'email' => 'ali@student.test',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
        ]);
        $this->studentUser->assignRole('Student');

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'name' => 'Ali Khan',
            'registration_no' => 'BCS-2026-001',
            'email' => 'ali@student.test',
            'phone' => '03001234567',
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);

        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall 2026', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-31', 'status' => 'active']);

        $course = Course::create([
            'code' => 'CS101', 'name' => 'Programming Fundamentals', 'credit_hours' => 3,
            'description' => 'Core programming course',
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true,
        ]);

        $curriculum = SemesterCurriculum::create([
            'department_id' => $department->id, 'semester' => 'Semester 1', 'version' => '2026', 'status' => 'approved',
        ]);
        $curriculumCourse = $curriculum->courses()->create([
            'course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'type' => 'required',
        ]);

        $teacher = Teacher::create(['name' => 'Prof. Ahmed', 'email' => 'ahmed@test.com', 'phone' => '03009998877', 'course' => 'CS101']);
        $offering = CourseOffering::create([
            'academic_term_id' => $term->id, 'department_id' => $department->id, 'section_id' => $section->id,
            'curriculum_course_id' => $curriculumCourse->id, 'course_id' => $course->id,
            'semester' => 'Semester 1',
            'course_code' => $course->code, 'course_name' => $course->name, 'credit_hours' => 3,
            'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'status' => 'active',
        ]);
        $offering->teachers()->attach($teacher->id);

        $this->enrollment = StudentSemesterEnrollment::create([
            'student_id' => $this->student->id, 'department_id' => $department->id, 'section_id' => $section->id,
            'academic_term_id' => $term->id, 'semester_curriculum_id' => $curriculum->id,
            'academic_year' => $year->name, 'semester' => 'Semester 1', 'status' => 'active', 'enrolled_at' => today(),
        ]);

        $enrollmentCourse = $this->enrollment->courses()->create([
            'course_offering_id' => $offering->id, 'course_id' => $course->id, 'course_code' => $course->code,
            'course_name' => $course->name, 'credit_hours' => 3, 'total_marks' => 100,
            'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'registration_type' => 'required',
        ]);

        $this->result = SemesterResult::create([
            'student_semester_enrollment_id' => $this->enrollment->id,
            'student_id' => $this->student->id,
            'semester_percentage' => 85.0,
            'sgpa' => 4.00,
            'cgpa' => 4.00,
            'status' => 'Pass',
            'published_at' => now(),
            'verification_hash' => hash('sha256', 'test_transcript_hash_123'),
        ]);

        $this->result->items()->create([
            'student_enrollment_course_id' => $enrollmentCourse->id,
            'course_id' => $course->id,
            'course_code' => $course->code,
            'course_name' => $course->name,
            'credit_hours' => 3,
            'total_marks' => 100,
            'attendance_marks' => 10,
            'mid_marks' => 30,
            'final_marks' => 60,
            'obtained_marks' => 85,
            'percentage' => 85.0,
            'grade' => 'A',
            'grade_point' => 4.00,
            'status' => 'Pass',
        ]);

        $this->invoice = StudentFeeInvoice::create([
            'student_id' => $this->student->id,
            'student_semester_enrollment_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2026-001',
            'status' => 'approved',
            'credit_hours' => 3,
            'line_items' => [
                ['label' => 'Tuition Fee', 'amount' => 15000],
                ['label' => 'Examination Fee', 'amount' => 2000],
            ],
            'subtotal' => 17000,
            'total_amount' => 17000,
            'amount_paid' => 17000,
            'due_date' => today()->addDays(15),
            'generated_at' => now(),
            'approved_at' => now(),
            'verification_hash' => hash('sha256', 'test_invoice_hash_123'),
        ]);
    }

    public function test_student_can_download_official_transcript_pdf(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('student.transcript.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Official-Transcript-BCS-2026-001.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_student_can_download_fee_challan_pdf(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('student.fees.challan-pdf', $this->invoice));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Fee-Challan-INV-2026-001.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_student_cannot_download_another_students_challan(): void
    {
        $otherUser = User::create(['name' => 'Other', 'email' => 'other@student.test', 'phone' => '03009991111', 'password' => bcrypt('password')]);
        $otherUser->assignRole('Student');
        Student::create(['user_id' => $otherUser->id, 'name' => 'Other', 'email' => 'other@student.test', 'phone' => '03009991111']);

        $this->actingAs($otherUser)
            ->get(route('student.fees.challan-pdf', $this->invoice))
            ->assertForbidden();
    }

    public function test_admin_can_download_semester_result_transcript_pdf(): void
    {
        $admin = User::create(['name' => 'Admin User', 'email' => 'admin@test.com', 'phone' => '03007777777', 'password' => bcrypt('password')]);
        $admin->assignRole('Academic Admin');

        $response = $this->actingAs($admin)->get(route('result.semester.transcript-pdf', $this->result));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Transcript-BCS-2026-001-Semester-1.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_public_verification_endpoint_resolves_valid_transcript(): void
    {
        $response = $this->get(route('verify.document', [
            'type' => 'transcript',
            'hash' => $this->result->verification_hash,
        ]));

        $response->assertOk();
        $response->assertSee('OFFICIALLY VERIFIED &amp; AUTHENTIC RECORD', false);
        $response->assertSee('Ali Khan');
        $response->assertSee('BCS-2026-001');
    }

    public function test_public_verification_endpoint_resolves_valid_challan(): void
    {
        $response = $this->get(route('verify.document', [
            'type' => 'challan',
            'hash' => $this->invoice->verification_hash,
        ]));

        $response->assertOk();
        $response->assertSee('OFFICIALLY VERIFIED &amp; AUTHENTIC RECORD', false);
        $response->assertSee('INV-2026-001');
        $response->assertSee('PKR 17,000');
    }

    public function test_public_verification_endpoint_rejects_tampered_hash(): void
    {
        $response = $this->get(route('verify.document', [
            'type' => 'transcript',
            'hash' => 'fake_tampered_hash_code_999999',
        ]));

        $response->assertOk();
        $response->assertSee('DOCUMENT VERIFICATION FAILED');
    }

    public function test_admit_card_view_and_download_flow_with_clearance_gates(): void
    {
        // 1. When fee is cleared and attendance is satisfied (no sessions = 100%), student is eligible
        $this->actingAs($this->studentUser)
            ->get(route('student.admit-card.index'))
            ->assertOk()
            ->assertSee('Eligible')
            ->assertSee('Download Admit Card (PDF)');

        $pdfResponse = $this->actingAs($this->studentUser)->get(route('student.admit-card.pdf'));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Examination-Admit-Card-BCS-2026-001.pdf', $pdfResponse->headers->get('Content-Disposition'));

        // 2. When invoice is unpaid, admit card is blocked
        $this->invoice->update(['status' => 'unpaid', 'amount_paid' => 0]);
        $this->studentUser->unsetRelation('studentProfile');

        $this->actingAs($this->studentUser)
            ->get(route('student.admit-card.index'))
            ->assertOk()
            ->assertSee('Blocked')
            ->assertSee('Clear and approve your current semester fee invoice');

        $this->actingAs($this->studentUser)
            ->get(route('student.admit-card.pdf'))
            ->assertRedirect(route('student.admit-card.index'))
            ->assertSessionHasErrors('admit_card');
    }
}
