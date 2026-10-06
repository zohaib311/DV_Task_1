@extends('welcome')
@section('title', 'Notifications')
@section('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}">@endsection
@section('content')
    <div class="container py-5 academic-page"><section class="academic-shell">
        <header class="academic-header"><div><span class="academic-eyebrow">LIVE UPDATES</span><h1>My Notifications</h1><p>Role-specific academic activity for your account.</p></div>
            <form method="POST" action="{{ route('account.notifications.read-all') }}">@csrf<button class="btn academic-header-btn">Mark all read</button></form>
        </header>
        <div class="academic-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>When</th><th>Update</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @forelse($notifications as $notification)<tr><td>{{ $notification->created_at->format('d M Y H:i') }}</td><td><strong>{{ $notification->data['title'] ?? 'Academic update' }}</strong><small>{{ $notification->data['message'] ?? '' }}</small></td><td>{{ $notification->read_at ? 'Read' : 'New' }}</td><td><form method="POST" action="{{ route('account.notifications.read', $notification) }}">@csrf<button class="btn btn-sm btn-primary">{{ ($notification->data['url'] ?? null) ? 'Open update' : 'Mark read' }}</button></form></td></tr>
                @empty<tr><td colspan="4" class="academic-empty">No notifications yet.</td></tr>@endforelse
            </tbody></table></div>{{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    </section></div>
@endsection
