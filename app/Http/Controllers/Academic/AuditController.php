<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Assessment\AssessmentAudit;
use App\Models\Attendance\AttendanceAudit;
use App\Models\Result\SemesterResultAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:result,assessment,attendance'], 'search' => ['nullable', 'string', 'max:100']]);
        $sources = [
            'result' => [SemesterResultAudit::class, 'updatedBy', 'semesterResult.enrollment.student', 'registration_no'],
            'assessment' => [AssessmentAudit::class, 'user', 'offering', 'course_code'],
            'attendance' => [AttendanceAudit::class, 'user', 'session.offering', 'course_code'],
        ];
        $union = null;
        foreach ($sources as $type => [$model, $actor, $subject, $field]) {
            if (! empty($data['type']) && $data['type'] !== $type) {
                continue;
            }
            $query = $model::query()->select('id', 'created_at')->selectRaw('? as audit_type', [$type]);
            $search = mb_strtolower(trim($data['search'] ?? ''));
            if ($search !== '' && ! str_contains($type, $search)) {
                $like = '%'.$search.'%';
                $query->where(function ($query) use ($like, $actor, $subject, $field) {
                    $query->whereRaw('LOWER(action) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(reason) LIKE ?', [$like])
                        ->orWhereHas($actor, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$like]))
                        ->orWhereHas($subject, fn ($q) => $q->whereRaw('LOWER('.$field.') LIKE ?', [$like]));
                });
            }
            $union = $union ? $union->unionAll($query->toBase()) : $query->toBase();
        }
        // Page metadata first; load JSON evidence and relationships only for visible rows.
        $audits = DB::query()->fromSub($union, 'audit_entries')->orderByDesc('created_at')
            ->orderBy('audit_type')->orderByDesc('id')->paginate(25)->withQueryString();
        $records = [];
        foreach ($sources as $type => [$model, $actor, $subject]) {
            $ids = $audits->getCollection()->where('audit_type', $type)->pluck('id');
            $records[$type] = $model::with([$actor, $subject])->whereIn('id', $ids)->get()->keyBy('id');
        }
        $audits->through(function ($entry) use ($records, $sources) {
            $row = $records[$entry->audit_type][$entry->id];
            [, $actor, $subject, $field] = $sources[$entry->audit_type];

            return ['type' => ucfirst($entry->audit_type), 'at' => $row->created_at,
                'actor' => $row->$actor?->name, 'action' => $row->action, 'reason' => $row->reason,
                'subject' => data_get($row, $subject.'.'.$field), 'before' => $row->before, 'after' => $row->after];
        });

        return view('academic.audits.index', compact('audits'));
    }
}
