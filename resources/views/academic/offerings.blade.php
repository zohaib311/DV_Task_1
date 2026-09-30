@extends('academic.layout')
@section('title', 'Course Offerings')
@section('heading', 'Course Offerings')
@section('description', 'Connect an approved course to its teaching term, section, and assigned teachers.')
@section('header-actions')<a href="{{ route('academic.offerings.create') }}" class="btn academic-header-btn"><i class="bi bi-plus-lg"></i> New offering</a>@endsection
@section('academic-content')
    <form class="academic-filter" method="GET"><label for="term_filter" class="form-label">Teaching term</label><div class="d-flex gap-2"><select id="term_filter" name="term_id" class="form-select"><option value="">All terms</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }}</option>@endforeach</select><button class="btn btn-light">Filter</button></div></form>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Term / Section</th><th>Curriculum</th><th>Teachers</th><th>Enrolled</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($offerings as $offering)<tr>
            <td><strong>{{ $offering->course_name }}</strong><small>{{ $offering->course_code }} · {{ $offering->credit_hours }} credits</small></td>
            <td>{{ $offering->term->name }}<small>{{ $offering->department->name }} / {{ $offering->section->name }}</small></td>
            <td>{{ $offering->semester }}<small>{{ $offering->curriculumCourse->curriculum->version }}</small></td>
            <td>{{ $offering->teachers->pluck('name')->implode(', ') }}</td><td>{{ $offering->enrollment_courses_count }}</td>
            <td><span class="academic-badge {{ $offering->status === 'active' ? 'is-active' : '' }}">{{ ucfirst(str_replace('_', ' ', $offering->status)) }}</span></td>
            <td><a href="{{ route('academic.offerings.edit', $offering) }}">{{ $offering->status === 'planned' ? 'Manage' : 'View' }}</a></td>
        </tr>@empty<tr><td colspan="7" class="academic-empty">No course offerings yet. Approve a curriculum, then assign its courses to a term, section, and teacher.</td></tr>@endforelse
    </tbody></table></div>{{ $offerings->links() }}
@endsection
