@extends('student-portal.layout')
@section('heading', $result->enrollment->semester.' — Published result')
@section('student-content')
    <p>{{ $student->name }} · {{ $student->registration_no }} · {{ $result->enrollment->academic_year }}</p>
    <div class="academic-note mb-4">Published {{ $result->published_at->format('d M Y') }} · {{ $result->status }} · SGPA {{ $result->sgpa }} · CGPA {{ $result->cgpa }} · {{ $result->semester_percentage }}%</div>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Credits</th><th>Assessment breakdown</th><th>Total</th><th>%</th><th>Grade</th><th>GP</th><th>Outcome</th></tr></thead><tbody>@foreach($result->items as $item)<tr><td>{{ $item->course_name }}<small>{{ $item->course_code }}</small></td><td>{{ $item->credit_hours }}</td><td>@include('student-portal.results.breakdown')</td><td>{{ $item->obtained_marks }} / {{ $item->total_marks }}</td><td>{{ $item->percentage }}%</td><td>{{ $item->grade }}</td><td>{{ $item->grade_point }}</td><td>{{ $item->status }}</td></tr>@endforeach</tbody></table></div>
    <p class="text-muted">This sheet uses saved published marks, not live teacher entries. Authorized corrections may update it; contact your academic office with any questions.</p>
@endsection
