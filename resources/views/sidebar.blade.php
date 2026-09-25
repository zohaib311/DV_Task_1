<div class="sidebar d-flex flex-column shadow" id="appSidebar">

    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="sidebar-logo-icon">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>
            <h4 class="mb-0">Dashboard</h4>
        </div>

        <button type="button" class="btn-close-sidebar d-lg-none" id="sidebarCloseBtn" aria-label="Close sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <ul class="nav nav-pills flex-column gap-2 px-3 flex-grow-1">

        <li class="nav-item">
            <a href="{{ route('dashboardView') }}"
                class="sidebar-link nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill me-2 fs-5"></i>
                <span class="fw-bold">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allStudents') }}"
                class="sidebar-link nav-link {{ request()->is('student*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard me-2 fs-5"></i>
                <span>Students</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allTeachers') }}"
                class="sidebar-link nav-link {{ request()->is('teacher*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-2 fs-5"></i>
                <span>Teachers</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allCourses') }}"
                class="sidebar-link nav-link {{ request()->is('course*') ? 'active' : '' }}">
                <i class="bi bi-book me-2 fs-5"></i>
                <span>Courses</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allEvents') }}"
                class="sidebar-link nav-link {{ request()->is('event*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-2 fs-5"></i>
                <span>Events</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allDepartments') }}"
                class="sidebar-link nav-link {{ request()->is('department*') ? 'active' : '' }}">
                <i class="bi bi-building me-2 fs-5"></i>
                <span>Departments</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allSections') }}"
                class="sidebar-link nav-link {{ request()->is('section*') ? 'active' : '' }}">
                <i class="bi-bar-chart-steps me-2 fs-5"></i>
                <span>Sections</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('addResult') }}"
                class="sidebar-link nav-link {{ request()->is('result*') ? 'active' : '' }}">
                <i class="bi bi-window-stack me-2 fs-5"></i>
                <span>Results</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('allUsers') }}"
                class="sidebar-link nav-link {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-people me-2 fs-5"></i>
                <span>Users</span>
            </a>
        </li>

    </ul>

    <div class="sidebar-footer mt-auto p-3 ">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('storage/images/' . (auth()->user()->image ?? 'default-user.png')) }}"
                alt="{{ auth()->user()->name ?? 'User' }}" class="sidebar-user-avatar">
            <div class="sidebar-user-info text-truncate">
                <div class="user-name text-truncate">{{ auth()->user()->name ?? 'Guest' }}</div>
                <small class="text-muted text-truncate d-block">{{ auth()->user()->email ?? '' }}</small>
            </div>
        </div>
    </div>

</div>
