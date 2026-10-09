@extends('student-portal.layout')
@section('heading', $result->enrollment->semester.' — Published result')
@section('description', 'Your official published course grades, assessment totals, SGPA, and cumulative GPA.')
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('student.results.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Result history</a>@endsection
@section('student-content')
    <div class="portal-card portal-result-card mb-3"><span class="portal-stat-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><div class="portal-result-main"><strong>{{ $student->name }}</strong><small>{{ $student->registration_no }} · {{ $result->enrollment->academic_year }}</small></div><span class="portal-badge {{ $result->status === 'Pass' ? 'is-success' : 'is-danger' }}">{{ $result->status }}</span></div>
    <div class="portal-result-summary">
        <div><small>Published</small><strong>{{ $result->published_at->format('d M Y') }}</strong></div>
        <div><small>Percentage</small><strong>{{ $result->semester_percentage }}%</strong></div>
        <div><small>SGPA</small><strong>{{ $result->sgpa }}</strong></div>
        <div><small>CGPA</small><strong>{{ $result->cgpa }}</strong></div>
        <div><small>Record state</small><span class="portal-badge is-success"><i class="bi bi-patch-check" aria-hidden="true"></i>Published</span></div>
    </div>
    <div class="portal-section-heading"><div><h2>Course results</h2><p>Published component totals and grade outcomes</p></div><span class="portal-badge is-info">{{ $result->items->count() }} courses</span></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Credits</th><th>Assessment breakdown</th><th>Total</th><th>%</th><th>Grade</th><th>GP</th><th>Outcome</th></tr></thead><tbody>@foreach($result->items as $item)<tr><td class="portal-course-cell"><strong>{{ $item->course_name }}</strong><small>{{ $item->course_code }}</small></td><td>{{ number_format((float) $item->credit_hours, 1) }}</td><td>@include('student-portal.results.breakdown')</td><td><strong>{{ $item->obtained_marks }}</strong> / {{ $item->total_marks }}</td><td>{{ $item->percentage }}%</td><td><span class="portal-badge">{{ $item->grade }}</span></td><td>{{ $item->grade_point }}</td><td><span class="portal-badge {{ $item->status === 'Pass' ? 'is-success' : 'is-danger' }}">{{ $item->status }}</span></td></tr>@endforeach</tbody></table></div>
    <p class="academic-note portal-note-with-icon mt-3"><i class="bi bi-lock" aria-hidden="true"></i><span>This sheet uses saved published marks, not live teacher entries. Authorized corrections may update it; contact your academic office with any questions.</span></p>
@endsection
