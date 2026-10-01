@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">
@endsection

@section('content')
    <div class="container py-5 academic-page">
        <section class="academic-shell">
            <header class="academic-header">
                <div><span class="academic-eyebrow"><i class="bi bi-mortarboard-fill"></i> @yield('workspace-eyebrow', 'ACADEMIC PLANNING')</span><h1>@yield('heading')</h1><p>@yield('description')</p></div>
                <div class="academic-header-actions">@yield('header-actions')</div>
            </header>
            @hasSection('workspace-navigation')
                @yield('workspace-navigation')
            @else
            <nav class="academic-tabs" aria-label="Academic setup">
                @can('terms.manage')<a href="{{ route('academic.terms.index') }}" @class(['active' => request()->is('academic/terms*')])><span>01</span> Years &amp; terms</a>@endcan
                @can('curriculum.manage')<a href="{{ route('academic.curricula.index') }}" @class(['active' => request()->is('academic/curricula*')])><span>02</span> Curriculum</a>@endcan
                @can('offerings.manage')<a href="{{ route('academic.offerings.index') }}" @class(['active' => request()->is('academic/offerings*')])><span>03</span> Course offerings</a>@endcan
            </nav>
            @endif
            <div class="academic-body">
                @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert"><strong>Please review the following:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('academic-content')
            </div>
        </section>
    </div>
@endsection
