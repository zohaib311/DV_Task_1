@extends('teaching.layout')
@section('heading', 'Student assessment submission')
@section('description', $assessment->title.' · '.$offering->course_code)
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('teaching.assessments.edit', [$offering, $assessment]) }}">Back to marks</a>@endsection
@section('teaching-content')
    <div class="teaching-scheme mb-4"><strong>{{ $submission->enrollmentCourse->enrollment->student->name }}</strong><span>{{ $submission->enrollmentCourse->enrollment->student->registration_no }}</span><span>Submitted {{ $submission->submitted_at->format('d M Y H:i') }}</span><span>{{ ucfirst($submission->status) }}</span></div>
    <h2>Written response</h2>
    <div class="academic-note mb-4" style="white-space: pre-wrap">{{ $submission->answer_text ?: 'No written response was provided.' }}</div>
    @if($submission->attachment_path)<a class="btn btn-primary" href="{{ route('teaching.assessments.student-submissions.download', [$offering, $assessment, $submission]) }}">Download {{ $submission->original_filename }}</a>@endif
    @if($submission->teacher_feedback)<h2 class="mt-4">Saved feedback</h2><p>{{ $submission->teacher_feedback }}</p>@endif
@endsection
