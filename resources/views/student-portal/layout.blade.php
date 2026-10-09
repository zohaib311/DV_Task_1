@extends('welcome')
@section('title', 'Student Portal')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-portal/portal.css') }}">
@endsection
@section('content')
    <div class="container py-4 py-lg-5 academic-page student-portal-page">
        <section class="academic-shell student-portal-shell">
        <header class="academic-header student-portal-header">
            <div class="student-portal-header-copy">
                <span class="academic-eyebrow"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i> STUDENT PORTAL</span>
                <h1>@yield('heading')</h1>
                <p>@yield('description', 'Your enrollment, learning progress, and published academic records.')</p>
            </div>
            <div class="student-portal-header-actions">@yield('header-actions')</div>
            <i class="bi bi-mortarboard student-portal-header-mark" aria-hidden="true"></i>
        </header>
        <nav class="academic-tabs student-portal-tabs" aria-label="Student portal">@include('student-portal.partials.navigation')</nav>
        <div class="academic-body student-portal-body">
            @if(session('success'))
                <div class="alert alert-success portal-alert" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger portal-alert" role="alert">
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    <div><strong>Please correct the following:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            @endif
            @yield('student-content')
        </div>
        </section>
    </div>
@endsection
