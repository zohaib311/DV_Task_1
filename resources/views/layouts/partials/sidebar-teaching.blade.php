@can('offerings.view-assigned')
@if(auth()->user()->teacherProfile && !auth()->user()->can('offerings.manage'))
    <li class="nav-item">
        <details class="sidebar-group" @if(request()->routeIs('teaching.*')) open @endif>
            <summary class="sidebar-link">
                <i class="bi bi-person-workspace me-2 fs-5" aria-hidden="true"></i>
                <span>Teacher Panel</span>
                <i class="bi bi-chevron-down sidebar-group-arrow" aria-hidden="true"></i>
            </summary>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('teaching.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.dashboard')]) @if(request()->routeIs('teaching.dashboard')) aria-current="page" @endif>
                        <i class="bi bi-speedometer2" aria-hidden="true"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teaching.offerings.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.offerings.*')]) @if(request()->routeIs('teaching.offerings.*')) aria-current="page" @endif>
                        <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                        <span>My Courses &amp; Rosters</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teaching.timetable.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.timetable.*')]) @if(request()->routeIs('teaching.timetable.*')) aria-current="page" @endif>
                        <i class="bi bi-calendar-week" aria-hidden="true"></i>
                        <span>My Schedule</span>
                    </a>
                </li>
                @can('attendance.manage-assigned')
                    <li>
                        <a href="{{ route('teaching.attendance.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.attendance.*')]) @if(request()->routeIs('teaching.attendance.*')) aria-current="page" @endif>
                            <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                @endcan
                @canany(['assessments.manage-assigned', 'marks.manage-assigned', 'results.submit'])
                    <li>
                        <a href="{{ route('teaching.assessments.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.assessments.*')])>
                            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                            <span>Assessments &amp; Marks</span>
                        </a>
                    </li>
                @endcanany
                @can('leaves.review-assigned')
                    <li>
                        <a href="{{ route('teaching.leaves.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.leaves.*')]) @if(request()->routeIs('teaching.leaves.*')) aria-current="page" @endif>
                            <i class="bi bi-envelope-paper" aria-hidden="true"></i>
                            <span>Leave Requests</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </details>
    </li>
@endif
@endcan