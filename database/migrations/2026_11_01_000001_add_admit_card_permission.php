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

        $perm = Permission::firstOrCreate(['name' => 'student.admit-card.view-own', 'guard_name' => 'web']);
        $studentRole = Role::where('name', 'Student')->where('guard_name', 'web')->first();
        if ($studentRole && ! $studentRole->hasPermissionTo($perm)) {
            $studentRole->givePermissionTo($perm);
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::where('name', 'student.admit-card.view-own')->where('guard_name', 'web')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
