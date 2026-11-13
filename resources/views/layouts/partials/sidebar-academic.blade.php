@canany(['terms.manage', 'curriculum.manage', 'offerings.manage', 'assessments.review', 'reports.view', 'audit.view', 'timetable.manage'])
    <li class="nav-item">
        <details class="sidebar-group" @if(request()->routeIs('academic.*')) open @endif>
            <summary class="sidebar-link">
                <i class="bi bi-journal-richtext me-2 fs-5" aria-hidden="true"></i>
                <span>Academic Setup</span>
                <i class="bi bi-chevron-down sidebar-group-arrow" aria-hidden="true"></i>
            </summary>
            <ul class="sidebar-submenu">
                @can('timetable.manage')
                    <li>
                        <a href="{{ route('academic.timetable.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.timetable.*')]) @if(request()->routeIs('academic.timetable.*')) aria-current="page" @endif>
                            <i class="bi bi-calendar-week" aria-hidden="true"></i>
                            <span>Timetable &amp; Schedule</span>
                        </a>
                    </li>
                @endcan
                @can('assessments.review')
                    <li>
                        <a href="{{ route('academic.assessment-reviews.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.assessment-reviews.*')])>
                            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                            <span>Assessment Reviews</span>
                        </a>
                    </li>
                @endcan
                @can('reports.view')
                    <li>
                        <a href="{{ route('academic.reports.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.reports.*')])>
                            <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
                            <span>Academic Reports</span>
                        </a>
                    </li>
                @endcan
                @can('audit.view')
                    <li>
                        <a href="{{ route('academic.audits.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.audits.*')])>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>Audit Viewer</span>
                        </a>
                    </li>
                @endcan
                @can('terms.manage')
                    <li>
                        <a href="{{ route('academic.terms.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.terms.*')]) @if(request()->routeIs('academic.terms.*')) aria-current="page" @endif>
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <span>Academic Terms</span>
                        </a>
                    </li>
                @endcan
                @can('curriculum.manage')
                    <li>
                        <a href="{{ route('academic.programs.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.programs.*')])>
                            <i class="bi bi-mortarboard" aria-hidden="true"></i>
                            <span>Programs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('academic.curricula.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.curricula.*')]) @if(request()->routeIs('academic.curricula.*')) aria-current="page" @endif>
                            <i class="bi bi-diagram-3" aria-hidden="true"></i>
                            <span>8-Semester Program Plan</span>
                        </a>
                    </li>
                @endcan
                @can('offerings.manage')
                    <li>
                        <a href="{{ route('academic.teaching-setup.create') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.teaching-setup.*')])>
                            <i class="bi bi-gear-wide-connected" aria-hidden="true"></i>
                            <span>Prepare Semester Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('academic.offerings.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.offerings.*')]) @if(request()->routeIs('academic.offerings.*')) aria-current="page" @endif>
                            <i class="bi bi-collection-play" aria-hidden="true"></i>
                            <span>Semester Classes</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </details>
    </li>
@endcanany