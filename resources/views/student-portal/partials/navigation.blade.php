@can('student.profile.view-own')
    <li>
        <a href="{{ route('student.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.dashboard')])>
            <i class="bi bi-speedometer2" aria-hidden="true"></i>
            <span>My Dashboard &amp; Profile</span>
        </a>
    </li>
@endcan
@can('student.courses.view-own')
    <li>
        <a href="{{ route('student.courses') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.courses')])>
            <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
            <span>My Courses</span>
        </a>
    </li>
    <li>
        <a href="{{ route('student.timetable.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.timetable.*')])>
            <i class="bi bi-calendar-week" aria-hidden="true"></i>
            <span>My Timetable</span>
        </a>
    </li>
@endcan
@can('student.registration.manage-own')
    <li>
        <a href="{{ route('student.registration.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.registration.*')])>
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <span>Course Registration</span>
        </a>
    </li>
@endcan
@can('student.leaves.manage-own')
    <li>
        <a href="{{ route('student.leaves.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.leaves.*')])>
            <i class="bi bi-calendar2-plus" aria-hidden="true"></i>
            <span>Leave Requests</span>
        </a>
    </li>
@endcan
@can('student.attendance.view-own')
    <li>
        <a href="{{ route('student.attendance.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.attendance.*')])>
            <i class="bi bi-calendar2-check" aria-hidden="true"></i>
            <span>My Attendance</span>
        </a>
    </li>
@endcan
@can('student.assessments.view-own')
    <li>
        <a href="{{ route('student.assessments.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.assessments.*') || request()->routeIs('student.assessment-submissions.*')])>
            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
            <span>Assessments &amp; Marks</span>
        </a>
    </li>
@endcan
@can('student.result.view-own')
    <li>
        <a href="{{ route('student.results.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.results.*')])>
            <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
            <span>Results &amp; History</span>
        </a>
    </li>
    <li>
        <a href="{{ route('student.transcript') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.transcript')])>
            <i class="bi bi-file-earmark-medical" aria-hidden="true"></i>
            <span>My Transcript</span>
        </a>
    </li>
@endcan
@can('student.fees.view-own')
    <li>
        <a href="{{ route('student.fees.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.fees.*')])>
            <i class="bi bi-wallet2" aria-hidden="true"></i>
            <span>Fees &amp; Payments</span>
        </a>
    </li>
@endcan
@can('student.admit-card.view-own')
    <li>
        <a href="{{ route('student.admit-card.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.admit-card.*')])>
            <i class="bi bi-card-checklist" aria-hidden="true"></i>
            <span>Admit Card</span>
        </a>
    </li>
@endcan
@can('notifications.view-own')
    <li>
        <a href="{{ route('student.notifications.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.notifications.*')])>
            <i class="bi bi-bell" aria-hidden="true"></i>
            <span>Notifications</span>
        </a>
    </li>
@endcan
