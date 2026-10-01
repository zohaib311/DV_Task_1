@extends('student-portal.layout')
@section('heading', 'My released assessment marks')
@section('student-content')
    <p class="academic-note">Assessment marks are released with your published semester result. Teacher drafts and pending submissions are not visible.</p>
    <div class="table-responsive"><table class="table academic-table"><thead><tr><th>Course</th><th>Semester / year</th><th>Assessment marks</th></tr></thead><tbody>@forelse($courses as $course)<tr><td>{{ $course->course_name }}<small>{{ $course->course_code }}</small></td><td>{{ $course->enrollment->semester }} · {{ $course->enrollment->academic_year }}</td><td><a href="{{ route('student.assessments.show', $course) }}">View released marks</a></td></tr>@empty<tr><td colspan="3" class="academic-empty">No registered courses yet.</td></tr>@endforelse</tbody></table></div>
    {{ $courses->links('pagination::bootstrap-5') }}
@endsection
