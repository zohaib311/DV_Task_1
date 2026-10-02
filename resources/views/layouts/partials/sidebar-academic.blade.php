@canany(['terms.manage', 'curriculum.manage', 'offerings.manage', 'assessments.review', 'reports.view', 'audit.view'])
    <li class="nav-item">
        <details class="sidebar-group" @if(request()->routeIs('academic.*')) open @endif>
            <summary class="sidebar-link"><i class="bi bi-journal-richtext me-2 fs-5" aria-hidden="true"></i><span>Academic Setup</span><i class="bi bi-chevron-down sidebar-group-arrow" aria-hidden="true"></i></summary>
            <ul class="sidebar-submenu">
                @can('assessments.review')<li><a href="{{ route('academic.assessment-reviews.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.assessment-reviews.*')])>Assessment Reviews</a></li>@endcan
                @can('reports.view')<li><a href="{{ route('academic.reports.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.reports.*')])>Academic Reports</a></li>@endcan
                @can('audit.view')<li><a href="{{ route('academic.audits.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.audits.*')])>Audit Viewer</a></li>@endcan
                @can('terms.manage')<li><a href="{{ route('academic.terms.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.terms.*')]) @if(request()->routeIs('academic.terms.*')) aria-current="page" @endif>Academic Terms</a></li>@endcan
                @can('curriculum.manage')<li><a href="{{ route('academic.programs.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.programs.*')])>Programs</a></li><li><a href="{{ route('academic.curricula.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.curricula.*')]) @if(request()->routeIs('academic.curricula.*')) aria-current="page" @endif>8-Semester Program Plan</a></li>@endcan
                @can('offerings.manage')<li><a href="{{ route('academic.teaching-setup.create') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.teaching-setup.*')])>Prepare Semester Classes</a></li><li><a href="{{ route('academic.offerings.index') }}" @class(['sidebar-sublink', 'active' => request()->routeIs('academic.offerings.*')]) @if(request()->routeIs('academic.offerings.*')) aria-current="page" @endif>Semester Classes</a></li>@endcan
            </ul>
        </details>
    </li>
@endcanany
