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

        $url = $item->data['url'] ?? null;
        $workspace = app(\App\Services\StudentPortal\StudentWorkspace::class);
        if (is_string($url) && $request->user()->can('student.result.view-own')) {
            $resultId = basename(parse_url($url, PHP_URL_PATH) ?: '');
            if (ctype_digit($resultId) && $url === route('student.results.show', $resultId)
                && $workspace->results($request->user())->whereKey($resultId)->exists()) {
                return redirect($url);
            }
        }
        if (is_string($url) && $request->user()->can('student.courses.view-own')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
            $enrollmentId = $query['enrollment'] ?? null;
            if (is_string($enrollmentId) && ctype_digit($enrollmentId)
                && $url === route('student.courses', ['enrollment' => $enrollmentId])
                && \App\Models\Enrollment\StudentSemesterEnrollment::whereKey($enrollmentId)
                    ->where('student_id', $request->user()->studentProfile->id)->exists()) {
                return redirect($url);
            }
        }
        // Only follow known, authorized portal destinations, never arbitrary stored URLs.
        foreach (['student.dashboard' => 'student.profile.view-own', 'student.courses' => 'student.courses.view-own',
            'student.attendance.index' => 'student.attendance.view-own', 'student.assessments.index' => 'student.assessments.view-own',
            'student.results.index' => 'student.result.view-own'] as $route => $permission) {
            if ($url === route($route) && $request->user()->can($permission)) {
                return redirect()->route($route);
            }
        }

        return redirect()->route('student.notifications.index');
    }
}
