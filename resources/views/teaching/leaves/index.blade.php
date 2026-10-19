@extends('teaching.layout')
@section('heading', 'Leave requests')
@section('description', 'Review leave requests only from students enrolled in your assigned classes.')
@section('header-actions')<span class="teaching-header-chip"><i class="bi bi-person-check" aria-hidden="true"></i> Teacher review</span>@endsection
@section('teaching-content')
    <form method="GET" class="teaching-filter-bar">
        <div><label class="form-label" for="leave-status">Request status</label><select class="form-select" id="leave-status" name="status"><option value="">All requests</option>@foreach(['pending', 'approved', 'rejected', 'cancelled'] as $item)<option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>@endforeach</select></div>
        <div class="teaching-filter-actions"><button class="btn btn-primary" type="submit">Filter requests</button>@if($status)<a class="btn btn-light" href="{{ route('teaching.leaves.index') }}">Clear</a>@endif</div>
    </form>
    <div class="teaching-section-heading"><div><h2>Student requests</h2><p>Approval is a leave decision only; you still choose the attendance status for each class session.</p></div><span>{{ $requests->total() }} records</span></div>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Student</th><th>Course</th><th>Dates</th><th>Type</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($requests as $leave)
            <tr><td><strong>{{ $leave->enrollmentCourse->enrollment->student->name }}</strong><small>{{ $leave->enrollmentCourse->enrollment->student->registration_no }}</small></td><td><strong>{{ $leave->enrollmentCourse->course_code }}</strong><small>{{ $leave->enrollmentCourse->course_name }}</small></td><td>{{ $leave->starts_on->format('d M') }} – {{ $leave->ends_on->format('d M Y') }}</td><td>{{ ucfirst($leave->leave_type) }}</td><td>@include('teaching.partials.status', ['status' => $leave->status])</td><td><a href="{{ route('teaching.leaves.show', $leave) }}">{{ $leave->status === 'pending' ? 'Review request' : 'View request' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></td></tr>
        @empty<tr><td colspan="6" class="academic-empty">No leave requests match this filter.</td></tr>@endforelse
    </tbody></table></div>
    <div class="mt-3">{{ $requests->links('pagination::bootstrap-5') }}</div>
@endsection
