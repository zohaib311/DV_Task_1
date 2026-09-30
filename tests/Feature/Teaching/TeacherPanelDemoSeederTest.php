<?php

namespace Tests\Feature\Teaching;

use App\Models\Academic\CourseOffering;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\User;
use Database\Seeders\Demo\TeacherPanelDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherPanelDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_is_repeatable_and_teacher_logins_have_isolated_and_shared_classes(): void
    {
        $this->seed(TeacherPanelDemoSeeder::class);
        $teacher = User::where('email', 'demo.teacher1@example.test')->firstOrFail();
        $this->assertTrue(Hash::check(TeacherPanelDemoSeeder::PASSWORD, $teacher->password));
        $this->actingAs($teacher)->get(route('teaching.dashboard'))->assertOk()->assertViewHas('offeringCount', 2)->assertViewHas('studentCount', 6);
        $this->get(route('teaching.offerings.index'))->assertSee('Demo Programming')->assertSee('Demo Computing Lab')->assertDontSee('Demo Mathematics');
        $other = CourseOffering::where('course_code', 'DEMO-CS102')->firstOrFail();
        $this->get(route('teaching.offerings.show', $other))->assertNotFound();
        $teacher->update(['password' => Hash::make('ChangedPassword123!')]);
        $this->seed(TeacherPanelDemoSeeder::class);
        $this->assertSame(2, User::count());
        $this->assertSame(3, CourseOffering::count());
        $this->assertSame(6, StudentSemesterEnrollment::count());
        $this->assertSame(18, StudentEnrollmentCourse::count());
        $this->assertTrue(Hash::check('ChangedPassword123!', $teacher->fresh()->password));
        $second = User::where('email', 'demo.teacher2@example.test')->firstOrFail();
        $this->actingAs($second)->get(route('teaching.offerings.index'))->assertOk()->assertSee('Demo Mathematics')->assertSee('Demo Computing Lab')->assertDontSee('Demo Programming');
    }

    public function test_demo_refuses_production_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('local/testing');
        app(TeacherPanelDemoSeeder::class)->run();
    }
}
