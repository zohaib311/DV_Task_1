@extends('student-portal.layout')
@section('heading', 'My attendance')
@section('description', 'Track completed class sessions, attendance percentage, and calculated attendance marks.')
@section('student-content')
    <p class="academic-note portal-note-with-icon"><i class="bi bi-info-circle" aria-hidden="true"></i><span>These are live attendance totals, not your published semester result. Only completed, non-cancelled sessions in your saved attendance roster are counted.</span></p>
    <div class="portal-section-heading"><div><h2>Attendance by course</h2><p>Open a course to review its completed session history</p></div></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table align-middle">
        <thead><tr><th>Course</th><th>Semester / Term</th><th>Counted sessions</th><th>Attendance</th><th>Calculated marks</th><th>Details</th></tr></thead>
        <tbody>@forelse($courses as $course)
            @php($summary = $summaries[$course->id])
            @php($attendanceVariant = $summary['percentage'] === null ? '' : ($summary['percentage'] >= 75 ? 'is-success' : ($summary['percentage'] >= 50 ? 'is-warning' : 'is-danger')))
            <tr><td class="portal-course-cell"><strong>{{ $course->course_name ?? $course->course->name }}</strong><small>{{ $course->course_code ?? $course->course->code }}</small></td><td><strong>{{ $course->enrollment->semester }}</strong><small>{{ $course->offering?->term->name ?? 'Historical enrollment' }}</small></td><td><span class="portal-badge is-info">{{ $summary['valid_sessions'] }} sessions</span></td><td>@if($summary['percentage'] === null)<span class="portal-badge">N/A</span>@else<div class="portal-attendance-value"><div class="portal-mini-progress" aria-hidden="true"><span class="{{ $attendanceVariant }}" style="width: {{ min(100, $summary['percentage']) }}%"></span></div><span class="portal-badge {{ $attendanceVariant }}">{{ $summary['percentage'] }}%</span></div>@endif</td><td><strong>{{ $summary['marks'] ?? 'N/A' }}</strong><small>out of {{ $summary['maximum'] }}</small></td><td><a class="portal-btn is-outline is-sm" href="{{ route('student.attendance.show', $course) }}"><i class="bi bi-calendar2-week" aria-hidden="true"></i> View sessions</a></td></tr>
        @empty<tr class="portal-empty-row"><td colspan="6"><div class="portal-empty-state"><i class="bi bi-calendar2-x" aria-hidden="true"></i><strong>No course enrollments yet</strong><span>Contact your academic administrator if your active courses are missing.</span></div></td></tr>@endforelse</tbody>
    </table></div>
    <div class="portal-pagination">{{ $courses->links('pagination::bootstrap-5') }}</div>
@endsection
