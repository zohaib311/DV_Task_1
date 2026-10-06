@extends('student-portal.layout')
@section('heading', $course->course_code.' — Assessments & Marks')
@section('student-content')
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <h2>{{ $course->course_name }}</h2><p>{{ $course->enrollment->semester }} · {{ $course->enrollment->academic_year }} · {{ $course->enrollment->term?->name }}</p>
    <h2 class="mt-4">Current assessment activity</h2>
    <div class="table-responsive"><table class="table academic-table align-middle">
        <thead><tr><th>Assessment</th><th>Date / deadline</th><th>Submission</th><th>Released marks</th></tr></thead>
        <tbody>@forelse($liveAssessments as $assessment)
            @php($submission = $assessment->studentSubmissions->firstWhere('student_enrollment_course_id', $course->id))
            @php($mark = $assessment->marks->firstWhere('student_enrollment_course_id', $course->id))
            @php($submissionOpen = $assessment->submission_required && $course->enrollment->status === 'active' && $course->enrollment->term?->status === 'active' && $assessment->submissions_due_at && now()->lte($assessment->submissions_due_at))
            <tr>
                <td><strong>{{ $assessment->title }}</strong><small>{{ ucfirst($assessment->component->code) }} · Weight {{ $assessment->weight }}</small>@if($assessment->instructions)<p class="small mt-2 mb-0">{{ $assessment->instructions }}</p>@endif
                    @if($assessment->question_file_path)<p class="small mt-2 mb-0"><a class="btn btn-sm btn-outline-primary" href="{{ route('student.assessments.question-file', [$course, $assessment]) }}"><i class="bi bi-download"></i> Download question file</a><br><span class="text-muted">{{ $assessment->question_original_filename }} · {{ number_format(($assessment->question_file_size ?? 0) / 1024, 1) }} KB</span></p>@endif
                </td>
                <td>{{ $assessment->held_on->format('d M Y') }}@if($assessment->submissions_due_at)<small>Due {{ $assessment->submissions_due_at->format('d M Y H:i') }}</small>@endif</td>
                <td>
                    @if(!$assessment->submission_required)<span class="text-muted">No upload required</span>
                    @else
                        <span>{{ $submission ? ucfirst($submission->status) : ($submissionOpen ? 'Awaiting submission' : 'Closed') }}</span>
                        @if($submission?->attachment_path)<small><a href="{{ route('student.assessment-submissions.download', $submission) }}">Download submitted file</a></small>@endif
                        @if($submissionOpen)
                            <details class="mt-2"><summary>{{ $submission ? 'Update submission' : 'Submit work' }}</summary>
                                <form method="POST" enctype="multipart/form-data" action="{{ route('student.assessment-submissions.store', [$course, $assessment]) }}" class="mt-2">
                                    @csrf
                                    <textarea class="form-control mb-2" name="answer_text" maxlength="10000" rows="3" placeholder="Written response">{{ old('answer_text', $submission?->answer_text) }}</textarea>
                                    <input class="form-control mb-2" type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                    <button class="btn btn-primary btn-sm">Save submission</button>
                                </form>
                            </details>
                        @endif
                        @if($submission?->teacher_feedback)<small>Feedback: {{ $submission->teacher_feedback }}</small>@endif
                    @endif
                </td>
                <td>@if($assessment->marks_released_at && $mark && $mark->obtained !== null)<strong>{{ $mark->obtained }} / {{ $assessment->maximum }}</strong><small>Released {{ $assessment->marks_released_at->format('d M Y') }}</small>@else<span class="text-muted">Not released</span>@endif</td>
            </tr>
        @empty<tr><td colspan="4" class="academic-empty">No assessments have been created for this course yet.</td></tr>@endforelse</tbody>
    </table></div>
    <h2 class="mt-4">Published result record</h2>
    @if(!$item)<p class="academic-note">Marks have not been released in a published course result yet.</p>
    @else
        <div class="academic-note mb-3">Published course total: {{ $item->obtained_marks }} / {{ $item->total_marks }}</div>
        @include('student-portal.results.breakdown')
        @if($item->assessment_snapshot)
            <div class="table-responsive mt-4"><table class="table academic-table"><thead><tr><th>Assessment</th><th>Component</th><th>Date</th><th>Raw marks</th><th>Course contribution</th></tr></thead><tbody>@foreach($item->assessment_snapshot['student']['assessments'] as $assessment)<tr><td>{{ $assessment['title'] }}</td><td>{{ ucfirst($assessment['component']) }}</td><td>{{ $assessment['held_on'] }}</td><td>{{ $assessment['obtained'] }} / {{ $assessment['maximum'] }}</td><td>{{ number_format($assessment['obtained'] / $assessment['maximum'] * $assessment['weight'], 2) }} / {{ $assessment['weight'] }}</td></tr>@endforeach</tbody></table></div>
            @if($attendance = $item->assessment_snapshot['student']['attendance'] ?? null)<p>Published attendance snapshot: {{ $attendance['percentage'] }}% · {{ $attendance['valid_sessions'] }} counted sessions.</p>@endif
        @else<p class="text-muted mt-3">Individual assessment records are not available for this historical result.</p>@endif
    @endif
@endsection
