<div class="sidebar d-none d-md-flex flex-column shadow">

    <div class="sidebar-header">
        <h4 class="mb-0">
            Dashboard
        </h4>
    </div>

    <ul class="nav nav-pills flex-column gap-2 px-3">

        <li class="nav-item">
            <a href="{{ route('allUsers') }}" class="sidebar-link nav-link">
                <span>All Students</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allTeachers') }}" class="sidebar-link nav-link">
                <span>All Teachers</span>
            </a>
        </li>

    </ul>

    <div class="sidebar-footer mt-auto p-3">

        <hr>

        <div class="small text-muted mb-1">
            Logged in as
        </div>

        <div class="user-name">
            <i class="bi bi-person-circle me-2"></i>
            {{ auth()->user()->name ?? 'Guest' }}
        </div>

    </div>

</div>
