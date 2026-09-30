@extends('teaching.layout')
@section('heading', 'My teaching overview')
@section('description', 'Your assigned classes, teaching terms and student rosters in one place.')
@section('header-actions')
    <a href="{{ route('teaching.offerings.index') }}" class="btn academic-header-btn">All assigned courses <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
@endsection
@section('teaching-content')
    <div class="teaching-intro"><div><span class="teaching-overline">YOUR WORKSPACE</span><h2>Welcome, {{ $teacher->name }}</h2><p>Start with a class below to view its enrolled students.</p></div><i class="bi bi-person-video3 teaching-intro-icon" aria-hidden="true"></i></div>
    <dl class="teaching-metrics">
        <div><dt>Assigned offerings</dt><dd>{{ $offeringCount }}</dd></div>
        <div><dt>Active offerings</dt><dd>{{ $activeCount }}</dd></div>
        <div><dt>Distinct students · all terms</dt><dd>{{ $studentCount }}</dd></div>
    </dl>
    <div class="teaching-section-heading"><h2>Your classes</h2><span>Up to five offerings · active first</span></div>
    @include('teaching.partials.offering-table')
    <p class="teaching-footnote"><i class="bi bi-shield-check" aria-hidden="true"></i> Only courses assigned to your linked teacher profile appear here. Attendance and assessment entry will be added in their next phases.</p>
@endsection
