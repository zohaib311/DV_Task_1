<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The application renders the public university website for visitors.
     */
    public function test_the_application_renders_university_website_for_visitors(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Portal Login');
        $response->assertSee('My Form Task University');
    }
}
