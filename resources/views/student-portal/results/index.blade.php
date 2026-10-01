@extends('student-portal.layout')
@section('heading', 'My results & academic history')
@section('student-content')
    <p class="academic-note">Only published semester results appear here. Drafts and pending reviews are private until released.</p>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Semester</th><th>Academic year</th><th>Percentage</th><th>SGPA</th><th>CGPA</th><th>Outcome</th><th>Result sheet</th></tr></thead><tbody>@forelse($results as $result)<tr><td>{{ $result->enrollment->semester }}</td><td>{{ $result->enrollment->academic_year }}</td><td>{{ $result->semester_percentage }}%</td><td>{{ $result->sgpa }}</td><td>{{ $result->cgpa }}</td><td>{{ $result->status }}</td><td><a href="{{ route('student.results.show', $result) }}">View published sheet</a></td></tr>@empty<tr><td colspan="7" class="academic-empty">No published semester results yet.</td></tr>@endforelse</tbody></table></div>
    {{ $results->links('pagination::bootstrap-5') }}
@endsection
