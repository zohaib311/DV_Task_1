@extends('academic.layout')
@section('liveRefresh', 'true')
@section('title', 'Course Offerings')
@section('heading', 'Course Offerings')
@section('description', 'Prepare all semester classes once, then student enrollment assigns the required courses automatically.')
@section('header-actions')<a href="{{ route('academic.teaching-setup.create') }}" class="btn academic-header-btn"><i class="bi bi-diagram-3"></i> Prepare semester classes</a><a href="{{ route('academic.offerings.create') }}" class="btn academic-header-btn"><i class="bi bi-plus-lg"></i> Single offering</a>@endsection
@section('academic-content')
    <form class="academic-filter" method="GET"><label for="term_filter" class="form-label">Teaching term</label><div class="d-flex gap-2"><select id="term_filter" name="term_id" class="form-select"><option value="">All terms</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }}</option>@endforeach</select><button class="btn btn-light">Filter</button></div></form>
    <div class="academic-offering-legend"><i class="bi bi-info-circle" aria-hidden="true"></i><span><strong>Course status</strong> shows the teaching and result lifecycle. <strong>Assessment scheme</strong> shows whether the marking allocation has been approved and permanently locked.</span></div>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Term / Section</th><th>Curriculum</th><th>Teachers</th><th>Enrolled</th><th>Course status</th><th>Assessment scheme</th><th></th></tr></thead><tbody>
        @forelse($offerings as $offering)<tr>
            <td><strong>{{ $offering->course_name }}</strong><small>{{ $offering->course_code }} · {{ $offering->credit_hours }} credits</small></td>
            <td>{{ $offering->term->name }}<small>{{ $offering->program?->code ?? 'Legacy' }} · {{ $offering->department->name }} / {{ $offering->section->name }}</small></td>
            <td>{{ $offering->semester }}<small>{{ $offering->curriculumCourse->curriculum->version }}</small></td>
            <td>{{ $offering->teachers->pluck('name')->implode(', ') }}</td><td>{{ $offering->enrollment_courses_count }}</td>
            <td><span class="academic-badge {{ $offering->status === 'active' ? 'is-active' : '' }}">{{ ucfirst(str_replace('_', ' ', $offering->status)) }}</span></td>
            <td>
                @if($offering->assessment_scheme_approved_at)
                    <div class="assessment-scheme-state is-approved"><span class="academic-badge"><i class="bi bi-lock-fill" aria-hidden="true"></i> Approved - Locked</span><small>{{ $offering->assessmentComponents->map(fn ($component) => ucfirst($component->code).' '.$component->allocation)->implode(' - ') }}</small><small>Approved {{ $offering->assessment_scheme_approved_at->format('d M Y') }}</small></div>
                @else
                    <div class="assessment-scheme-state is-pending"><span class="academic-badge"><i class="bi bi-unlock" aria-hidden="true"></i> Not approved</span><small>Teacher assessments remain unavailable until approval.</small></div>
                @endif
            </td>
            <td><a href="{{ route('academic.offerings.edit', $offering) }}">{{ $offering->status === 'planned' ? 'Manage' : 'View' }}</a><a class="d-block mt-2" href="{{ route('academic.assessment-schemes.edit', $offering) }}">{{ $offering->assessment_scheme_approved_at ? 'View locked scheme' : 'Approve scheme' }}</a></td>
        </tr>@empty<tr><td colspan="8" class="academic-empty">No course offerings yet. Approve a curriculum, then assign its courses to a term, section, and teacher.</td></tr>@endforelse
    </tbody></table></div>{{ $offerings->links() }}
@endsection
