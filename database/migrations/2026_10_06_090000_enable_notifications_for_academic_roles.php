<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('notifications.view-own', 'web');
        Role::whereIn('name', ['Academic Admin', 'HOD / Program Coordinator', 'Teacher', 'Student'])->where('guard_name', 'web')
            ->get()->each->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::whereIn('name', ['Academic Admin', 'HOD / Program Coordinator', 'Teacher'])->where('guard_name', 'web')
            ->get()->each->revokePermissionTo('notifications.view-own');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
