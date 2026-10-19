@extends('student-portal.layout')
@section('heading', 'Leave Requests')
@section('description', 'Request leave for an active course and track the assigned teacher’s decision.')
@section('header-actions')<span class="portal-header-chip"><i class="bi bi-shield-check" aria-hidden="true"></i> Course-specific requests</span>@endsection
@section('student-content')
    <div class="portal-section-heading"><div><h2>Request course leave</h2><p>Your assigned teacher will receive the request immediately.</p></div></div>
    @if($courses->isEmpty())
        <div class="portal-empty-card"><i class="bi bi-journal-x" aria-hidden="true"></i><strong>No active courses available</strong><span>Leave requests become available when you have an active course in an active teaching term.</span></div>
    @else
        <form method="POST" action="{{ route('student.leaves.store') }}" enctype="multipart/form-data" class="portal-leave-form">
            @csrf
            <div class="portal-form-grid">
                <div class="portal-field-wide"><label class="form-label" for="leave-course">Course</label><select @class(['form-select', 'is-invalid' => $errors->has('student_enrollment_course_id')]) id="leave-course" name="student_enrollment_course_id" required><option value="">Choose a course</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('student_enrollment_course_id') == $course->id)>{{ $course->course_code }} — {{ $course->course_name }} · {{ $course->offering->term->name }}</option>@endforeach</select>@error('student_enrollment_course_id')<div class="portal-field-error">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="leave-start">From</label><input @class(['form-control', 'is-invalid' => $errors->has('starts_on')]) id="leave-start" type="date" name="starts_on" value="{{ old('starts_on') }}" required>@error('starts_on')<div class="portal-field-error">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="leave-end">To</label><input @class(['form-control', 'is-invalid' => $errors->has('ends_on')]) id="leave-end" type="date" name="ends_on" value="{{ old('ends_on') }}" required>@error('ends_on')<div class="portal-field-error">{{ $message }}</div>@enderror</div>
                <div><label class="form-label" for="leave-type">Leave type</label><select class="form-select" id="leave-type" name="leave_type" required><option value="">Choose type</option>@foreach(\App\Models\Leave\StudentLeaveRequest::TYPES as $type)<option value="{{ $type }}" @selected(old('leave_type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div><label class="form-label" for="leave-file">Supporting file <span class="portal-optional">Optional</span></label><input class="form-control" id="leave-file" type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"><small>PDF, document, or image · max 10 MB</small></div>
                <div class="portal-field-wide"><label class="form-label" for="leave-reason">Reason</label><textarea @class(['form-control', 'is-invalid' => $errors->has('reason')]) id="leave-reason" name="reason" rows="3" minlength="5" maxlength="1500" required placeholder="Briefly explain your leave request">{{ old('reason') }}</textarea>@error('reason')<div class="portal-field-error">{{ $message }}</div>@enderror</div>
            </div>
            <div class="portal-form-footer"><span><i class="bi bi-info-circle" aria-hidden="true"></i> You can cancel only while this request is pending.</span><button class="btn portal-btn" type="submit"><i class="bi bi-send" aria-hidden="true"></i> Send request</button></div>@error('leave_request')<div class="portal-field-error mt-2">{{ $message }}</div>@enderror
        </form>
    @endif

    <div class="portal-section-heading"><div><h2>My leave history</h2><p>All course-specific requests and teacher decisions</p></div><span class="portal-badge is-info">{{ $requests->total() }} total</span></div>
    <div class="portal-leave-list">
        @forelse($requests as $leave)
            @php($class = match($leave->status) {'approved' => 'is-success', 'rejected' => 'is-danger', 'pending' => 'is-warning', default => 'is-muted'})
            <article class="portal-leave-card">
                <div class="portal-leave-card-top"><div><span class="portal-code">{{ $leave->enrollmentCourse->course_code }}</span><h3>{{ $leave->enrollmentCourse->course_name }}</h3><p>{{ ucfirst($leave->leave_type) }} leave · {{ $leave->starts_on->format('d M Y') }} – {{ $leave->ends_on->format('d M Y') }}</p></div><span class="portal-badge {{ $class }}">{{ ucfirst($leave->status) }}</span></div>
                <p class="portal-leave-reason">{{ $leave->reason }}</p>
                @if($leave->teacher_remark)<div class="portal-review-note"><i class="bi bi-chat-square-text" aria-hidden="true"></i><span><strong>Teacher response</strong>{{ $leave->teacher_remark }}</span></div>@endif
                <div class="portal-leave-card-bottom"><span>Submitted {{ $leave->created_at->format('d M Y, H:i') }}</span><div class="portal-inline-actions">@if($leave->attachment_path)<a class="portal-action-link" href="{{ route('student.leaves.attachment', $leave) }}"><i class="bi bi-download" aria-hidden="true"></i> File</a>@endif @if($leave->status === 'pending')<form method="POST" action="{{ route('student.leaves.cancel', $leave) }}">@csrf<button class="portal-text-button is-danger" type="submit" onclick="return confirm('Cancel this leave request?')">Cancel request</button></form>@endif</div></div>
            </article>
        @empty
            <div class="portal-empty-card"><i class="bi bi-calendar2-plus" aria-hidden="true"></i><strong>No leave requests yet</strong><span>Select an active course above when you need to request leave.</span></div>
        @endforelse
    </div>
    <div class="portal-pagination">{{ $requests->links('pagination::bootstrap-5') }}</div>
@endsection
