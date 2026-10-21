@can('student.profile.view-own')
    <a href="{{ route('student.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.dashboard')])><span class="student-nav-icon is-profile"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><span>My Dashboard &amp; Profile</span></a>
@endcan
@can('student.courses.view-own')
    <a href="{{ route('student.courses') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.courses')])><span class="student-nav-icon is-courses"><i class="bi bi-journal-richtext" aria-hidden="true"></i></span><span>My Courses</span></a>
@endcan
@can('student.registration.manage-own')
    <a href="{{ route('student.registration.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.registration.*')])><span class="student-nav-icon is-registration"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span>Course Registration</span></a>
@endcan
@can('student.leaves.manage-own')
    <a href="{{ route('student.leaves.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.leaves.*')])><span class="student-nav-icon is-leaves"><i class="bi bi-calendar2-plus" aria-hidden="true"></i></span><span>Leave Requests</span></a>
@endcan
@can('student.attendance.view-own')
    <a href="{{ route('student.attendance.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.attendance.*')])><span class="student-nav-icon is-attendance"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span><span>My Attendance</span></a>
@endcan
@can('student.assessments.view-own')
    <a href="{{ route('student.assessments.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.assessments.*') || request()->routeIs('student.assessment-submissions.*')])><span class="student-nav-icon is-assessments"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span><span>Assessments &amp; Marks</span></a>
@endcan
@can('student.result.view-own')
    <a href="{{ route('student.results.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.results.*')])><span class="student-nav-icon is-results"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span><span>Results &amp; History</span></a>
    <a href="{{ route('student.transcript') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.transcript')])><span class="student-nav-icon is-transcript"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i></span><span>My Transcript</span></a>
@endcan
@can('notifications.view-own')
    <a href="{{ route('student.notifications.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.notifications.*')])><span class="student-nav-icon is-notifications"><i class="bi bi-bell" aria-hidden="true"></i></span><span>Notifications</span></a>
@endcan
