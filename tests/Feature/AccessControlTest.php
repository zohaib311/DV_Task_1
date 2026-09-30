<?php

namespace Tests\Feature;

use App\Models\Result\SemesterResult;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_role_cannot_open_an_admin_course_screen(): void
    {
        $this->seed(AccessControlSeeder::class);
        $studentUser = $this->user('student@example.com');
        $studentUser->assignRole('Student');

        $this->actingAs($studentUser)
            ->get(route('allCourses'))
            ->assertForbidden();
    }

    public function test_an_academic_admin_can_manage_roles_and_assign_user_roles(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = $this->user('admin@example.com');
        $admin->assignRole('Academic Admin');
        $teacherUser = $this->user('teacher@example.com');

        $this->actingAs($admin)
            ->get(route('access.index'))
            ->assertOk()
            ->assertSee('Roles & Permissions');

        $this->actingAs($admin)
            ->put(route('access.users.roles.update', $teacherUser), ['role_ids' => [
                \Spatie\Permission\Models\Role::findByName('Teacher')->id,
            ]])
            ->assertSessionHasNoErrors();

        $this->assertTrue($teacherUser->fresh()->hasRole('Teacher'));
    }

    public function test_a_published_result_requires_the_separate_correction_permission(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = $this->user('results-admin@example.com');
        $admin->assignRole('Academic Admin');
        $result = new SemesterResult(['status' => 'Pass', 'published_at' => now()]);

        $this->assertFalse($admin->can('update', $result));

        $admin->givePermissionTo('results.edit-published');

        $this->assertTrue($admin->fresh()->can('update', $result));
    }

    public function test_an_academic_admin_cannot_assign_the_super_admin_role(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = $this->user('academic-admin@example.com');
        $admin->assignRole('Academic Admin');
        $target = $this->user('target@example.com');

        $this->actingAs($admin)
            ->put(route('access.users.roles.update', $target), ['role_ids' => [
                \Spatie\Permission\Models\Role::findByName('Super Admin')->id,
            ]])
            ->assertForbidden();

        $this->assertFalse($target->fresh()->hasRole('Super Admin'));
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => str($email)->before('@')->replace('.', ' ')->title(),
            'email' => $email,
            'phone' => '0300'.str_pad((string) (User::count() + 1), 7, '0', STR_PAD_LEFT),
            'password' => bcrypt('password'),
            'image' => 'default-user.png',
        ]);
    }
}
