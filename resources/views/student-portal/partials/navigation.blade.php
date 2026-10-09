@can('student.profile.view-own')
    <a href="{{ route('student.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.dashboard')])><i class="bi bi-grid-1x2 portal-nav-icon" aria-hidden="true"></i><span>My Dashboard &amp; Profile</span></a>
@endcan
@can('student.courses.view-own')
    <a href="{{ route('student.courses') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.courses')])><i class="bi bi-journal-bookmark portal-nav-icon" aria-hidden="true"></i><span>My Courses</span></a>
@endcan
@can('student.registration.manage-own')
    <a href="{{ route('student.registration.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.registration.*')])><i class="bi bi-journal-plus portal-nav-icon" aria-hidden="true"></i><span>Course Registration</span></a>
@endcan
@can('student.attendance.view-own')
    <a href="{{ route('student.attendance.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.attendance.*')])><i class="bi bi-calendar2-check portal-nav-icon" aria-hidden="true"></i><span>My Attendance</span></a>
@endcan
@can('student.assessments.view-own')
    <a href="{{ route('student.assessments.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.assessments.*') || request()->routeIs('student.assessment-submissions.*')])><i class="bi bi-file-earmark-check portal-nav-icon" aria-hidden="true"></i><span>Assessments &amp; Marks</span></a>
@endcan
@can('student.result.view-own')
    <a href="{{ route('student.results.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.results.*')])><i class="bi bi-award portal-nav-icon" aria-hidden="true"></i><span>Results &amp; History</span></a>
@endcan
@can('notifications.view-own')
    <a href="{{ route('student.notifications.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.notifications.*')])><i class="bi bi-bell portal-nav-icon" aria-hidden="true"></i><span>Notifications</span></a>
@endcan
