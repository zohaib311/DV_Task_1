<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    /** @var array<string, array<int, string>> */
    private const ROLE_PERMISSIONS = [
        'Super Admin' => [],
        'Academic Admin' => [
            'dashboard.view', 'roles.view', 'roles.manage', 'permissions.assign', 'users.assign-role', 'users.manage', 'students.manage', 'teachers.manage',
            'departments.manage', 'sections.manage', 'courses.manage', 'events.manage',
            'enrollments.view', 'enrollments.create', 'enrollments.promote', 'enrollments.manage-courses',
            'results.view-all', 'results.create', 'results.edit', 'results.publish',
            'academic-history.view', 'audit.view',
        ],
        'HOD / Program Coordinator' => [
            'dashboard.view', 'courses.manage', 'enrollments.view', 'enrollments.manage-courses',
            'results.view-all', 'results.approve', 'academic-history.view', 'audit.view',
        ],
        'Teacher' => [
            'dashboard.view', 'offerings.view-assigned', 'attendance.manage-assigned',
            'assessments.manage-assigned', 'marks.manage-assigned', 'results.submit',
        ],
        'Student' => [
            'dashboard.view', 'student.profile.view-own', 'student.attendance.view-own',
            'student.result.view-own',
        ],
    ];

    /** @var array<int, string> */
    private const PERMISSIONS = [
        'dashboard.view',
        'roles.view', 'roles.manage', 'permissions.assign', 'users.assign-role', 'users.manage',
        'departments.manage', 'sections.manage', 'courses.manage', 'curriculum.manage', 'terms.manage',
        'students.manage', 'teachers.manage', 'events.manage',
        'enrollments.view', 'enrollments.create', 'enrollments.promote', 'enrollments.manage-courses',
        'offerings.view-assigned', 'attendance.manage-assigned', 'assessments.manage-assigned', 'marks.manage-assigned',
        'results.view-all', 'results.create', 'results.edit', 'results.submit', 'results.approve',
        'results.publish', 'results.edit-published',
        'student.profile.view-own', 'student.attendance.view-own', 'student.result.view-own',
        'academic-history.view', 'audit.view',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            if ($roleName !== 'Super Admin') {
                $role->syncPermissions($permissions);
            }
        }

        $this->linkExistingProfiles();

        // The initial installation needs one administrator. This only affects an
        // unassigned existing system and never replaces an existing Super Admin.
        if (! User::role('Super Admin')->exists()) {
            User::query()->oldest('id')->first()?->assignRole('Super Admin');
        }
    }

    private function linkExistingProfiles(): void
    {
        $linkedUserIds = [];

        Student::query()->whereNull('user_id')->orderBy('id')->each(function (Student $student) use (&$linkedUserIds) {
            $user = User::query()->where('email', $student->email)->first();
            if ($user && ! in_array($user->id, $linkedUserIds, true) && ! $user->teacherProfile()->exists()) {
                $student->update(['user_id' => $user->id]);
                $linkedUserIds[] = $user->id;
            }
        });

        Teacher::query()->whereNull('user_id')->orderBy('id')->each(function (Teacher $teacher) use (&$linkedUserIds) {
            $user = User::query()->where('email', $teacher->email)->first();
            if ($user && ! in_array($user->id, $linkedUserIds, true) && ! $user->studentProfile()->exists()) {
                $teacher->update(['user_id' => $user->id]);
                $linkedUserIds[] = $user->id;
            }
        });
    }
}
