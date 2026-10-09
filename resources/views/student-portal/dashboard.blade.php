@extends('student-portal.layout')
@section('heading', 'Welcome, '.$student->name)
@section('description', 'A clear view of your current enrollment, academic identity, and latest published progress.')
@section('student-content')
    <div class="portal-summary-grid" aria-label="Enrollment summary">
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Registration</small><strong title="{{ $student->registration_no }}">{{ $student->registration_no }}</strong></div></div>
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-layers" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Current semester</small><strong>{{ $enrollment?->semester ?? 'Not enrolled' }}</strong></div></div>
        <div class="portal-stat-card"><span class="portal-stat-icon"><i class="bi bi-journals" aria-hidden="true"></i></span><div class="portal-stat-content"><small>Registered courses</small><strong>{{ $courseCount }}</strong></div></div>
    </div>

    <div class="portal-section-heading"><div><h2>My academic profile</h2><p>University-maintained identity and current placement</p></div></div>
    <div class="portal-card">
        <dl class="portal-profile-grid">
            <div class="portal-profile-item"><dt>Name</dt><dd>{{ $student->name }}</dd></div>
            <div class="portal-profile-item"><dt>Email</dt><dd>{{ $student->email }}</dd></div>
            <div class="portal-profile-item"><dt>Phone</dt><dd>{{ $student->phone ?? 'Not provided' }}</dd></div>
            <div class="portal-profile-item"><dt>Department</dt><dd>{{ $student->department?->name ?? 'Not assigned' }}</dd></div>
            <div class="portal-profile-item"><dt>Section</dt><dd>{{ $enrollment?->section?->name ?? $student->section?->name ?? 'Not assigned' }}</dd></div>
            <div class="portal-profile-item"><dt>Academic year / term</dt><dd>{{ $enrollment?->academic_year ?? 'Not assigned' }} · {{ $enrollment?->term?->name ?? 'No active term' }}</dd></div>
            <div class="portal-profile-item"><dt>Enrolled on</dt><dd>{{ $enrollment?->enrolled_at?->format('d M Y') ?? 'Not enrolled' }}</dd></div>
            <div class="portal-profile-item"><dt>Enrollment status</dt><dd><span class="portal-badge {{ $enrollment?->status === 'active' ? 'is-success' : '' }}">{{ $enrollment ? ucfirst($enrollment->status) : 'Not enrolled' }}</span></dd></div>
        </dl>
    </div>
    <p class="academic-note portal-note-with-icon mt-3"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Academic profile details are maintained by your university. Contact your administrator to correct your registration or enrollment information.</span></p>
    @can('student.result.view-own')
        <div class="portal-section-heading"><div><h2>Latest published semester</h2><p>Your most recently released academic result</p></div></div>
        @if($latestResult)
            <div class="portal-card portal-result-card">
                <div class="portal-result-main"><strong>{{ $latestResult->enrollment->semester }}</strong><small>{{ $latestResult->enrollment->academic_year }}</small></div>
                <div class="portal-metric"><small>SGPA</small><strong>{{ $latestResult->sgpa }}</strong></div>
                <div class="portal-metric"><small>CGPA</small><strong>{{ $latestResult->cgpa }}</strong></div>
                <span class="portal-badge {{ $latestResult->status === 'Pass' ? 'is-success' : 'is-danger' }}"><i class="bi {{ $latestResult->status === 'Pass' ? 'bi-check-circle' : 'bi-exclamation-circle' }}" aria-hidden="true"></i>{{ $latestResult->status }}</span>
                <a class="portal-btn is-outline" href="{{ route('student.results.show', $latestResult) }}"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> Open result sheet</a>
            </div>
        @else
            <div class="portal-empty-state portal-card"><i class="bi bi-hourglass-split" aria-hidden="true"></i><strong>No published results yet</strong><span>Results appear here after the university releases them.</span></div>
        @endif
    @endcan
@endsection
