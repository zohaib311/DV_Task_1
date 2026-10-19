<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $student = Permission::findOrCreate('student.leaves.manage-own', 'web');
        $teacher = Permission::findOrCreate('leaves.review-assigned', 'web');

        Role::where('name', 'Student')->where('guard_name', 'web')->first()?->givePermissionTo($student);
        Role::where('name', 'Teacher')->where('guard_name', 'web')->first()?->givePermissionTo($teacher);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', ['student.leaves.manage-own', 'leaves.review-assigned'])->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
