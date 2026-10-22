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
}
