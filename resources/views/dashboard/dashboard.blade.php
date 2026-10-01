@extends('welcome')
@section('title', 'University Dashboard')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endsection
@section('content')
    <div class="container-fluid py-3">
        <div class="dashboard-hero-banner">
            <h2>Welcome back, {{ auth()->user()->name }}!</h2>
            <p>Live academic management statistics and quick access to your authorized modules.</p>
        </div>
        @if($statistics->contains(fn ($stat) => $stat['createRoute']) || auth()->user()->can('results.create'))
            <div class="quick-actions-card">
                <h5><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h5>
                <div class="action-buttons-grid">
                    @foreach($statistics as $stat)
                        @if($stat['createRoute'])
                            <a href="{{ route($stat['createRoute']) }}" class="action-shortcut-btn"><i class="bi {{ $stat['icon'] }}"></i> Add {{ \Illuminate\Support\Str::singular($stat['label']) }}</a>
                        @endif
                    @endforeach
                    @can('results.create')
                        <a href="{{ route('addResultForm') }}" class="action-shortcut-btn"><i class="bi bi-award-fill"></i> Add Result</a>
                    @endcan
                </div>
            </div>
        @endif
        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-speedometer2 me-2"></i>Overview Statistics</h5>
        <div class="stats-grid">
            @forelse($statistics as $stat)
                <a href="{{ route($stat['route']) }}" class="stat-card {{ $stat['style'] }}">
                    <div class="stat-card-info"><h6>{{ $stat['label'] }}</h6><h3>{{ number_format($stat['count']) }}</h3></div>
                    <div class="stat-card-icon"><i class="bi {{ $stat['icon'] }}"></i></div>
                </a>
            @empty
                <p class="text-muted">No overview statistics are available for your permissions. Use the sidebar to access your assigned workspace.</p>
            @endforelse
        </div>
    </div>
@endsection
