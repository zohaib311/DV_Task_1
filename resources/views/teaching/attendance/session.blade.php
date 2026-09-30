@extends('teaching.layout')
@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/teaching/attendance.css') }}">
@endsection
@section('heading', $offering->course_code.' — '.$session->held_on->format('d M Y'))
@section('description', ucfirst($session->type).' · Slot '.$session->slot.' · '.ucfirst($session->status))
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('teaching.attendance.offering', $offering) }}">Back to attendance</a>@endsection
@section('teaching-content')
    @if($locked)<div class="academic-note mb-4">This session is read-only because it is cancelled, the class/term is closed, or an enrollment has been finalized.</div>@endif
    <form method="POST" action="{{ route('teaching.attendance.update', [$offering, $session]) }}">
        @csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ old('revision', $session->revision) }}">
        <fieldset @disabled($locked)>
            <legend class="h6">Mark each student explicitly</legend>
            <div class="table-responsive">
                <table class="table academic-table align-middle">
                    <thead><tr><th>Student</th><th>Attendance status</th><th>Teacher note (optional)</th></tr></thead>
                    <tbody>@foreach($session->records as $index => $record)
                        <tr>
                            <td><strong>{{ $record->enrollmentCourse->enrollment->student->name }}</strong><small>{{ $record->enrollmentCourse->enrollment->student->registration_no }}</small><input type="hidden" name="records[{{ $index }}][id]" value="{{ $record->id }}"></td>
                            <td>@include('teaching.attendance.partials.status-options')</td>
                            <td><input class="form-control" name="records[{{ $index }}][note]" maxlength="500" aria-label="Note for {{ $record->enrollmentCourse->enrollment->student->name }}" value="{{ old('records.'.$index.'.note', $record->note) }}"></td>
                        </tr>
                    @endforeach</tbody>
                </table>
            </div>
            <label class="form-label mt-3" for="correction-reason">Correction reason {{ $session->status === 'completed' ? '(required)' : '(optional on first save)' }}</label>
            <input id="correction-reason" class="form-control" name="reason" maxlength="500" value="{{ old('reason') }}" @required($session->status === 'completed')>
            <div class="academic-actions"><button class="btn btn-primary" type="submit">Save complete attendance</button></div>
        </fieldset>
    </form>
    @unless($locked)
        <details class="mt-4 academic-form-section"><summary class="text-danger">Cancel this session</summary>
            <p class="text-muted small mt-2">Cancelled sessions are excluded from totals. Records and audit history are retained; cancellation cannot be undone.</p>
            <form method="POST" action="{{ route('teaching.attendance.cancel', [$offering, $session]) }}" class="d-flex flex-wrap gap-2">
                @csrf <input type="hidden" name="revision" value="{{ $session->revision }}">
                <input class="form-control" name="reason" aria-label="Cancellation reason" placeholder="Cancellation reason (required)" minlength="5" maxlength="500" required>
                <button class="btn btn-outline-danger" type="submit">Confirm cancellation</button>
            </form>
        </details>
    @endunless
    <h2 class="mt-4">Audit history</h2>
    @foreach($audits as $audit)
        <details class="teaching-footnote">
            <summary>{{ ucfirst($audit->action) }} · {{ $audit->user?->name ?? 'Former account' }} · {{ $audit->created_at->format('d M Y H:i') }}@if($audit->reason) — {{ $audit->reason }}@endif</summary>
            <div class="table-responsive"><table class="table academic-table"><thead><tr><th>Enrollment course</th><th>Before</th><th>After</th></tr></thead><tbody>
                @foreach($audit->after['records'] as $row)
                    @php($previous = collect($audit->before['records'] ?? [])->firstWhere('id', $row['id']))
                    <tr><td>#{{ $row['student_enrollment_course_id'] }}</td><td>{{ $previous['status'] ?? 'Unmarked' }}<small>{{ $previous['note'] ?? '' }}</small></td><td>{{ $row['status'] ?? 'Unmarked' }}<small>{{ $row['note'] ?? '' }}</small></td></tr>
                @endforeach
            </tbody></table></div>
        </details>
    @endforeach
    <div class="mt-3">{{ $audits->links('pagination::bootstrap-5') }}</div>
@endsection
