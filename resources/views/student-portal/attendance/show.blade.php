@extends('student-portal.layout')
@section('heading', $course->course_name ?? $course->course->name)
@section('student-content')
    @include('attendance.partials.policy', ['policy' => $summary['policy']])
    <p><strong>{{ $summary['percentage'] === null ? 'N/A' : $summary['percentage'].'%' }} attendance</strong> · {{ $summary['marks'] ?? 'N/A' }} / {{ $summary['maximum'] }} calculated marks · {{ $summary['valid_sessions'] }} counted sessions</p>
    <div class="table-responsive"><table class="table academic-table">
        <thead><tr><th>Class date</th><th>Session</th><th>Status</th></tr></thead>
        <tbody>@forelse($records as $record)
            <tr><td>{{ $record->session->held_on->format('d M Y') }}</td><td>{{ ucfirst($record->session->type) }} · Slot {{ $record->session->slot }}</td><td>{{ ucfirst($record->status) }}</td></tr>
        @empty<tr><td colspan="3" class="academic-empty">No completed attendance records yet.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $records->links('pagination::bootstrap-5') }}
@endsection
