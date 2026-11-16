<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityWebsiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public homepage is accessible and renders core university sections.
     */
    public function test_public_homepage_renders_successfully_with_portal_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('My Form Task University');
        $response->assertSee('Portal Login');
        $response->assertSee('Featured Degree Programs');
        $response->assertSee('Admission Guidelines');
        $response->assertSee('World-Class Infrastructure Built for Success');
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee(route('login'));
    }

    /**
     * Test prospective student admission inquiry submission.
     */
    public function test_prospective_student_can_submit_inquiry(): void
    {
        $response = $this->post('/inquiry', [
            'name' => 'Ali Khan',
            'email' => 'ali.khan@example.com',
            'phone' => '03001234567',
            'program_interest' => 'BS Computer Science',
            'message' => 'I would like to inquire about eligibility criteria and scholarship requirements.',
        ]);

        $response->assertSessionHas('inquiry_success');
        $response->assertRedirect();
    }

    /**
     * Test inquiry form validation errors when required fields are missing.
     */
    public function test_inquiry_form_validates_required_fields(): void
    {
        $response = $this->post('/inquiry', [
            'name' => '',
            'email' => 'invalid-email',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    /**
     * Test that authenticated user sees their portal link instead of plain login.
     */
    public function test_authenticated_user_sees_portal_access_button(): void
    {
        $user = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@mftu.edu.pk',
            'phone' => '03001234567',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Main Dashboard');
        $response->assertSee(url('/dashboard'));
    }
}
