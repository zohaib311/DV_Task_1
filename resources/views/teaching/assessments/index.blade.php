@extends('teaching.layout')
@section('liveRefresh', 'true')
@section('heading', 'Assessments & marks')
@section('description', 'Plan assessments, enter marks and submit your assigned classes for review.')
@section('teaching-content')
    <div class="table-responsive"><table class="table academic-table align-middle">
        <thead><tr><th>Course</th><th>Term / Section</th><th>Scheme</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>@forelse($offerings as $offering)
            <tr><td>{{ $offering->course_name }}<small>{{ $offering->course_code }}</small></td><td>{{ $offering->term->name }}<small>{{ $offering->section->name }}</small></td><td>{{ $offering->assessment_scheme_approved_at ? 'Approved' : 'Awaiting admin setup' }}</td><td>@include('teaching.partials.status', ['status' => $offering->status])</td><td><a href="{{ route('teaching.assessments.show', $offering) }}">Open assessments</a></td></tr>
        @empty<tr><td colspan="5" class="academic-empty">No assigned offerings.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $offerings->links('pagination::bootstrap-5') }}
@endsection
