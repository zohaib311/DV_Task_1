@extends('student-portal.layout')
@section('heading', 'My academic notifications')
@section('student-content')
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>When</th><th>Update</th><th>Status</th><th>Open</th></tr></thead><tbody>@forelse($notifications as $notification)<tr><td>{{ $notification->created_at->format('d M Y H:i') }}</td><td><strong>{{ $notification->data['title'] }}</strong><small>{{ $notification->data['message'] }}</small></td><td>{{ $notification->read_at ? 'Read' : 'New' }}</td><td><form method="POST" action="{{ route('student.notifications.read', $notification) }}">@csrf<button class="btn btn-sm btn-primary">{{ $notification->data['url'] ? 'Open update' : 'Mark read' }}</button></form></td></tr>@empty<tr><td colspan="4" class="academic-empty">No academic notifications yet.</td></tr>@endforelse</tbody></table></div>{{ $notifications->links('pagination::bootstrap-5') }}
@endsection
