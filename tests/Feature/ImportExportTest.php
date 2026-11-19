<?php

namespace Tests\Feature;

use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $department;
    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin.manager@test.com',
            'phone' => '03001239999',
            'password' => bcrypt('password'),
        ]);
        $this->admin->givePermissionTo('students.manage');
        $this->admin->givePermissionTo('teachers.manage');

        $this->department = Department::create(['name' => 'Computer Section (IT)']);
        $this->section = Section::create(['name' => 'A', 'department_id' => $this->department->id]);
    }

    /**
     * Test export students streams CSV successfully.
     */
    public function test_admin_can_export_students_as_csv(): void
    {
        Student::create([
            'name' => 'Export Student Test',
            'email' => 'export.student@test.com',
            'phone' => '03009988776',
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('students.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Registration No', $response->streamedContent());
        $this->assertStringContainsString('Export Student Test', $response->streamedContent());
        $this->assertStringContainsString('export.student@test.com', $response->streamedContent());
    }

    /**
     * Test download students template CSV.
     */
    public function test_admin_can_download_students_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('students.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('name,email,phone', $response->streamedContent());
    }

    /**
     * Test import students with optional fields missing imports cleanly with smart defaults.
     */
    public function test_admin_can_safely_import_students_with_missing_optional_fields(): void
    {
        $csvContent = "name,email\n";
        $csvContent .= "Zohaib Tariq,zohaib.unique1@test.com\n";
        $csvContent .= "Ayesha Noor,ayesha.unique2@test.com\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('students.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('allStudents'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'email' => 'zohaib.unique1@test.com',
            'name' => 'Zohaib Tariq',
        ]);
        $this->assertDatabaseHas('students', [
            'email' => 'ayesha.unique2@test.com',
            'name' => 'Ayesha Noor',
        ]);

        $importedStudent = Student::where('email', 'zohaib.unique1@test.com')->first();
        $this->assertNotEmpty($importedStudent->registration_no);
        $this->assertStringStartsWith('REG-', $importedStudent->registration_no);
        $this->assertNotEmpty($importedStudent->phone);
    }

    /**
     * Test import students skips duplicates gracefully without crashing.
     */
    public function test_import_students_handles_duplicate_email_gracefully(): void
    {
        Student::create([
            'name' => 'Existing Student',
            'email' => 'existing.student@test.com',
            'phone' => '03001112223',
            'department_id' => $this->department->id,
            'section_id' => $this->section->id,
        ]);

        $csvContent = "name,email,phone\n";
        $csvContent .= "Existing Student,existing.student@test.com,03001112223\n";
        $csvContent .= "New Unique Student,new.unique@test.com,03004445556\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('students.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('allStudents'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_errors');

        $this->assertDatabaseHas('students', [
            'email' => 'new.unique@test.com',
            'name' => 'New Unique Student',
        ]);
    }

    /**
     * Test export teachers streams CSV successfully.
     */
    public function test_admin_can_export_teachers_as_csv(): void
    {
        Teacher::create([
            'name' => 'Prof. Export Teacher',
            'email' => 'export.teacher@test.com',
            'phone' => '03112223334',
            'course' => 'Cloud Computing & Distributed Systems',
        ]);

        $response = $this->actingAs($this->admin)->get(route('teachers.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Prof. Export Teacher', $response->streamedContent());
        $this->assertStringContainsString('export.teacher@test.com', $response->streamedContent());
    }

    /**
     * Test download teachers template CSV.
     */
    public function test_admin_can_download_teachers_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('teachers.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('name,email,phone,course', $response->streamedContent());
    }

    /**
     * Test import teachers with missing optional fields defaults safely.
     */
    public function test_admin_can_safely_import_teachers(): void
    {
        $csvContent = "name,email\n";
        $csvContent .= "Dr. Sarah Khan,sarah.teacher1@test.com\n";
        $csvContent .= "Prof. Bilal Ahmed,bilal.teacher2@test.com\n";

        $file = UploadedFile::fake()->createWithContent('teachers.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('teachers.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('allTeachers'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'email' => 'sarah.teacher1@test.com',
            'name' => 'Dr. Sarah Khan',
            'course' => 'General Faculty',
        ]);
        $this->assertDatabaseHas('teachers', [
            'email' => 'bilal.teacher2@test.com',
            'name' => 'Prof. Bilal Ahmed',
        ]);
    }

    /**
     * Test unauthorized user cannot access import or export routes.
     */
    public function test_unauthorized_user_cannot_access_import_or_export(): void
    {
        $studentUser = User::create([
            'name' => 'Normal Student User',
            'email' => 'unauth.user@test.com',
            'phone' => '03009988111',
            'password' => bcrypt('password'),
        ]);
        $studentUser->assignRole('Student');

        $this->actingAs($studentUser)->get(route('students.export'))->assertForbidden();
        $this->actingAs($studentUser)->get(route('teachers.export'))->assertForbidden();
        $this->actingAs($studentUser)->post(route('students.import'))->assertForbidden();
        $this->actingAs($studentUser)->post(route('teachers.import'))->assertForbidden();
    }
}
