@extends('welcome')

@section('title', 'Access Control')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/access-control.css') }}">
@endsection

@section('content')
    <div class="container py-5 access-control-page">
        <section class="access-control-shell">
            <header class="access-control-header">
                <div>
                    <span class="access-control-kicker"><i class="bi bi-shield-lock-fill"></i> ADMINISTRATION</span>
                    <h1>Roles &amp; Permissions</h1>
                    <p>Control who can manage academic records, enrollments, and future teaching workflows.</p>
                </div>
                <div class="access-control-stat">
                    <strong>{{ $users->count() }}</strong>
                    <span>User accounts</span>
                </div>
            </header>

            @if (session('success'))
                <div class="alert alert-success access-control-alert mb-4" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger access-control-alert mb-4" role="alert">
                    <strong>Please review the access-control changes.</strong>
                    <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="access-control-toolbar">
                <button class="btn access-primary-btn" type="button" data-bs-toggle="collapse" data-bs-target="#newRoleForm">
                    <i class="bi bi-person-gear"></i> Create Role
                </button>
                <button class="btn access-secondary-btn" type="button" data-bs-toggle="collapse" data-bs-target="#newPermissionForm">
                    <i class="bi bi-plus-circle"></i> Add Permission
                </button>
                <span>{{ $roles->count() }} roles · {{ $permissions->count() }} permissions</span>
            </div>

            <div class="collapse" id="newRoleForm">
                <form class="access-inline-form" method="POST" action="{{ route('access.roles.store') }}">
                    @csrf
                    <div class="access-form-heading"><i class="bi bi-person-plus"></i><span>Create a role</span></div>
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4"><label class="form-label" for="role_name">Role name</label><input class="form-control" id="role_name" name="name" placeholder="e.g. Examination Officer" required></div>
                        <div class="col-lg-6"><label class="form-label">Permissions</label><div class="access-checkbox-list">@foreach ($permissions as $permission)<label><input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"> <span>{{ $permission->name }}</span></label>@endforeach</div></div>
                        <div class="col-lg-2"><button class="btn access-primary-btn w-100" type="submit">Save Role</button></div>
                    </div>
                </form>
            </div>

            <div class="collapse" id="newPermissionForm">
                <form class="access-inline-form access-permission-form" method="POST" action="{{ route('access.permissions.store') }}">
                    @csrf
                    <div><div class="access-form-heading"><i class="bi bi-key-fill"></i><span>Add a permission</span></div><p>Use lowercase dot notation, for example <code>reports.export</code>.</p></div>
                    <div class="d-flex gap-2"><input class="form-control" name="name" placeholder="reports.export" required><button class="btn access-secondary-btn text-nowrap" type="submit">Add Permission</button></div>
                </form>
            </div>

            <section class="access-section">
                <div class="access-section-heading"><div><span>ROLE DEFINITIONS</span><h2>What each role can do</h2></div><p>Permissions are assigned to roles; users receive access through their role.</p></div>
                <div class="access-role-list">
                    @foreach ($roles as $role)
                        <details class="access-role-row" {{ $loop->first ? 'open' : '' }}>
                            <summary>
                                <div><span class="access-role-icon"><i class="bi bi-shield-check"></i></span><strong>{{ $role->name }}</strong><small>{{ $role->users_count }} assigned user{{ $role->users_count === 1 ? '' : 's' }}</small></div>
                                <span class="access-role-count">{{ $role->name === 'Super Admin' ? 'Full access' : $role->permissions->count().' permissions' }} <i class="bi bi-chevron-down"></i></span>
                            </summary>
                            <div class="access-role-editor">
                                @if ($role->name === 'Super Admin')
                                    <p class="access-locked-note"><i class="bi bi-lock-fill"></i> Super Admin bypasses individual checks and always retains full system access.</p>
                                @else
                                    <form method="POST" action="{{ route('access.roles.update', $role) }}">
                                        @csrf @method('PUT')
                                        <div class="access-permission-grid">
                                            @foreach ($permissions as $permission)
                                                <label class="access-permission-choice"><input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))><span>{{ $permission->name }}</span></label>
                                            @endforeach
                                        </div>
                                        <div class="access-row-actions"><button class="btn access-secondary-btn" type="submit"><i class="bi bi-check2"></i> Save permissions</button></div>
                                    </form>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>

            <section class="access-section access-user-section">
                <div class="access-section-heading"><div><span>USER ASSIGNMENTS</span><h2>Assign system roles</h2></div><p>Each person may have more than one role when required.</p></div>
                <div class="table-responsive">
                    <table class="table access-user-table align-middle mb-0">
                        <thead><tr><th>User</th><th>Profile link</th><th>Current roles</th><th class="text-end">Manage</th></tr></thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td><div class="access-user-name"><img src="{{ asset('storage/images/' . ($user->image ?: 'default-user.png')) }}" alt=""><div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div></div></td>
                                    <td><span class="access-profile-link">{{ $user->studentProfile ? 'Student profile' : ($user->teacherProfile ? 'Teacher profile' : 'Not linked') }}</span></td>
                                    <td><div class="access-role-tags">@forelse ($user->roles as $role)<span>{{ $role->name }}</span>@empty<span class="is-empty">No role assigned</span>@endforelse</div></td>
                                    <td class="text-end"><button type="button" class="btn access-link-btn" data-bs-toggle="collapse" data-bs-target="#userRoles{{ $user->id }}">Edit roles <i class="bi bi-chevron-down"></i></button></td>
                                </tr>
                                <tr class="collapse" id="userRoles{{ $user->id }}"><td colspan="4"><form method="POST" action="{{ route('access.users.roles.update', $user) }}" class="access-user-role-form">@csrf @method('PUT')<div class="access-role-checks">@foreach ($roles as $role)<label><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked($user->roles->contains('id', $role->id))> {{ $role->name }}</label>@endforeach</div><button class="btn access-primary-btn" type="submit">Update {{ $user->name }}’s roles</button></form></td></tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">No user accounts are available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </div>
@endsection
