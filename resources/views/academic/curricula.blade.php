@extends('academic.layout')
@section('title', 'Semester Curriculum')
@section('heading', 'Semester Curriculum')
@section('description', 'Define each department’s semester plan with required courses, electives, and approved assessment schemes.')
@section('header-actions')<a href="{{ route('academic.curricula.create') }}" class="btn academic-header-btn"><i class="bi bi-plus-lg"></i> New curriculum</a>@endsection
@section('academic-content')
    <p class="academic-note"><i class="bi bi-shield-check"></i> Approved versions retain their course and assessment details. Create a new version when the academic plan changes.</p>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Department</th><th>Semester</th><th>Version</th><th>Courses</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($curricula as $curriculum)<tr><td class="fw-semibold">{{ $curriculum->department->name }}</td><td>{{ $curriculum->semester }}</td><td>{{ $curriculum->version }}</td><td>{{ $curriculum->courses_count }}</td><td><span class="academic-badge {{ $curriculum->status === 'approved' ? 'is-active' : '' }}">{{ ucfirst($curriculum->status) }}</span></td><td><a href="{{ route('academic.curricula.edit', $curriculum) }}">{{ $curriculum->status === 'approved' ? 'View plan' : 'Edit & approve' }}</a></td></tr>
        @empty<tr><td colspan="6" class="academic-empty">No curriculum defined. Create the first department-semester course plan.</td></tr>@endforelse
    </tbody></table></div>{{ $curricula->links() }}
@endsection
