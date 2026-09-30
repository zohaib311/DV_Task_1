@extends('welcome')

@section('title', 'Teacher Workspace')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">
    <link rel="stylesheet" href="{{ asset('css/teaching/workspace.css') }}">
@endsection

@section('content')
    <div class="container py-5 academic-page teaching-page">
        <section class="academic-shell">
            <header class="academic-header">
                <div>
                    <span class="academic-eyebrow"><i class="bi bi-person-workspace" aria-hidden="true"></i> TEACHER WORKSPACE</span>
                    <h1>@yield('heading')</h1>
                    <p>@yield('description')</p>
                </div>
                <div class="academic-header-actions">@yield('header-actions')</div>
            </header>
            <div class="academic-body">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert"><strong>Please review your filters.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('teaching-content')
            </div>
        </section>
    </div>
@endsection
