<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlController extends Controller
{
    public function index()
    {
        $roles = Role::query()->with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $users = User::query()->with(['roles', 'studentProfile', 'teacherProfile'])->orderBy('name')->get();

        return view('access-control.index', compact('roles', 'permissions', 'users'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9 \/&-]*$/', Rule::unique('roles', 'name')],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create(['name' => trim($validated['name']), 'guard_name' => 'web']);
        $role->syncPermissions(Permission::query()->whereIn('id', $validated['permission_ids'] ?? [])->get());
        $this->forgetPermissionCache();

        return back()->with('success', 'Role created and permissions assigned successfully.');
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if ($role->name === 'Super Admin') {
            return back()->withErrors(['role' => 'Super Admin always has full system access and does not need individual permission assignments.']);
        }

        $role->syncPermissions(Permission::query()->whereIn('id', $validated['permission_ids'] ?? [])->get());
        $this->forgetPermissionCache();

        return back()->with('success', "Permissions updated for {$role->name}.");
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9.-]*$/', Rule::unique('permissions', 'name')],
        ]);

        Permission::create(['name' => trim($validated['name']), 'guard_name' => 'web']);
        $this->forgetPermissionCache();

        return back()->with('success', 'Permission created successfully. Assign it to the appropriate role next.');
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $roles = Role::query()->whereIn('id', $validated['role_ids'] ?? [])->get();
        $keepsSuperAdmin = $roles->contains('name', 'Super Admin');

        if (($user->hasRole('Super Admin') || $keepsSuperAdmin) && ! $request->user()->hasRole('Super Admin')) {
            abort(403, 'Only a Super Admin can assign or remove the Super Admin role.');
        }

        if ($user->hasRole('Super Admin') && ! $keepsSuperAdmin && User::role('Super Admin')->count() === 1) {
            return back()->withErrors(['user_role' => 'At least one Super Admin must remain assigned to the system.']);
        }

        $user->syncRoles($roles);
        $this->forgetPermissionCache();

        return back()->with('success', "Roles updated for {$user->name}.");
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
