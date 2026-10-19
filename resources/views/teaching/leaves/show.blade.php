@extends('teaching.layout')
@section('heading', 'Review leave request')
@section('description', $leave->enrollmentCourse->course_code.' · '.$leave->enrollmentCourse->course_name)
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('teaching.leaves.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> All requests</a>@endsection
@section('teaching-content')
    @php($statusClass = match($leave->status) {'approved' => 'is-complete', 'rejected' => 'is-cancelled', 'pending' => 'is-pending', default => ''})
    <section class="teaching-leave-detail">
        <div class="teaching-leave-heading"><div><span class="teaching-overline">{{ ucfirst($leave->leave_type) }} leave</span><h2>{{ $leave->enrollmentCourse->enrollment->student->name }}</h2><p>{{ $leave->enrollmentCourse->enrollment->student->registration_no }} · Submitted {{ $leave->created_at->format('d M Y, H:i') }}</p></div><span class="academic-badge {{ $statusClass }}">{{ ucfirst($leave->status) }}</span></div>
        <dl class="teaching-class-details"><div><dt>Course</dt><dd>{{ $leave->enrollmentCourse->course_code }} — {{ $leave->enrollmentCourse->course_name }}</dd></div><div><dt>Leave dates</dt><dd>{{ $leave->starts_on->format('d M Y') }} – {{ $leave->ends_on->format('d M Y') }}</dd></div><div><dt>Teaching term</dt><dd>{{ $leave->offering->term->name }}</dd></div><div><dt>Attachment</dt><dd>@if($leave->attachment_path)<a class="teaching-inline-link" href="{{ route('teaching.leaves.attachment', $leave) }}"><i class="bi bi-download" aria-hidden="true"></i> Download</a>@else<span class="text-muted">None supplied</span>@endif</dd></div></dl>
        <div class="teaching-leave-reason"><strong>Student reason</strong><p>{{ $leave->reason }}</p></div>
        @if($leave->teacher_remark)<div class="teaching-history-reason"><i class="bi bi-chat-square-text" aria-hidden="true"></i><span><strong>Your response</strong><br>{{ $leave->teacher_remark }}</span></div>@endif
    </section>
    @if($leave->status === 'pending')
        <form method="POST" action="{{ route('teaching.leaves.review', $leave) }}" class="teaching-leave-review">
            @csrf @method('PUT')
            <label class="form-label" for="teacher-remark">Teacher response <span class="text-muted">(required when rejecting)</span></label>
            <textarea @class(['form-control', 'is-invalid' => $errors->has('teacher_remark')]) id="teacher-remark" name="teacher_remark" rows="4" maxlength="1500" placeholder="Add a note for the student">{{ old('teacher_remark') }}</textarea>@error('teacher_remark')<div class="teaching-field-error">{{ $message }}</div>@enderror
            <p class="teaching-footnote"><i class="bi bi-info-circle" aria-hidden="true"></i> Approving this request does not automatically mark attendance as excused. Please decide the attendance status in the relevant class session.</p>
            <div class="academic-actions"><button class="btn btn-primary" type="submit" name="decision" value="approved"><i class="bi bi-check2-circle" aria-hidden="true"></i> Approve leave</button><button class="btn btn-outline-danger" type="submit" name="decision" value="rejected"><i class="bi bi-x-circle" aria-hidden="true"></i> Reject leave</button></div>
        </form>@error('leave_request')<div class="teaching-field-error mt-3">{{ $message }}</div>@enderror
    @endif
@endsection
