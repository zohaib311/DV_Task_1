@extends('student-portal.layout')
@section('heading', $course->course_code.' — Assessment marks')
@section('student-content')
    <h2>{{ $course->course_name }}</h2><p>{{ $course->enrollment->semester }} · {{ $course->enrollment->academic_year }}</p>
    @if(!$item)<p class="academic-note">Marks have not been released for this course yet.</p>
    @else
        <div class="academic-note mb-3">Published course total: {{ $item->obtained_marks }} / {{ $item->total_marks }}</div>
        @include('student-portal.results.breakdown')
        @if($item->assessment_snapshot)
            <div class="table-responsive mt-4"><table class="table academic-table"><thead><tr><th>Assessment</th><th>Component</th><th>Date</th><th>Raw marks</th><th>Course contribution</th></tr></thead><tbody>@foreach($item->assessment_snapshot['student']['assessments'] as $assessment)<tr><td>{{ $assessment['title'] }}</td><td>{{ ucfirst($assessment['component']) }}</td><td>{{ $assessment['held_on'] }}</td><td>{{ $assessment['obtained'] }} / {{ $assessment['maximum'] }}</td><td>{{ number_format($assessment['obtained'] / $assessment['maximum'] * $assessment['weight'], 2) }} / {{ $assessment['weight'] }}</td></tr>@endforeach</tbody></table></div>
            @if($attendance = $item->assessment_snapshot['student']['attendance'] ?? null)<p>Published attendance snapshot: {{ $attendance['percentage'] }}% · {{ $attendance['valid_sessions'] }} counted sessions.</p>@endif
        @else<p class="text-muted mt-3">Individual assessment records are not available for this historical result.</p>@endif
    @endif
@endsection
