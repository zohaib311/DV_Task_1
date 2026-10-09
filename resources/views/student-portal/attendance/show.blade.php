@extends('student-portal.layout')
@section('heading', $course->course_name ?? $course->course->name)
@section('description', ($course->course_code ?? $course->course->code).' attendance details and completed session history.')
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('student.attendance.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> All attendance</a>@endsection
@section('student-content')
    @include('attendance.partials.policy', ['policy' => $summary['policy']])
    <div class="portal-attendance-summary" aria-label="Attendance summary">
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-percent" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Attendance</small><strong>{{ $summary['percentage'] === null ? 'N/A' : $summary['percentage'].'%' }}</strong></div></div>
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-star" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Calculated marks</small><strong>{{ $summary['marks'] ?? 'N/A' }} / {{ $summary['maximum'] }}</strong></div></div>
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Counted sessions</small><strong>{{ $summary['valid_sessions'] }}</strong></div></div>
    </div>
    <div class="portal-section-heading"><div><h2>Completed sessions</h2><p>Draft and cancelled sessions are not shown</p></div></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table">
        <thead><tr><th>Class date</th><th>Session</th><th>Status</th></tr></thead>
        <tbody>@forelse($records as $record)
            @php($statusClass = match($record->status) {'present' => 'is-success', 'absent' => 'is-danger', 'late' => 'is-warning', 'excused' => 'is-info', default => ''})
            <tr><td><strong>{{ $record->session->held_on->format('d M Y') }}</strong></td><td>{{ ucfirst($record->session->type) }}<small>Slot {{ $record->session->slot }}</small></td><td><span class="portal-badge {{ $statusClass }}">{{ ucfirst($record->status) }}</span></td></tr>
        @empty<tr class="portal-empty-row"><td colspan="3"><div class="portal-empty-state"><i class="bi bi-calendar2" aria-hidden="true"></i><strong>No completed attendance records yet</strong><span>Completed class sessions will appear here.</span></div></td></tr>@endforelse</tbody>
    </table></div>
    <div class="portal-pagination">{{ $records->links('pagination::bootstrap-5') }}</div>
@endsection
