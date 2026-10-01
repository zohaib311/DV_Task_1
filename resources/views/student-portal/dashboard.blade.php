@extends('student-portal.layout')
@section('heading', 'Welcome, '.$student->name)
@section('student-content')
    <div class="d-flex flex-wrap gap-4 border-bottom pb-4 mb-4"><div><small class="text-muted">REGISTRATION</small><h2>{{ $student->registration_no }}</h2></div><div><small class="text-muted">CURRENT SEMESTER</small><h2>{{ $enrollment?->semester ?? 'Not enrolled' }}</h2></div><div><small class="text-muted">REGISTERED COURSES</small><h2>{{ $courseCount }}</h2></div></div>
    <h2>My academic profile</h2>
    <dl class="row"><dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $student->name }}</dd><dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $student->email }}</dd><dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $student->phone ?? 'Not provided' }}</dd><dt class="col-sm-3">Department</dt><dd class="col-sm-9">{{ $student->department?->name ?? 'Not assigned' }}</dd><dt class="col-sm-3">Section</dt><dd class="col-sm-9">{{ $enrollment?->section?->name ?? $student->section?->name ?? 'Not assigned' }}</dd><dt class="col-sm-3">Academic year / term</dt><dd class="col-sm-9">{{ $enrollment?->academic_year ?? 'Not assigned' }} · {{ $enrollment?->term?->name ?? 'No active term' }}</dd><dt class="col-sm-3">Enrolled on</dt><dd class="col-sm-9">{{ $enrollment?->enrolled_at?->format('d M Y') ?? 'Not enrolled' }}</dd></dl>
    <p class="academic-note">Academic profile details are maintained by your university. Contact your administrator to correct your registration or enrollment information.</p>
    @can('student.result.view-own')
        <h2 class="mt-4">Latest published semester</h2>
        @if($latestResult)<div class="d-flex flex-wrap gap-4 align-items-center"><span>{{ $latestResult->enrollment->semester }} · {{ $latestResult->enrollment->academic_year }}</span><span>SGPA <strong>{{ $latestResult->sgpa }}</strong></span><span>CGPA <strong>{{ $latestResult->cgpa }}</strong></span><span>{{ $latestResult->status }}</span><a href="{{ route('student.results.show', $latestResult) }}">Open result sheet</a></div>@else<p class="text-muted">No published results yet. Results appear after the university releases them.</p>@endif
    @endcan
@endsection
