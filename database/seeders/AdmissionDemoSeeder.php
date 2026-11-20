<?php

namespace Database\Seeders;

use App\Models\Academic\Program;
use App\Models\Admission\AdmissionApplication;
use App\Models\Admission\AdmissionInquiry;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdmissionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Baseline Roles Exist
        $this->call(AccessControlSeeder::class);

        // 2. Locate or Create Departments & Programs
        $csDept = Department::where('name', 'Computer Science')->first()
            ?? Department::where('name', 'like', '%Computer%')->first()
            ?? Department::first();

        $itDept = Department::where('name', 'Information Technology')->first()
            ?? Department::first();

        $csProgram = Program::where('code', 'BSCS')->first()
            ?? Program::first();

        $itProgram = Program::where('code', 'BSIT')->first()
            ?? Program::where('id', '!=', $csProgram->id)->first()
            ?? $csProgram;

        $csSection = Section::where('department_id', $csDept->id)->first()
            ?? Section::first();

        $defaultPassword = Hash::make('1234');

        // =========================================================================
        // APPLICANT 1: Pending Review (Regular Entry)
        // =========================================================================
        $userPending = User::updateOrCreate(
            ['email' => 'applicant.pending@example.test'],
            [
                'name' => 'Hamza Tariq',
                'phone' => '03001110001',
                'password' => $defaultPassword,
                'image' => 'default-user.png',
            ]
        );
        $userPending->syncRoles(['Applicant']);

        AdmissionApplication::updateOrCreate(
            ['application_no' => 'APP-2026-0101'],
            [
                'user_id' => $userPending->id,
                'name' => 'Hamza Tariq',
                'father_name' => 'Tariq Mehmood',
                'email' => 'applicant.pending@example.test',
                'phone' => '03001110001',
                'cnic' => '35202-1110001-1',
                'dob' => '2005-03-12',
                'gender' => 'male',
                'address' => 'House 45, Street 12, Gulberg III, Lahore',
                'city' => 'Lahore',
                'department_id' => $csDept->id,
                'program_id' => $csProgram->id,
                'admission_type' => 'regular',
                'inter_degree' => 'FSc Pre-Engineering',
                'inter_board' => 'BISE Lahore',
                'inter_roll_no' => '412098',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 960,
                'inter_percentage' => 87.27,
                'scholarship_name' => 'Merit Scholarship',
                'status' => 'pending',
                'admin_notes' => null,
            ]
        );

        // =========================================================================
        // APPLICANT 2: Under Review (Regular Entry)
        // =========================================================================
        $userReview = User::updateOrCreate(
            ['email' => 'applicant.review@example.test'],
            [
                'name' => 'Ayesha Bibi',
                'phone' => '03002220002',
                'password' => $defaultPassword,
                'image' => 'default-user.png',
            ]
        );
        $userReview->syncRoles(['Applicant']);

        AdmissionApplication::updateOrCreate(
            ['application_no' => 'APP-2026-0102'],
            [
                'user_id' => $userReview->id,
                'name' => 'Ayesha Bibi',
                'father_name' => 'Muhammad Aslam',
                'email' => 'applicant.review@example.test',
                'phone' => '03002220002',
                'cnic' => '35201-2220002-2',
                'dob' => '2005-07-25',
                'gender' => 'female',
                'address' => 'Model Town Block B, Gujranwala',
                'city' => 'Gujranwala',
                'department_id' => $itDept->id,
                'program_id' => $itProgram->id,
                'admission_type' => 'regular',
                'inter_degree' => 'ICS (Physics & Stats)',
                'inter_board' => 'BISE Gujranwala',
                'inter_roll_no' => '518920',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 920,
                'inter_percentage' => 83.64,
                'scholarship_name' => 'Need Based Financial Aid',
                'status' => 'under_review',
                'admin_notes' => 'Intermediate marks verified with BISE board online portal. Under department committee review.',
            ]
        );

        // =========================================================================
        // APPLICANT 3: Approved - Ready to be Enrolled! (Regular Entry)
        // =========================================================================
        $userApproved = User::updateOrCreate(
            ['email' => 'applicant.approved@example.test'],
            [
                'name' => 'Zeeshan Ali',
                'phone' => '03003330003',
                'password' => $defaultPassword,
                'image' => 'default-user.png',
            ]
        );
        $userApproved->syncRoles(['Applicant']);

        AdmissionApplication::updateOrCreate(
            ['application_no' => 'APP-2026-0103'],
            [
                'user_id' => $userApproved->id,
                'name' => 'Zeeshan Ali',
                'father_name' => 'Ali Raza',
                'email' => 'applicant.approved@example.test',
                'phone' => '03003330003',
                'cnic' => '35202-3330003-1',
                'dob' => '2004-11-18',
                'gender' => 'male',
                'address' => 'Canal View Society, Lahore',
                'city' => 'Lahore',
                'department_id' => $csDept->id,
                'program_id' => $csProgram->id,
                'admission_type' => 'regular',
                'inter_degree' => 'FSc Pre-Engineering',
                'inter_board' => 'BISE Lahore',
                'inter_roll_no' => '309871',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 995,
                'inter_percentage' => 90.45,
                'scholarship_name' => 'Presidential Merit Scholarship',
                'status' => 'approved',
                'admin_notes' => 'Top merit candidate. Approved by Academic Board. Click Enroll to assign section.',
            ]
        );

        // =========================================================================
        // APPLICANT 4: Approved - ADP Lateral Entry (Ready to enroll into Sem 5!)
        // =========================================================================
        $userLateral = User::updateOrCreate(
            ['email' => 'applicant.lateral@example.test'],
            [
                'name' => 'Bilal Ahmed',
                'phone' => '03004440004',
                'password' => $defaultPassword,
                'image' => 'default-user.png',
            ]
        );
        $userLateral->syncRoles(['Applicant']);

        AdmissionApplication::updateOrCreate(
            ['application_no' => 'APP-2026-0104'],
            [
                'user_id' => $userLateral->id,
                'name' => 'Bilal Ahmed',
                'father_name' => 'Ahmed Saeed',
                'email' => 'applicant.lateral@example.test',
                'phone' => '03004440004',
                'cnic' => '35202-4440004-1',
                'dob' => '2003-05-14',
                'gender' => 'male',
                'address' => 'Allama Iqbal Town, Lahore',
                'city' => 'Lahore',
                'department_id' => $csDept->id,
                'program_id' => $csProgram->id,
                'admission_type' => 'lateral_adp',
                'inter_degree' => 'ICS',
                'inter_board' => 'BISE Lahore',
                'inter_roll_no' => '221190',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 880,
                'inter_percentage' => 80.00,
                'previous_institution' => 'Punjab College of Information Technology',
                'previous_degree' => 'Associate Degree in Computer Science (ADP CS)',
                'previous_cgpa' => '3.65',
                'transferred_credits' => 64.0,
                'status' => 'approved',
                'admin_notes' => 'ADP transcript verified with 64 transfer credits. Eligible for direct Semester 5 admission.',
            ]
        );

        // =========================================================================
        // APPLICANT 5: Officially Enrolled (Student Portal Unlocked)
        // =========================================================================
        $userEnrolled = User::updateOrCreate(
            ['email' => 'applicant.enrolled@example.test'],
            [
                'name' => 'Fatima Noor',
                'phone' => '03005550005',
                'password' => $defaultPassword,
                'image' => 'default-user.png',
            ]
        );
        $userEnrolled->syncRoles(['Student']);

        $enrolledStudent = Student::updateOrCreate(
            ['email' => 'applicant.enrolled@example.test'],
            [
                'name' => 'Fatima Noor',
                'phone' => '03005550005',
                'cnic' => '35201-5550005-2',
                'department_id' => $csDept->id,
                'program_id' => $csProgram->id,
                'section_id' => $csSection->id,
                'admission_type' => 'regular',
                'semester' => 'Semester 1',
                'admitted_semester' => 'Semester 1',
                'inter_degree' => 'FSc Pre-Medical (Additional Math)',
                'inter_board' => 'BISE Federal',
                'inter_roll_no' => '994411',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 975,
                'inter_percentage' => 88.64,
                'scholarship_name' => 'Merit Waiver',
                'scholarship_percentage' => 25.0,
                'scholarship_status' => 'active',
                'image' => 'default-user.png',
                'user_id' => $userEnrolled->id,
            ]
        );

        AdmissionApplication::updateOrCreate(
            ['application_no' => 'APP-2026-0105'],
            [
                'user_id' => $userEnrolled->id,
                'name' => 'Fatima Noor',
                'father_name' => 'Noor Muhammad',
                'email' => 'applicant.enrolled@example.test',
                'phone' => '03005550005',
                'cnic' => '35201-5550005-2',
                'dob' => '2005-01-30',
                'gender' => 'female',
                'address' => 'DHA Phase 5, Lahore',
                'city' => 'Lahore',
                'department_id' => $csDept->id,
                'program_id' => $csProgram->id,
                'admission_type' => 'regular',
                'inter_degree' => 'FSc Pre-Medical',
                'inter_board' => 'BISE Federal',
                'inter_roll_no' => '994411',
                'inter_total_marks' => 1100,
                'inter_obtained_marks' => 975,
                'inter_percentage' => 88.64,
                'scholarship_name' => 'Merit Waiver',
                'status' => 'enrolled',
                'enrolled_student_id' => $enrolledStudent->id,
                'enrolled_at' => now()->subDay(),
                'admin_notes' => 'Enrolled in Fall 2026 CS Section. Student portal credentials active.',
            ]
        );

        // =========================================================================
        // SAMPLE PROSPECTUS INQUIRIES
        // =========================================================================
        AdmissionInquiry::updateOrCreate(
            ['email' => 'tahir.inquiry@example.test'],
            [
                'name' => 'Tahir Khan',
                'email' => 'tahir.inquiry@example.test',
                'phone' => '03119876543',
                'program_interest' => 'BS Computer Science',
                'message' => 'Hello, I want to inquire about the evening shift classes and entry test schedule for Fall 2026.',
                'status' => 'new',
                'admin_notes' => null,
            ]
        );

        AdmissionInquiry::updateOrCreate(
            ['email' => 'sana.inquiry@example.test'],
            [
                'name' => 'Sana Malik',
                'email' => 'sana.inquiry@example.test',
                'phone' => '03224445566',
                'program_interest' => 'BS Information Technology',
                'message' => 'Are there any merit scholarships available for female students with above 85% in FSc?',
                'status' => 'contacted',
                'admin_notes' => 'Counselor contacted via phone, shared scholarship policy PDF on WhatsApp.',
            ]
        );

        AdmissionInquiry::updateOrCreate(
            ['email' => 'usman.inquiry@example.test'],
            [
                'name' => 'Usman Ghani',
                'email' => 'usman.inquiry@example.test',
                'phone' => '03337778899',
                'program_interest' => 'ADP Lateral Entry',
                'message' => 'I have completed ADP in Computer Science with 3.5 CGPA from BZU. Can I transfer directly to Semester 5?',
                'status' => 'closed',
                'admin_notes' => 'Candidate counseled and registered an online application for ADP Lateral Entry.',
            ]
        );
    }
}
