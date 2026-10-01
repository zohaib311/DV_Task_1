@extends('welcome')
@section('title', 'Student Portal')
@section('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">@endsection
@section('content')
    <div class="container py-5 academic-page"><section class="academic-shell">
        <header class="academic-header"><div><span class="academic-eyebrow">STUDENT PORTAL</span><h1>@yield('heading')</h1><p>Your enrollment, learning progress, and published academic records.</p></div></header>
        <nav class="academic-tabs" aria-label="Student portal">@include('student-portal.partials.navigation')</nav>
        <div class="academic-body">
            @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('student-content')
        </div>
    </section></div>
@endsection
