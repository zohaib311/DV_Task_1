<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notifications.index', ['notifications' => $request->user()->notifications()->latest()->paginate(25)]);
    }

    public function feed(Request $request)
    {
        $items = $request->user()->notifications()->latest()->limit(10)->get();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $items->map(fn ($item) => [
                'id' => $item->id, 'title' => $item->data['title'] ?? 'Academic update',
                'message' => $item->data['message'] ?? '', 'category' => $item->data['category'] ?? 'academic',
                'read' => $item->read_at !== null, 'created_at' => $item->created_at->toIso8601String(),
                'created_label' => $item->created_at->diffForHumans(), 'read_url' => route('account.notifications.read', $item),
            ])->values(),
        ]);
    }

    public function read(Request $request, string $notification)
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();
        $destination = $this->destination($item->data['url'] ?? null);

        return $request->expectsJson() ? response()->json(['redirect_url' => $destination]) : redirect($destination);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $request->expectsJson() ? response()->json(['unread_count' => 0]) : back()->with('success', 'All notifications marked as read.');
    }

    private function destination(mixed $url): string
    {
        if (! is_string($url) || $url === '') return route('account.notifications.index');
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $host !== parse_url(config('app.url'), PHP_URL_HOST)) return route('account.notifications.index');
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        if (! str_starts_with($path, '/')) return route('account.notifications.index');
        $query = parse_url($url, PHP_URL_QUERY);

        return url($path.($query ? '?'.$query : ''));
    }
}
