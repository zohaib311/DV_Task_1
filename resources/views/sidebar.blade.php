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

        @can('dashboard.view')
        <li class="nav-item">
            <a href="{{ route('dashboardView') }}"
                class="sidebar-link nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill me-2 fs-5"></i>
                <span class="fw-bold">Dashboard</span>
            </a>
        </li>
        @endcan

        @can('students.manage')
        <li class="nav-item">
            <a href="{{ route('allStudents') }}"
                class="sidebar-link nav-link {{ request()->is('student*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard me-2 fs-5"></i>
                <span>Students</span>
            </a>
        </li>
        @endcan

        @can('enrollments.view')
        <li class="nav-item">
            <a href="{{ route('allEnrollments') }}"
                class="sidebar-link nav-link {{ request()->is('enrollment*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark me-2 fs-5"></i>
                <span>Enrollments</span>
            </a>
        </li>
        @endcan

        @can('teachers.manage')
        <li class="nav-item">
            <a href="{{ route('allTeachers') }}"
                class="sidebar-link nav-link {{ request()->is('teacher*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-2 fs-5"></i>
                <span>Teachers</span>
            </a>
        </li>
        @endcan

        @can('courses.manage')
        <li class="nav-item">
            <a href="{{ route('allCourses') }}"
                class="sidebar-link nav-link {{ request()->is('course*') ? 'active' : '' }}">
                <i class="bi bi-book me-2 fs-5"></i>
                <span>Courses</span>
            </a>
        </li>
        @endcan

        @can('terms.manage')
        <li class="nav-item"><a href="{{ route('academic.terms.index') }}" class="sidebar-link nav-link {{ request()->is('academic/terms*') ? 'active' : '' }}"><i class="bi bi-calendar-range me-2 fs-5"></i><span>Academic Terms</span></a></li>
        @endcan
        @can('curriculum.manage')
        <li class="nav-item"><a href="{{ route('academic.curricula.index') }}" class="sidebar-link nav-link {{ request()->is('academic/curricula*') ? 'active' : '' }}"><i class="bi bi-journal-richtext me-2 fs-5"></i><span>Semester Curriculum</span></a></li>
        @endcan
        @can('offerings.manage')
        <li class="nav-item"><a href="{{ route('academic.offerings.index') }}" class="sidebar-link nav-link {{ request()->is('academic/offerings*') ? 'active' : '' }}"><i class="bi bi-person-video3 me-2 fs-5"></i><span>Course Offerings</span></a></li>
        @endcan

        @can('events.manage')
        <li class="nav-item">
            <a href="{{ route('allEvents') }}"
                class="sidebar-link nav-link {{ request()->is('event*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-2 fs-5"></i>
                <span>Events</span>
            </a>
        </li>
        @endcan

        @can('departments.manage')
        <li class="nav-item">
            <a href="{{ route('allDepartments') }}"
                class="sidebar-link nav-link {{ request()->is('department*') ? 'active' : '' }}">
                <i class="bi bi-building me-2 fs-5"></i>
                <span>Departments</span>
            </a>
        </li>
        @endcan

        @can('sections.manage')
        <li class="nav-item">
            <a href="{{ route('allSections') }}"
                class="sidebar-link nav-link {{ request()->is('section*') ? 'active' : '' }}">
                <i class="bi-bar-chart-steps me-2 fs-5"></i>
                <span>Sections</span>
            </a>
        </li>
        @endcan

        @can('results.view-all')
        <li class="nav-item">
            <a href="{{ route('allResults') }}"
                class="sidebar-link nav-link {{ request()->is('result*') ? 'active' : '' }}">
                <i class="bi bi-window-stack me-2 fs-5"></i>
                <span>Results</span>
            </a>
        </li>
        @endcan

        @can('users.manage')
        <li class="nav-item">
            <a href="{{ route('allUsers') }}"
                class="sidebar-link nav-link {{ request()->is('users*') ? 'active' : '' }}">
                <i class="bi bi-people me-2 fs-5"></i>
                <span>Users</span>
            </a>
        </li>
        @endcan

        @can('roles.view')
        <li class="nav-item">
            <a href="{{ route('access.index') }}"
                class="sidebar-link nav-link {{ request()->is('access-control*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock me-2 fs-5"></i>
                <span>Access Control</span>
            </a>
        </li>
        @endcan

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
