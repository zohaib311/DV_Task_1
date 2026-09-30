@extends('teaching.layout')
@section('heading', $offering->course_name)
@section('description', $offering->course_code.' · '.$offering->semester.' · '.$offering->term->name)
@section('header-actions')<a href="{{ route('teaching.offerings.index') }}" class="btn academic-header-btn"><i class="bi bi-arrow-left" aria-hidden="true"></i> My courses</a>@endsection
@section('teaching-content')
    @can('attendance.manage-assigned')
        <div class="academic-actions mb-4 mt-0"><a class="btn btn-primary" href="{{ route('teaching.attendance.offering', $offering) }}">Manage attendance</a></div>
    @endcan
    <dl class="teaching-class-details">
        <div><dt>Department / Section</dt><dd>{{ $offering->department->name }} / {{ $offering->section->name }}</dd></div>
        <div><dt>Academic year</dt><dd>{{ $offering->term->academicYear->name }}</dd></div>
        <div><dt>Teaching team</dt><dd>{{ $offering->teachers->pluck('name')->implode(', ') }}</dd></div>
        <div><dt>Offering status</dt><dd>@include('teaching.partials.status', ['status' => $offering->status])</dd></div>
    </dl>
    <div class="teaching-scheme"><strong>Approved assessment scheme</strong><span>{{ $offering->credit_hours }} credits</span><span>Attendance {{ $offering->attendance_marks }}</span><span>Midterm {{ $offering->mid_marks }}</span><span>Final {{ $offering->final_marks }}</span><span>Total {{ $offering->total_marks }}</span></div>
    <div class="teaching-section-heading"><div><h2>Student roster <span class="academic-badge">{{ $rosterCount }}</span></h2><p>Registered students for this offering, including preserved enrollment history.</p></div></div>
    <form method="GET" action="{{ route('teaching.offerings.show', $offering) }}" class="teaching-roster-search">
        <div><label class="visually-hidden" for="student-search">Search student name or registration number</label><input id="student-search" name="q" type="search" maxlength="100" value="{{ request('q') }}" class="form-control" placeholder="Student name or registration number"></div><button type="submit" class="btn btn-primary">Search</button><a class="btn btn-light" href="{{ route('teaching.offerings.show', $offering) }}">Reset</a>
    </form>
    <div class="table-responsive"><table class="table academic-table align-middle mb-0">
        <thead><tr><th scope="col">#</th><th scope="col">Student</th><th scope="col">Registration no.</th><th scope="col">Enrollment semester</th><th scope="col">Registration type</th><th scope="col">Enrollment status</th></tr></thead>
        <tbody>@forelse($roster as $entry)<tr><td>{{ $roster->firstItem() + $loop->index }}</td><td><strong>{{ $entry->enrollment->student->name }}</strong></td><td>{{ $entry->enrollment->student->registration_no ?? 'Not assigned' }}</td><td>{{ $entry->enrollment->semester }}</td><td>{{ ucfirst($entry->registration_type ?? 'Required') }}</td><td>@include('teaching.partials.status', ['status' => $entry->enrollment->status])</td></tr>@empty<tr><td colspan="6" class="academic-empty">{{ request()->filled('q') ? 'No students match your search.' : 'No students are enrolled in this offering yet.' }}</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-4">{{ $roster->links('pagination::bootstrap-5') }}</div>
    <p class="teaching-footnote"><i class="bi bi-lock" aria-hidden="true"></i> Read-only roster. Student registration and enrollment changes are managed by your administrator.</p>
@endsection
