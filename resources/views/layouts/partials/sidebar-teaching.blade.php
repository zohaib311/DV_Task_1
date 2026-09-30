@can('offerings.view-assigned')
    <li class="nav-item">
        <details class="sidebar-group" @if(request()->routeIs('teaching.*')) open @endif>
            <summary class="sidebar-link"><i class="bi bi-person-workspace me-2 fs-5" aria-hidden="true"></i><span>Teacher Panel</span><i class="bi bi-chevron-down sidebar-group-arrow" aria-hidden="true"></i></summary>
            <ul class="sidebar-submenu">
                <li><a href="{{ route('teaching.dashboard') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.dashboard')]) @if(request()->routeIs('teaching.dashboard')) aria-current="page" @endif>Overview</a></li>
                <li><a href="{{ route('teaching.offerings.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('teaching.offerings.*')]) @if(request()->routeIs('teaching.offerings.*')) aria-current="page" @endif>My Courses &amp; Rosters</a></li>
            </ul>
        </details>
    </li>
@endcan
