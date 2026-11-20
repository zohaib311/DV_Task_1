<?php

namespace Tests\Feature\Admissions;

use App\Models\Academic\Program;
use App\Models\Admission\AdmissionApplication;
use App\Models\Admission\AdmissionInquiry;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineAdmissionsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $dept;
    private Program $program;
    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);

        $this->admin = User::create([
            'name' => 'Admissions Admin',
            'email' => 'admissions.admin@test.com',
            'phone' => '03009998877',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('Academic Admin');

        $this->dept = Department::create(['name' => 'Computer Science']);
        $this->program = Program::create([
            'department_id' => $this->dept->id,
            'name' => 'BS Computer Science',
            'code' => 'BSCS',
            'degree_level' => 'undergraduate',
            'total_semesters' => 8,
            'total_credit_hours' => 134,
            'is_active' => true,
        ]);
        $this->section = Section::create([
            'name' => 'CS-1A',
            'department_id' => $this->dept->id,
        ]);
    }

    /**
     * Test guest is directed to applicant login/register portal.
     */
    public function test_guest_is_directed_to_applicant_login_portal(): void
    {
        $response = $this->get(route('admissions.portal'));
        $response->assertRedirect(route('admissions.login'));

        $loginResponse = $this->get(route('admissions.login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Online Admission');
        $loginResponse->assertSee('Sign In to Apply');
        $loginResponse->assertSee('New Applicant Register');
    }

    /**
     * Test prospective candidate can register an applicant account and fill application.
     */
    public function test_candidate_can_register_applicant_account_and_access_apply_form(): void
    {
        $regResponse = $this->post(route('admissions.register.submit'), [
            'name' => 'Zubair Ahmed',
            'email' => 'zubair@example.com',
            'phone' => '03001239876',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $regResponse->assertRedirect(route('admissions.apply'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertEquals('zubair@example.com', $user->email);
        $this->assertTrue($user->hasRole('Applicant'));

        // Visit apply form as authenticated applicant
        $applyResponse = $this->get(route('admissions.apply'));
        $applyResponse->assertStatus(200);
        $applyResponse->assertSee('Zubair Ahmed');
        $applyResponse->assertSee('zubair@example.com');
        $applyResponse->assertSee('BS Computer Science');
    }

    /**
     * Test applicant can submit admission application and view their applicant dashboard.
     */
    public function test_applicant_can_submit_application_and_view_status_dashboard(): void
    {
        $applicant = User::create([
            'name' => 'Usman Tariq',
            'email' => 'usman.tariq@example.com',
            'phone' => '03112233445',
            'password' => bcrypt('password'),
        ]);
        $applicant->assignRole('Applicant');

        $this->actingAs($applicant);

        $payload = [
            'name' => 'Usman Tariq',
            'father_name' => 'Tariq Mehmood',
            'email' => 'usman.tariq@example.com',
            'phone' => '03112233445',
            'cnic' => '35202-1234567-1',
            'date_of_birth' => '2005-04-15',
            'gender' => 'male',
            'address' => 'House 123, Street 4, Lahore',
            'domicile' => 'Lahore',
            'department_id' => $this->dept->id,
            'program_id' => $this->program->id,
            'admission_type' => 'regular',
            'inter_degree' => 'FSc Pre-Engineering',
            'inter_board' => 'BISE Lahore',
            'inter_roll_no' => '542109',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 950,
            'scholarship_name' => 'Merit Scholarship',
        ];

        $response = $this->post(route('admissions.apply.submit'), $payload);
        $response->assertRedirect(route('admissions.applicant.dashboard'));

        $application = AdmissionApplication::where('email', 'usman.tariq@example.com')->first();
        $this->assertNotNull($application);
        $this->assertEquals($applicant->id, $application->user_id);
        $this->assertEquals('pending', $application->status);

        // View applicant dashboard
        $dashResponse = $this->get(route('admissions.applicant.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee($application->application_no);
        $dashResponse->assertSee('Usman Tariq');
        $dashResponse->assertSee('BS Computer Science');
        $dashResponse->assertSee('Pending Review');
    }

    /**
     * Test applicant CANNOT access university student portal before enrollment.
     */
    public function test_applicant_cannot_access_student_portal_before_official_enrollment(): void
    {
        $applicant = User::create([
            'name' => 'Pending Student',
            'email' => 'pending@example.com',
            'phone' => '03129876543',
            'password' => bcrypt('password'),
        ]);
        $applicant->assignRole('Applicant');

        $this->actingAs($applicant);

        // Student Portal Dashboard requires student.profile.view-own permission which Applicant does NOT have
        $response = $this->get(route('student.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Test admin enrollment assigns Student role and UNLOCKS Student Portal access.
     */
    public function test_admin_enrollment_unlocks_student_portal_access(): void
    {
        $applicant = User::create([
            'name' => 'Zeeshan Ali',
            'email' => 'zeeshan.ali@example.com',
            'phone' => '03129876543',
            'password' => bcrypt('password'),
        ]);
        $applicant->assignRole('Applicant');

        $app = AdmissionApplication::create([
            'user_id' => $applicant->id,
            'name' => 'Zeeshan Ali',
            'father_name' => 'Ali Raza',
            'email' => 'zeeshan.ali@example.com',
            'phone' => '03129876543',
            'cnic' => '35202-3334445-1',
            'department_id' => $this->dept->id,
            'program_id' => $this->program->id,
            'admission_type' => 'regular',
            'inter_degree' => 'FSc Pre-Engineering',
            'inter_board' => 'BISE Lahore',
            'inter_roll_no' => '998877',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 980,
            'inter_percentage' => 89.09,
            'status' => 'approved',
        ]);

        // Admin enrolls the student
        $this->actingAs($this->admin);

        $enrollResponse = $this->post(route('admissions.admin.enroll', $app->id), [
            'section_id' => $this->section->id,
            'create_portal_account' => 1,
            'scholarship_percentage' => 30,
            'admin_notes' => 'Officially enrolled in Fall 2026 CS-1A.',
        ]);

        $enrollResponse->assertSessionHas('success');

        // Check applicant user now has Student role
        $applicant->refresh();
        $this->assertTrue($applicant->hasRole('Student'));

        // Check Student record exists
        $student = Student::where('user_id', $applicant->id)->first();
        $this->assertNotNull($student);
        $this->assertEquals($this->section->id, $student->section_id);

        // Switch to the newly enrolled student
        $this->actingAs($applicant);

        // Now Applicant Dashboard shows congratulatory message & link to portal
        $dashResponse = $this->get(route('admissions.applicant.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Admission Confirmed');
        $dashResponse->assertSee($student->registration_no);

        // Now Student Portal is UNLOCKED!
        $portalResponse = $this->get(route('student.dashboard'));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Student Portal');
    }

    /**
     * Test public applicant can track status via public tracking page.
     */
    public function test_public_applicant_can_track_status(): void
    {
        $app = AdmissionApplication::create([
            'name' => 'Ayesha Bibi',
            'email' => 'ayesha@example.com',
            'phone' => '03221144556',
            'cnic' => '35201-9876543-2',
            'department_id' => $this->dept->id,
            'program_id' => $this->program->id,
            'admission_type' => 'regular',
            'inter_degree' => 'ICS',
            'inter_board' => 'BISE Gujranwala',
            'inter_roll_no' => '889900',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 890,
            'inter_percentage' => 80.91,
            'status' => 'pending',
        ]);

        $response = $this->get(route('admissions.status', ['query' => $app->application_no]));
        $response->assertStatus(200);
        $response->assertSee($app->application_no);
        $response->assertSee('Ayesha Bibi');
        $response->assertSee('Pending Review');
    }

    /**
     * Test non-admin user cannot access admin admissions hub.
     */
    public function test_non_admin_cannot_access_admin_admissions(): void
    {
        // Guest redirect
        $this->get(route('admissions.admin.index'))->assertRedirect(route('login'));

        // User with Student role (lacks students.manage permission)
        $plainUser = User::create([
            'name' => 'Regular Student User',
            'email' => 'student.regular@example.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
        ]);
        $plainUser->assignRole('Student');

        $this->actingAs($plainUser);
        $this->get(route('admissions.admin.index'))->assertStatus(403);
    }

    /**
     * Test admin can view applications in dashboard.
     */
    public function test_admin_can_view_applications_in_dashboard(): void
    {
        $app = AdmissionApplication::create([
            'name' => 'Hamza Tariq',
            'email' => 'hamza@example.com',
            'phone' => '03331122334',
            'cnic' => '35202-7778889-1',
            'department_id' => $this->dept->id,
            'program_id' => $this->program->id,
            'admission_type' => 'regular',
            'inter_degree' => 'FSc Pre-Medical',
            'inter_board' => 'BISE Lahore',
            'inter_roll_no' => '334455',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 920,
            'inter_percentage' => 83.64,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admissions.admin.index'));
        $response->assertStatus(200);
        $response->assertSee('Online Admissions Management');
        $response->assertSee($app->application_no);
        $response->assertSee('Hamza Tariq');
    }

    /**
     * Test admin can update application review status.
     */
    public function test_admin_can_update_application_status(): void
    {
        $app = AdmissionApplication::create([
            'name' => 'Bilal Ahmed',
            'email' => 'bilal@example.com',
            'phone' => '03445566778',
            'cnic' => '35202-5556667-1',
            'department_id' => $this->dept->id,
            'program_id' => $this->program->id,
            'admission_type' => 'regular',
            'inter_degree' => 'ICS',
            'inter_board' => 'BISE Rawalpindi',
            'inter_roll_no' => '112233',
            'inter_total_marks' => 1100,
            'inter_obtained_marks' => 900,
            'inter_percentage' => 81.82,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admissions.admin.status', $app->id), [
            'status' => 'approved',
            'admin_notes' => 'Credentials verified against BISE portal. Approved for merit intake.',
        ]);

        $response->assertSessionHas('success');
        $app->refresh();
        $this->assertEquals('approved', $app->status);
        $this->assertStringContainsString('Credentials verified', $app->admin_notes);
    }

    /**
     * Test admin can view and update prospectus inquiries.
     */
    public function test_admin_can_manage_prospectus_inquiries(): void
    {
        $inquiry = AdmissionInquiry::create([
            'name' => 'Khadija Bibi',
            'email' => 'khadija@example.com',
            'phone' => '03001239876',
            'program_interest' => 'BS Computer Science',
            'message' => 'Please provide syllabus and fee schedule details.',
            'status' => 'new',
        ]);

        $this->actingAs($this->admin);

        // View inquiries page
        $viewResponse = $this->get(route('admissions.admin.inquiries'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Website Prospectus Inquiries');
        $viewResponse->assertSee('Khadija Bibi');

        // Update inquiry status
        $updateResponse = $this->post(route('admissions.admin.inquiry-status', $inquiry->id), [
            'status' => 'contacted',
            'admin_notes' => 'Sent prospectus PDF via WhatsApp and Email.',
        ]);

        $updateResponse->assertSessionHas('success');
        $inquiry->refresh();
        $this->assertEquals('contacted', $inquiry->status);
        $this->assertEquals('Sent prospectus PDF via WhatsApp and Email.', $inquiry->admin_notes);
    }
}
