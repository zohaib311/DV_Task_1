@extends('student-portal.layout')
@section('heading', $course->course_code.' — Assessments & Marks')
@section('description', 'Review instructions, download question files, submit work, and follow released marks.')
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('student.assessments.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> All courses</a>@endsection
@section('student-content')
    <div class="portal-card portal-result-card">
        <div class="portal-result-main"><strong>{{ $course->course_name }}</strong><small>{{ $course->course_code }}</small></div>
        <div class="portal-metric"><small>Semester</small><strong>{{ $course->enrollment->semester }}</strong></div>
        <div class="portal-metric"><small>Academic year</small><strong>{{ $course->enrollment->academic_year }}</strong></div>
        <div class="portal-metric"><small>Teaching term</small><strong>{{ $course->enrollment->term?->name ?? 'Historical' }}</strong></div>
        <span class="portal-badge {{ $course->enrollment->status === 'active' ? 'is-success' : '' }}">{{ ucfirst($course->enrollment->status) }}</span>
    </div>

    <div class="portal-section-heading"><div><h2>Current assessment activity</h2><p>Submission access and released marks for this course</p></div><span class="portal-badge is-info">{{ $liveAssessments->count() }} assessments</span></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table align-middle">
        <thead><tr><th>Assessment</th><th>Date / deadline</th><th>Submission</th><th>Released marks</th></tr></thead>
        <tbody>@forelse($liveAssessments as $assessment)
            @php($submission = $assessment->studentSubmissions->firstWhere('student_enrollment_course_id', $course->id))
            @php($mark = $assessment->marks->firstWhere('student_enrollment_course_id', $course->id))
            @php($deadlineOpen = $assessment->isSubmissionDeadlineOpen())
            @php($submissionOpen = $deadlineOpen && $course->enrollment->status === 'active' && $course->enrollment->term?->status === 'active')
            @php($submissionClass = $submission ? 'is-success' : ($submissionOpen ? 'is-info' : 'is-danger'))
            <tr>
                <td class="portal-course-cell">
                    <strong>{{ $assessment->title }}</strong>
                    <small>{{ ucfirst($assessment->component->code) }} · Weight {{ $assessment->weight }}</small>
                    @if($assessment->instructions)<p class="small mt-2 mb-0">{{ $assessment->instructions }}</p>@endif
                    @if($assessment->question_file_path)
                        <div class="mt-2"><a class="portal-btn is-outline is-sm" href="{{ route('student.assessments.question-file', [$course, $assessment]) }}"><i class="bi bi-download" aria-hidden="true"></i> Download question file</a><small>{{ $assessment->question_original_filename }} · {{ number_format(($assessment->question_file_size ?? 0) / 1024, 1) }} KB</small></div>
                    @endif
                </td>
                <td>
                    <strong>{{ $assessment->held_on->format('d M Y') }}</strong>
                    @if($assessment->submissions_due_at)<small><i class="bi bi-clock me-1" aria-hidden="true"></i>Due {{ $assessment->submissions_due_at->format('d M Y H:i') }}</small>@else<small>No submission deadline</small>@endif
                </td>
                <td>
                    @if(!$assessment->submission_required)
                        <span class="portal-badge">No upload required</span>
                    @else
                        <span class="portal-badge {{ $submissionClass }}">{{ $submission ? ucfirst($submission->status) : ($submissionOpen ? 'Awaiting submission' : 'Closed') }}</span>
                        @if($assessment->submissions_due_at && !$deadlineOpen)<small class="text-danger"><i class="bi bi-lock me-1" aria-hidden="true"></i>Deadline passed {{ $assessment->submissions_due_at->format('d M Y H:i') }}</small>
                        @elseif(!$assessment->submissions_due_at)<small class="text-danger">Submission deadline is not configured.</small>
                        @elseif(!$submissionOpen)<small class="text-danger">This course or teaching term is closed.</small>@endif
                        @if($submission?->attachment_path)<div class="mt-2"><a class="portal-btn is-soft is-sm" href="{{ route('student.assessment-submissions.download', $submission) }}"><i class="bi bi-paperclip" aria-hidden="true"></i> Download submitted file</a></div>@endif
                        @if($submissionOpen)
                            <details class="portal-details"><summary>{{ $submission ? 'Update submission' : 'Submit work' }}</summary>
                                <form method="POST" enctype="multipart/form-data" action="{{ route('student.assessment-submissions.store', [$course, $assessment]) }}">
                                    @csrf
                                    <label class="form-label" for="answer-{{ $assessment->id }}">Written response</label>
                                    <textarea id="answer-{{ $assessment->id }}" class="form-control mb-2" name="answer_text" maxlength="10000" rows="3" placeholder="Write your response here">{{ old('answer_text', $submission?->answer_text) }}</textarea>
                                    <label class="form-label" for="attachment-{{ $assessment->id }}">Answer attachment</label>
                                    <input id="attachment-{{ $assessment->id }}" class="form-control mb-2" type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                    <small class="d-block mb-2">PDF, Word, ZIP, JPG or PNG · maximum 10 MB</small>
                                    <button class="portal-btn is-sm"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Save submission</button>
                                </form>
                            </details>
                        @endif
                        @if($submission?->teacher_feedback)<div class="portal-feedback"><strong><i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Teacher feedback</strong>{{ $submission->teacher_feedback }}</div>@endif
                    @endif
                </td>
                <td>
                    @if($assessment->marks_released_at && $mark && $mark->obtained !== null)
                        <span class="portal-badge is-success"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $mark->obtained }} / {{ $assessment->maximum }}</span><small>Released {{ $assessment->marks_released_at->format('d M Y') }}</small>
                    @else
                        <span class="portal-badge">Not released</span>
                    @endif
                </td>
            </tr>
        @empty<tr class="portal-empty-row"><td colspan="4"><div class="portal-empty-state"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i><strong>No assessments have been created for this course yet.</strong><span>New assessments will appear here when your teacher creates them.</span></div></td></tr>@endforelse</tbody>
    </table></div>

    <div class="portal-section-heading"><div><h2>Published result record</h2><p>Frozen academic evidence from the published semester result</p></div></div>
    @if(!$item)
        <p class="academic-note portal-note-with-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Marks have not been released in a published course result yet.</span></p>
    @else
        <div class="portal-result-summary">
            <div><small>Course total</small><strong>{{ $item->obtained_marks }} / {{ $item->total_marks }}</strong></div>
            <div><small>Percentage</small><strong>{{ $item->percentage }}%</strong></div>
            <div><small>Grade</small><strong>{{ $item->grade }}</strong></div>
            <div><small>Grade point</small><strong>{{ $item->grade_point }}</strong></div>
            <div><small>Outcome</small><span class="portal-badge {{ $item->status === 'Pass' ? 'is-success' : 'is-danger' }}">{{ $item->status }}</span></div>
        </div>
        <div class="portal-card">@include('student-portal.results.breakdown')</div>
        @if($item->assessment_snapshot)
            <div class="portal-section-heading"><div><h2>Published assessment evidence</h2><p>Saved raw marks and contribution to the course result</p></div></div>
            <div class="table-responsive portal-table-card"><table class="table academic-table"><thead><tr><th>Assessment</th><th>Component</th><th>Date</th><th>Raw marks</th><th>Course contribution</th></tr></thead><tbody>@foreach($item->assessment_snapshot['student']['assessments'] as $assessment)<tr><td><strong>{{ $assessment['title'] }}</strong></td><td><span class="portal-badge">{{ ucfirst($assessment['component']) }}</span></td><td>{{ $assessment['held_on'] }}</td><td>{{ $assessment['obtained'] }} / {{ $assessment['maximum'] }}</td><td><strong>{{ number_format($assessment['obtained'] / $assessment['maximum'] * $assessment['weight'], 2) }}</strong> / {{ $assessment['weight'] }}</td></tr>@endforeach</tbody></table></div>
            @if($attendance = $item->assessment_snapshot['student']['attendance'] ?? null)<p class="academic-note portal-note-with-icon mt-3"><i class="bi bi-calendar2-check" aria-hidden="true"></i><span>Published attendance snapshot: <strong>{{ $attendance['percentage'] }}%</strong> · {{ $attendance['valid_sessions'] }} counted sessions.</span></p>@endif
        @else
            <p class="text-muted mt-3">Individual assessment records are not available for this historical result.</p>
        @endif
    @endif
@endsection
