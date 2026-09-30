@extends('welcome')
@section('title', 'Student Attendance')
@section('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">@endsection
@section('content')
    <div class="container py-5 academic-page"><section class="academic-shell">
        <header class="academic-header"><div><span class="academic-eyebrow">STUDENT PORTAL</span><h1>@yield('heading')</h1><p>Your attendance, across your registered semesters.</p></div><a class="btn academic-header-btn" href="{{ route('student.attendance.index') }}">My attendance</a></header>
        <div class="academic-body">@yield('student-content')</div>
    </section></div>
@endsection
