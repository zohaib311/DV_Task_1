<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\CourseOffering;
use App\Models\Academic\Program;
use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Fee\FeeStructure;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\Fee\FeeInvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ScholarshipDemoSeeder extends Seeder
{
    /**
     * Seeds 2 Demo Scholarship Students (Merit Gold 50% & Merit Silver 30%) with active enrollments & fee invoices.
     * Fully idempotent — safe to re-run multiple times.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Department, Program, Section, and Term
            $department = Department::firstOrCreate(['name' => 'Computer Section (IT)']);
            $program = Program::firstOrCreate(
                ['code' => 'BSCS'],
                ['name' => 'BS COMPUTER SCIENCE', 'department_id' => $department->id, 'duration_years' => 4, 'total_semesters' => 8, 'is_active' => true]
            );
            $section = Section::firstOrCreate(
                ['name' => 'Computer Section', 'department_id' => $department->id]
            );

            $term = AcademicTerm::where('status', 'active')->latest('id')->first();
            if (! $term) {
                $year = AcademicYear::firstOrCreate(['name' => '2026-2027'], ['starts_on' => '2026-08-01', 'ends_on' => '2027-07-31']);
                $term = AcademicTerm::create(['academic_year_id' => $year->id, 'name' => 'Fall-2026', 'starts_on' => '2026-08-15', 'ends_on' => '2026-12-31', 'status' => 'active']);
            }

            // 2. Courses (3 Semester 1 courses = 9.0 Credit Hours)
            $coursesData = [
                ['code' => 'CS101', 'name' => 'Introduction to Computing', 'credit_hours' => 3.0, 'description' => 'Fundamental concepts of computer hardware, software, and logic.'],
                ['code' => 'CS102', 'name' => 'Programming Fundamentals', 'credit_hours' => 3.0, 'description' => 'Structured problem solving and algorithmic programming.'],
                ['code' => 'MT101', 'name' => 'Calculus & Analytical Geometry', 'credit_hours' => 3.0, 'description' => 'Differential and integral calculus with analytical geometry.'],
            ];

            $courses = [];
            foreach ($coursesData as $c) {
                $courses[] = Course::firstOrCreate(
                    ['code' => $c['code']],
                    [
                        'name' => $c['name'],
                        'description' => $c['description'],
                        'credit_hours' => $c['credit_hours'],
                        'total_marks' => 100,
                        'attendance_marks' => 10,
                        'mid_marks' => 30,
                        'final_marks' => 60,
                        'is_active' => true,
                    ]
                );
            }

            // 3. Ensure a Fee Structure exists for BSCS in this Term
            $feeStructure = FeeStructure::firstOrCreate(
                ['program_id' => $program->id, 'academic_term_id' => $term->id],
                [
                    'name' => 'BSCS Standard Academic Fee Structure',
                    'admission_fee' => 5000.0,
                    'registration_fee' => 2000.0,
                    'security_fee' => 3000.0,
                    'processing_fee' => 1000.0,
                    'enrollment_fee' => 2000.0,
                    'tuition_per_credit_hour' => 2500.0, // 9 cr * 2500 = 22,500 tuition
                    'exam_fee' => 1500.0,
                    'convocation_fee' => 0.0,
                    'late_fee' => 500.0,
                    'due_days' => 14,
                    'is_active' => true,
                ]
            );

            $studentRole = Role::findOrCreate('Student', 'web');
            $feeInvoiceService = app(FeeInvoiceService::class);

            // 4. Demo Students Data
            $demoStudents = [
                [
                    'email' => 'sara.merit@example.test',
                    'name' => 'Sara Ahmed (Merit Gold Scholar)',
                    'phone' => '03001234568',
                    'cnic' => '35201-8899771-2',
                    'inter_degree' => 'F.Sc Pre-Engineering',
                    'inter_board' => 'BISE Lahore',
                    'inter_roll_no' => '654321',
                    'inter_total_marks' => 1100.0,
                    'inter_obtained_marks' => 968.0,
                    'inter_percentage' => 88.00,
                    'scholarship_name' => 'Merit Scholarship (Gold - 50% Tuition Waiver)',
                    'scholarship_percentage' => 50.00,
                    'scholarship_status' => 'active',
                ],
                [
                    'email' => 'bilal.merit@example.test',
                    'name' => 'Bilal Hassan (Merit Silver Scholar)',
                    'phone' => '03001234569',
                    'cnic' => '35202-1122334-1',
                    'inter_degree' => 'ICS (Computer Science)',
                    'inter_board' => 'Federal Board Islamabad',
                    'inter_roll_no' => '789012',
                    'inter_total_marks' => 1100.0,
                    'inter_obtained_marks' => 858.0,
                    'inter_percentage' => 78.00,
                    'scholarship_name' => 'Merit Scholarship (Silver - 30% Tuition Waiver)',
                    'scholarship_percentage' => 30.00,
                    'scholarship_status' => 'active',
                ],
            ];

            foreach ($demoStudents as $data) {
                // User Account
                $user = User::where('email', $data['email'])->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'password' => Hash::make('1234'),
                        'image' => 'default-user.png',
                    ]);
                    $user->assignRole($studentRole);
                } else {
                    $user->update(['name' => $data['name'], 'phone' => $data['phone']]);
                }

                // Student Profile
                $student = Student::where('email', $data['email'])->first();
                if (! $student) {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'cnic' => $data['cnic'],
                        'department_id' => $department->id,
                        'program_id' => $program->id,
                        'section_id' => $section->id,
                        'admission_type' => 'regular',
                        'admitted_semester' => 'Semester 1',
                        'semester' => 'Semester 1',
                        'inter_degree' => $data['inter_degree'],
                        'inter_board' => $data['inter_board'],
                        'inter_roll_no' => $data['inter_roll_no'],
                        'inter_total_marks' => $data['inter_total_marks'],
                        'inter_obtained_marks' => $data['inter_obtained_marks'],
                        'inter_percentage' => $data['inter_percentage'],
                        'scholarship_name' => $data['scholarship_name'],
                        'scholarship_percentage' => $data['scholarship_percentage'],
                        'scholarship_status' => $data['scholarship_status'],
                        'image' => 'default-user.png',
                    ]);
                } else {
                    $student->update([
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'cnic' => $data['cnic'],
                        'inter_degree' => $data['inter_degree'],
                        'inter_board' => $data['inter_board'],
                        'inter_roll_no' => $data['inter_roll_no'],
                        'inter_total_marks' => $data['inter_total_marks'],
                        'inter_obtained_marks' => $data['inter_obtained_marks'],
                        'inter_percentage' => $data['inter_percentage'],
                        'scholarship_name' => $data['scholarship_name'],
                        'scholarship_percentage' => $data['scholarship_percentage'],
                        'scholarship_status' => $data['scholarship_status'],
                    ]);
                }

                // Semester 1 Enrollment
                $enrollment = StudentSemesterEnrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester' => 'Semester 1',
                    ],
                    [
                        'department_id' => $department->id,
                        'program_id' => $program->id,
                        'section_id' => $section->id,
                        'academic_term_id' => $term->id,
                        'academic_year' => $term->academicYear?->name ?? '2026-2027',
                        'status' => 'active',
                        'enrolled_at' => now(),
                    ]
                );

                // Register Courses for this enrollment
                foreach ($courses as $c) {
                    StudentEnrollmentCourse::firstOrCreate(
                        [
                            'student_semester_enrollment_id' => $enrollment->id,
                            'course_id' => $c->id,
                        ],
                        [
                            'course_code' => $c->code,
                            'course_name' => $c->name,
                            'credit_hours' => $c->credit_hours,
                            'total_marks' => 100,
                            'attendance_marks' => $c->attendance_marks,
                            'mid_marks' => $c->mid_marks,
                            'final_marks' => $c->final_marks,
                            'registration_type' => 'required',
                        ]
                    );
                }

                // Automatically generate fee invoice with scholarship discount applied
                $enrollment->load('courses', 'student', 'program');
                $feeInvoiceService->generate($enrollment);
            }
        });
    }
}
