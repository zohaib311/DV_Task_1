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
                'subject' => data_get($row, $subject.'.'.$field),
                'evidence' => $this->evidence($row->before, $row->after)];
        });

        return view('academic.audits.index', compact('audits'));
    }

    /**
     * The database retains full snapshots. The viewer receives a short,
     * readable comparison instead of raw database JSON.
     */
    private function evidence(?array $before, ?array $after): array
    {
        $before = $this->snapshotSummary($before);
        $after = $this->snapshotSummary($after);
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $label) {
            $old = $before[$label] ?? null;
            $new = $after[$label] ?? null;

            if ($old !== $new) {
                $changes[] = ['label' => $label, 'before' => $old, 'after' => $new];
            }
        }

        return ['changes' => $changes];
    }

    private function snapshotSummary(?array $snapshot): array
    {
        if (empty($snapshot)) {
            return [];
        }

        if (isset($snapshot['records']) && is_array($snapshot['records'])) {
            $counts = collect($snapshot['records'])->countBy('status')->filter()->map(
                fn ($count, $status) => ucfirst((string) $status).': '.$count
            )->implode(', ');

            return array_filter([
                'Session status' => $this->value($snapshot['status'] ?? null),
                'Revision' => $this->value($snapshot['revision'] ?? null),
                'Attendance records' => count($snapshot['records']).' student record(s)'.($counts ? ' — '.$counts : ''),
            ]);
        }

        if (isset($snapshot['marks']) && is_array($snapshot['marks'])) {
            $scored = collect($snapshot['marks'])->filter(fn ($mark) => ($mark['obtained'] ?? null) !== null)->count();

            return array_filter([
                'Assessment reference' => $this->reference('Assessment', $snapshot['assessment_id'] ?? null),
                'Saved marks' => count($snapshot['marks']).' student record(s), '.$scored.' score(s) entered',
            ]);
        }

        if (isset($snapshot['items']) && is_array($snapshot['items'])) {
            return array_filter([
                'Result status' => $this->value($snapshot['status'] ?? null),
                'Percentage' => isset($snapshot['percentage']) ? $snapshot['percentage'].'%' : null,
                'SGPA' => $this->value($snapshot['sgpa'] ?? null),
                'CGPA' => $this->value($snapshot['cgpa'] ?? null),
                'Course results' => count($snapshot['items']).' course result(s)',
                'Published at' => $this->value($snapshot['published_at'] ?? null),
            ]);
        }

        $labels = [
            'submission_id' => 'Submission reference',
            'assessment_id' => 'Assessment reference',
            'semester_result_id' => 'Semester result reference',
            'title' => 'Assessment title',
            'held_on' => 'Assessment date',
            'maximum' => 'Raw maximum',
            'weight' => 'Course weight',
            'submission_required' => 'Student submission required',
            'submissions_due_at' => 'Submission deadline',
            'marks_released_at' => 'Marks released at',
            'status' => 'Status',
            'revision' => 'Revision',
            'attendance' => 'Attendance allocation',
            'midterm' => 'Midterm allocation',
            'final' => 'Final allocation',
            'quiz' => 'Quiz allocation',
            'assignment' => 'Assignment allocation',
        ];
        $summary = [];

        foreach ($labels as $key => $label) {
            if (! array_key_exists($key, $snapshot)) {
                continue;
            }

            $summary[$label] = str_ends_with($key, '_id')
                ? $this->reference(str_replace(' reference', '', $label), $snapshot[$key])
                : $this->value($snapshot[$key]);
        }

        return array_filter($summary, fn ($value) => $value !== null && $value !== '');
    }

    private function reference(string $label, mixed $id): ?string
    {
        return $id === null ? null : $label.' #'.$id;
    }

    private function value(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
