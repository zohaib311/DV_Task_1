@extends('teaching.layout')
@section('heading', $offering->course_name.' — Attendance')
@section('description', $offering->course_code.' · '.$offering->term->name)
@section('header-actions')
    <a class="btn academic-header-btn" href="{{ route('teaching.attendance.index') }}">All classes</a>
    <a class="btn academic-header-btn" href="{{ route('teaching.offerings.show', $offering) }}">Student roster</a>
@endsection
@section('teaching-content')
    @include('attendance.partials.policy', ['policy' => $offering->attendance_policy ?? config('attendance.policy')])
    @if($offering->status === 'active' && $offering->term->status === 'active')
        <h2>Create a class session</h2>
        <form method="POST" action="{{ route('teaching.attendance.store', $offering) }}" class="teaching-filters mb-4">
            @csrf
            <div><label class="form-label" for="held-on">Class date</label><input class="form-control" type="date" id="held-on" name="held_on" min="{{ $offering->term->starts_on->toDateString() }}" max="{{ min(today()->toDateString(), $offering->term->ends_on->toDateString()) }}" value="{{ old('held_on', today()->toDateString()) }}" required></div>
            <div><label class="form-label" for="session-type">Session type</label><select class="form-select" id="session-type" name="type">@foreach(['lecture', 'lab', 'tutorial'] as $type)<option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div><label class="form-label" for="session-slot">Slot (same-day class number)</label><input class="form-control" id="session-slot" name="slot" type="number" min="1" max="20" value="{{ old('slot', 1) }}" required></div>
            <button class="btn btn-primary" type="submit">Create session</button>
        </form>
        <p class="teaching-footnote mb-4">The roster includes active, unpublished enrollments registered on or before the class date. Mark every student before saving; new drafts do not affect percentages.</p>
    @else
        <p class="academic-note">This class or term is not active. Attendance history is read-only.</p>
    @endif
    <h2>Class sessions</h2>
    <div class="table-responsive">
        <table class="table academic-table align-middle">
            <thead><tr><th>Date</th><th>Session</th><th>Created by</th><th>Students</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>@forelse($sessions as $session)
                <tr><td>{{ $session->held_on->format('d M Y') }}</td><td>{{ ucfirst($session->type) }} · {{ $session->slot }}</td><td>{{ $session->teacher->name }}</td><td>{{ $session->records_count }}</td><td>@include('teaching.partials.status', ['status' => $session->status])</td><td><a href="{{ route('teaching.attendance.show', [$offering, $session]) }}">Open session</a></td></tr>
            @empty<tr><td colspan="6" class="academic-empty">No attendance sessions yet.</td></tr>@endforelse</tbody>
        </table>
    </div>
    {{ $sessions->withQueryString()->links('pagination::bootstrap-5') }}
    <h2 class="mt-4">Student attendance summary</h2>
    <div class="table-responsive">
        <table class="table academic-table align-middle">
            <thead><tr><th>Student</th><th>Counted sessions</th><th>Present / Absent / Late / Excused</th><th>Attendance</th><th>Calculated marks</th></tr></thead>
            <tbody>@forelse($courses as $course)
                @php($summary = $summaries[$course->id])
                <tr><td>{{ $course->enrollment->student->name }}<small>{{ $course->enrollment->student->registration_no }}</small></td><td>{{ $summary['valid_sessions'] }}</td><td>@foreach(['present','absent','late','excused'] as $status){{ $summary['counts'][$status] ?? 0 }}{{ $loop->last ? '' : ' / ' }}@endforeach</td><td>{{ $summary['percentage'] === null ? 'N/A' : $summary['percentage'].'%' }}</td><td>{{ $summary['marks'] ?? 'N/A' }} / {{ $summary['maximum'] }}</td></tr>
            @empty<tr><td colspan="5" class="academic-empty">No enrolled students.</td></tr>@endforelse</tbody>
        </table>
    </div>
    {{ $courses->withQueryString()->links('pagination::bootstrap-5') }}
@endsection
