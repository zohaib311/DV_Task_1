<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->studentProfile, 403, 'Please ask an administrator to link your account to your student profile.');

        return view('student-portal.notifications.index', ['notifications' => $request->user()->notifications()->latest()->paginate(20)]);
    }

    public function read(Request $request, string $notification)
    {
        abort_unless($request->user()->studentProfile, 403, 'Please ask an administrator to link your account to your student profile.');
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return redirect($item->data['url'] ?? route('student.dashboard'));
    }
}
