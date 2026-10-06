@extends('academic.layout')
@section('liveRefresh', 'true')
@section('heading', 'Assessment review queue')
@section('description', 'Review submitted course marks. Approval here does not publish a semester result.')
@section('academic-content')
    <div class="table-responsive"><table class="table academic-table align-middle">
        <thead><tr><th>Course</th><th>Term / Section</th><th>Submitted by</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>@forelse($submissions as $submission)
            <tr><td>{{ $submission->offering->course_code }}<small>Submission #{{ $submission->id }}</small></td><td>{{ $submission->offering->term->name }}<small>{{ $submission->offering->section->name }}</small></td><td>{{ $submission->submitter?->name ?? 'Former account' }}</td><td>{{ ucfirst($submission->status) }}</td><td><a href="{{ route('academic.assessment-reviews.show', $submission) }}">Review snapshot</a></td></tr>
        @empty<tr><td colspan="5" class="academic-empty">No submitted course marks.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $submissions->links('pagination::bootstrap-5') }}
@endsection
