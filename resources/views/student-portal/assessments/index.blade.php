@extends('student-portal.layout')
@section('liveRefresh', 'true')
@section('heading', 'My Assessments & Marks')
@section('description', 'Submit assigned work, follow deadlines, and view marks released by your teachers.')
@section('student-content')
    <p class="academic-note portal-note-with-icon"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Open a course to submit assigned work, follow deadlines, and view marks released by your teacher. Published result history remains available separately.</span></p>
    <div class="portal-section-heading"><div><h2>Registered courses</h2><p>Select a course to open its assessment activity</p></div></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Semester / year</th><th>Action</th></tr></thead><tbody>@forelse($courses as $course)<tr><td class="portal-course-cell"><strong>{{ $course->course_name }}</strong><small>{{ $course->course_code }}</small></td><td><strong>{{ $course->enrollment->semester }}</strong><small>{{ $course->enrollment->academic_year }}</small></td><td><a class="portal-btn is-outline is-sm" href="{{ route('student.assessments.show', $course) }}"><i class="bi bi-file-earmark-check" aria-hidden="true"></i> Open assessments</a></td></tr>@empty<tr class="portal-empty-row"><td colspan="3"><div class="portal-empty-state"><i class="bi bi-file-earmark-x" aria-hidden="true"></i><strong>No registered courses yet.</strong><span>Your assessments will appear after course registration.</span></div></td></tr>@endforelse</tbody></table></div>
    <div class="portal-pagination">{{ $courses->links('pagination::bootstrap-5') }}</div>
@endsection
