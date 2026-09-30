@extends('student-portal.layout')
@section('heading', 'My attendance')
@section('student-content')
    <div class="table-responsive"><table class="table academic-table align-middle">
        <thead><tr><th>Course</th><th>Semester / Term</th><th>Counted sessions</th><th>Attendance</th><th>Calculated marks</th><th>Details</th></tr></thead>
        <tbody>@forelse($courses as $course)
            @php($summary = $summaries[$course->id])
            <tr><td>{{ $course->course_name ?? $course->course->name }}<small>{{ $course->course_code ?? $course->course->code }}</small></td><td>{{ $course->enrollment->semester }}<small>{{ $course->offering?->term->name ?? 'Historical enrollment' }}</small></td><td>{{ $summary['valid_sessions'] }}</td><td>{{ $summary['percentage'] === null ? 'N/A' : $summary['percentage'].'%' }}</td><td>{{ $summary['marks'] ?? 'N/A' }} / {{ $summary['maximum'] }}</td><td><a href="{{ route('student.attendance.show', $course) }}">View sessions</a></td></tr>
        @empty<tr><td colspan="6" class="academic-empty">No course enrollments yet. Contact your academic administrator.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $courses->links('pagination::bootstrap-5') }}
    <p class="academic-note">These are live attendance totals, not your published semester result. Only completed, non-cancelled sessions in your saved attendance roster are counted.</p>
@endsection
