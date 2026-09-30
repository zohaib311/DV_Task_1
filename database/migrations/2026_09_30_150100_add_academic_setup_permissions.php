<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['terms.manage', 'curriculum.manage', 'offerings.manage'] as $name) {
            $permission = Permission::findOrCreate($name, 'web');
            Role::where('name', 'Academic Admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Retain permissions and administrator assignments on rollback.
    }
};
