<?php

namespace Tests\Feature;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Program;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Fee\FeeStructure;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Section\Section;
use App\Models\Student;
use App\Services\Fee\FeeInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_invoice_uses_registered_credit_hours_and_freezes_after_submission(): void
    {
        $department = Department::create(['name' => 'Computing']);
        $section = Section::create(['name' => 'A', 'department_id' => $department->id]);
        $program = Program::create(['department_id' => $department->id, 'name' => 'BS Information Technology', 'code' => 'BSIT', 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]);
        $student = Student::create(['name' => 'Fee Student', 'email' => 'fee@example.test', 'phone' => '03001234567', 'department_id' => $department->id, 'program_id' => $program->id, 'section_id' => $section->id, 'semester' => 'Semester 1', 'course_ids' => [], 'image' => 'default-user.png']);
        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall', 'starts_on' => '2026-08-01', 'ends_on' => '2026-12-31', 'status' => 'active']);
        $course = Course::create(['code' => 'IT101', 'name' => 'Programming', 'description' => 'Fundamentals', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        $enrollment = StudentSemesterEnrollment::create(['student_id' => $student->id, 'department_id' => $department->id, 'program_id' => $program->id, 'section_id' => $section->id, 'academic_term_id' => $term->id, 'semester' => 'Semester 1', 'academic_year' => '2026-2027', 'status' => 'active', 'enrolled_at' => today()]);
        $enrollment->courses()->create(['course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name, 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60]);
        $enrollment->load(['courses', 'program']);
        $structure = FeeStructure::create([
            'program_id' => $program->id, 'academic_term_id' => $term->id,
            'name' => 'Test structure', 'admission_fee' => 1000, 'registration_fee' => 500, 'security_fee' => 500,
            'processing_fee' => 100, 'enrollment_fee' => 1000, 'tuition_per_credit_hour' => 1000, 'exam_fee' => 750,
            'convocation_fee' => 0, 'late_fee' => 0, 'due_days' => 14, 'is_active' => true,
        ]);

        $service = app(FeeInvoiceService::class);
        $invoice = $service->generate($enrollment);
        $credits = (float) $enrollment->courses->sum('credit_hours');

        $this->assertNotNull($invoice);
        $this->assertSame($credits, (float) $invoice->credit_hours);
        $this->assertSame(1000 * $credits, (float) collect($invoice->line_items)->firstWhere('label', 'Tuition fee ('.number_format($credits, 1).' credit hours × 1,000.00)')['amount']);

        $originalTotal = $invoice->total_amount;
        $invoice->update(['status' => 'submitted']);
        $structure->update(['tuition_per_credit_hour' => 9000]);
        $this->assertSame((float) $originalTotal, (float) $service->generate($enrollment)->total_amount);
    }

    public function test_scholarship_discount_is_applied_strictly_on_tuition_fee(): void
    {
        $department = Department::create(['name' => 'Computer Science']);
        $section = Section::create(['name' => 'CS-A', 'department_id' => $department->id]);
        $program = Program::create(['department_id' => $department->id, 'name' => 'BS Computer Science', 'code' => 'BSCS', 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]);
        
        // Student awarded 50% Merit Scholarship based on 85% in Intermediate
        $student = Student::create([
            'name' => 'Merit Scholar',
            'email' => 'scholar@example.test',
            'phone' => '03009998877',
            'cnic' => '35201-1234567-1',
            'department_id' => $department->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'semester' => 'Semester 1',
            'inter_degree' => 'F.Sc Pre-Engineering',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 935,
            'inter_percentage' => 85.00,
            'scholarship_name' => 'Merit Scholarship (Gold - 50% Tuition Waiver)',
            'scholarship_percentage' => 50.00,
            'scholarship_status' => 'active',
            'course_ids' => [],
            'image' => 'default-user.png',
        ]);

        $year = AcademicYear::create(['name' => '2026-2027', 'starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
        $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall-2026', 'starts_on' => '2026-08-01', 'ends_on' => '2026-12-31', 'status' => 'active']);
        $course = Course::create(['code' => 'CS101', 'name' => 'Intro to Programming', 'description' => 'Fundamentals of Computing', 'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60, 'is_active' => true]);
        
        $enrollment = StudentSemesterEnrollment::create([
            'student_id' => $student->id, 'department_id' => $department->id, 'program_id' => $program->id,
            'section_id' => $section->id, 'academic_term_id' => $term->id, 'semester' => 'Semester 1',
            'academic_year' => '2026-2027', 'status' => 'active', 'enrolled_at' => today(),
        ]);
        $enrollment->courses()->create([
            'course_id' => $course->id, 'course_code' => $course->code, 'course_name' => $course->name,
            'credit_hours' => 3, 'total_marks' => 100, 'attendance_marks' => 10, 'mid_marks' => 30, 'final_marks' => 60,
        ]);
        $enrollment->load(['courses', 'program']);

        $structure = FeeStructure::create([
            'program_id' => $program->id, 'academic_term_id' => $term->id,
            'name' => 'BSCS Standard Fee Structure',
            'admission_fee' => 5000, 'registration_fee' => 2000, 'security_fee' => 3000,
            'processing_fee' => 1000, 'enrollment_fee' => 2000, 'tuition_per_credit_hour' => 2000, 'exam_fee' => 1500,
            'convocation_fee' => 0, 'late_fee' => 0, 'due_days' => 14, 'is_active' => true,
        ]);

        $service = app(FeeInvoiceService::class);
        $invoice = $service->generate($enrollment);

        $this->assertNotNull($invoice);
        // Tuition: 3 cr * 2000 = 6000
        // Scholarship 50% on Tuition = 3000 discount
        $this->assertEquals(3000.0, (float) $invoice->discount_amount);

        // Fixed fees: 5000 + 2000 + 3000 + 1000 + 2000 + 1500 = 14500
        // Gross subtotal = 14500 + 6000 = 20500
        $this->assertEquals(20500.0, (float) $invoice->subtotal);

        // Net payable = 20500 - 3000 = 17500
        $this->assertEquals(17500.0, (float) $invoice->total_amount);

        // Check line items has discount line
        $discountItem = collect($invoice->line_items)->firstWhere('is_discount', true);
        $this->assertNotNull($discountItem);
        $this->assertEquals(-3000.0, (float) $discountItem['amount']);
        $this->assertStringContainsString('50% on Tuition Fee', $discountItem['label']);
    }

    public function test_admin_can_create_student_with_cnic_inter_marks_and_scholarship(): void
    {
        $this->seed(\Database\Seeders\AccessControlSeeder::class);

        $admin = \App\Models\User::create([
            'name' => 'Admin Fee Test',
            'email' => 'admin.fee.test@example.com',
            'phone' => '03009988776',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('Super Admin');

        $department = Department::create(['name' => 'Data Science']);
        $section = Section::create(['name' => 'DS-A', 'department_id' => $department->id]);
        $program = Program::create(['department_id' => $department->id, 'name' => 'BS Data Science', 'code' => 'BSDS', 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('addStudent'), [
            'name' => 'Zeeshan Khan',
            'email' => 'zeeshan@example.test',
            'phone' => '03001122334',
            'cnic' => '35201-7654321-1',
            'department_id' => $department->id,
            'program_id' => $program->id,
            'section_id' => $section->id,
            'admission_type' => 'regular',
            'inter_degree' => 'ICS (Computer Science)',
            'inter_board' => 'BISE Lahore',
            'inter_roll_no' => '987654',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 990, // 90%
            'inter_percentage' => 90.00,
            'scholarship_name' => 'Merit Scholarship (Gold - 50% Tuition Waiver)',
            'scholarship_percentage' => 50.00,
            'scholarship_status' => 'active',
        ]);

        $student = Student::where('email', 'zeeshan@example.test')->first();
        $this->assertNotNull($student);
        $response->assertRedirect(route('addEnrollmentForm', ['student_id' => $student->id]));

        $this->assertDatabaseHas('students', [
            'email' => 'zeeshan@example.test',
            'cnic' => '35201-7654321-1',
            'inter_degree' => 'ICS (Computer Science)',
            'inter_percentage' => 90.00,
            'scholarship_percentage' => 50.00,
            'scholarship_status' => 'active',
        ]);
    }
}
