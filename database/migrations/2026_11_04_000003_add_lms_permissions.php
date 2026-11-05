<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $manageLms = Permission::firstOrCreate(['name' => 'lms.manage-assigned', 'guard_name' => 'web']);
        $viewLms = Permission::firstOrCreate(['name' => 'student.lms.view-own', 'guard_name' => 'web']);

        $adminRole = Role::where('name', 'Academic Admin')->where('guard_name', 'web')->first();
        if ($adminRole && ! $adminRole->hasPermissionTo($manageLms)) {
            $adminRole->givePermissionTo($manageLms);
        }

        $teacherRole = Role::where('name', 'Teacher')->where('guard_name', 'web')->first();
        if ($teacherRole && ! $teacherRole->hasPermissionTo($manageLms)) {
            $teacherRole->givePermissionTo($manageLms);
        }

        $studentRole = Role::where('name', 'Student')->where('guard_name', 'web')->first();
        if ($studentRole && ! $studentRole->hasPermissionTo($viewLms)) {
            $studentRole->givePermissionTo($viewLms);
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::whereIn('name', ['lms.manage-assigned', 'student.lms.view-own'])
            ->where('guard_name', 'web')
            ->delete();
    }
};
