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

        $perm = Permission::firstOrCreate(['name' => 'timetable.manage', 'guard_name' => 'web']);
        $adminRole = Role::where('name', 'Academic Admin')->where('guard_name', 'web')->first();
        if ($adminRole && ! $adminRole->hasPermissionTo($perm)) {
            $adminRole->givePermissionTo($perm);
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::where('name', 'timetable.manage')->where('guard_name', 'web')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
