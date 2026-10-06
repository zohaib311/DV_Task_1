@extends('student-portal.layout')
@section('heading', 'My Assessments & Marks')
@section('student-content')
    <p class="academic-note">Open a course to submit assigned work, follow deadlines, and view marks released by your teacher. Published result history remains available separately.</p>
    <div class="table-responsive"><table class="table academic-table"><thead><tr><th>Course</th><th>Semester / year</th><th>Assessments</th></tr></thead><tbody>@forelse($courses as $course)<tr><td>{{ $course->course_name }}<small>{{ $course->course_code }}</small></td><td>{{ $course->enrollment->semester }} · {{ $course->enrollment->academic_year }}</td><td><a href="{{ route('student.assessments.show', $course) }}">Open assessments</a></td></tr>@empty<tr><td colspan="3" class="academic-empty">No registered courses yet.</td></tr>@endforelse</tbody></table></div>
    {{ $courses->links('pagination::bootstrap-5') }}
@endsection
