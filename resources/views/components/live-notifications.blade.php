@php($liveNotifications = auth()->user()->notifications()->latest()->limit(6)->get())
@php($liveUnreadCount = auth()->user()->unreadNotifications()->count())
<div class="dropdown live-notification-center" id="liveNotificationCenter" data-feed-url="{{ route('account.notifications.feed') }}" data-read-all-url="{{ route('account.notifications.read-all') }}">
    <button class="live-notification-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        <span id="liveNotificationBadge" class="live-notification-badge {{ $liveUnreadCount ? '' : 'd-none' }}">{{ $liveUnreadCount }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end live-notification-menu">
        <div class="live-notification-heading"><strong>Notifications</strong><button type="button" id="markAllNotificationsRead" class="btn btn-link btn-sm">Mark all read</button></div>
        <div id="liveNotificationList">
            @forelse($liveNotifications as $notification)
                <button type="button" class="live-notification-item {{ $notification->read_at ? '' : 'is-unread' }}" data-notification-id="{{ $notification->id }}" data-read-url="{{ route('account.notifications.read', $notification) }}">
                    <strong>{{ $notification->data['title'] ?? 'Academic update' }}</strong><small>{{ $notification->created_at->diffForHumans() }}</small>
                </button>
            @empty<div class="live-notification-empty">No notifications yet.</div>@endforelse
        </div>
        <a class="live-notification-footer" href="{{ route('account.notifications.index') }}">View all notifications</a>
    </div>
</div>
<div class="live-toast-stack" id="liveNotificationToasts" aria-live="polite" aria-atomic="true"></div>
