@can('student.profile.view-own')<a href="{{ route('student.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.dashboard')])>My Dashboard &amp; Profile</a>@endcan
@can('student.courses.view-own')<a href="{{ route('student.courses') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.courses')])>My Courses</a>@endcan
@can('student.attendance.view-own')<a href="{{ route('student.attendance.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.attendance.*')])>My Attendance</a>@endcan
@can('student.assessments.view-own')<a href="{{ route('student.assessments.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.assessments.*')])>Released Marks</a>@endcan
@can('student.result.view-own')<a href="{{ route('student.results.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('student.results.*')])>Results &amp; History</a>@endcan
