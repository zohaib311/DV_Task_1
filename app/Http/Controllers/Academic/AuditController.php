<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Assessment\AssessmentAudit;
use App\Models\Attendance\AttendanceAudit;
use App\Models\Result\SemesterResultAudit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:result,assessment,attendance'], 'search' => ['nullable', 'string', 'max:100']]);
        $rows = collect();
        $type = $data['type'] ?? null;
        if (! $type || $type === 'result') {
            $rows = $rows->concat(SemesterResultAudit::with(['updatedBy', 'semesterResult.enrollment.student'])->latest()->get()->map(fn ($row) => ['type' => 'Result', 'at' => $row->created_at, 'actor' => $row->updatedBy?->name, 'action' => $row->action, 'reason' => $row->reason, 'subject' => $row->semesterResult?->enrollment?->student?->registration_no, 'before' => $row->before, 'after' => $row->after]));
        }
        if (! $type || $type === 'assessment') {
            $rows = $rows->concat(AssessmentAudit::with(['user', 'offering'])->latest()->get()->map(fn ($row) => ['type' => 'Assessment', 'at' => $row->created_at, 'actor' => $row->user?->name, 'action' => $row->action, 'reason' => $row->reason, 'subject' => $row->offering?->course_code, 'before' => $row->before, 'after' => $row->after]));
        }
        if (! $type || $type === 'attendance') {
            $rows = $rows->concat(AttendanceAudit::with(['user', 'session.offering'])->latest()->get()->map(fn ($row) => ['type' => 'Attendance', 'at' => $row->created_at, 'actor' => $row->user?->name, 'action' => $row->action, 'reason' => $row->reason, 'subject' => $row->session?->offering?->course_code, 'before' => $row->before, 'after' => $row->after]));
        }
        $search = strtolower($data['search'] ?? '');
        if ($search !== '') {
            $rows = $rows->filter(fn ($row) => str_contains(strtolower(implode(' ', [$row['type'], $row['actor'], $row['action'], $row['reason'], $row['subject']])), $search));
        }

        return view('academic.audits.index', ['audits' => $rows->sortByDesc('at')->values()]);
    }
}
