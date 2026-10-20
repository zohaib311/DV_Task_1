@php
    $displayValue = function ($key, $value) {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (in_array($key, ['held_on', 'submissions_due_at', 'marks_released_at'], true)) {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format($key === 'held_on' ? 'd M Y' : 'd M Y, H:i');
            } catch (\Throwable) {
                // Keep legacy audit values visible even if an old value cannot be parsed as a date.
            }
        }

        return (string) $value;
    };
    $summarize = function ($snapshot) use ($displayValue) {
        if (empty($snapshot) || !is_array($snapshot)) {
            return [];
        }

        if (isset($snapshot['records']) && is_array($snapshot['records'])) {
            $counts = collect($snapshot['records'])
                ->countBy('status')
                ->filter()
                ->map(fn($count, $status) => ucfirst((string) $status) . ': ' . $count)
                ->implode(', ');

            return array_filter([
                'Session status' => $snapshot['status'] ?? null,
                'Revision' => isset($snapshot['revision']) ? (string) $snapshot['revision'] : null,
                'Attendance records' =>
                    count($snapshot['records']) . ' student record(s)' . ($counts ? ' — ' . $counts : ''),
            ]);
        }

        if (isset($snapshot['marks']) && is_array($snapshot['marks'])) {
            $scored = collect($snapshot['marks'])->filter(fn($mark) => ($mark['obtained'] ?? null) !== null)->count();

            return array_filter([
                'Assessment reference' => isset($snapshot['assessment_id'])
                    ? 'Assessment #' . $snapshot['assessment_id']
                    : null,
                'Saved marks' => count($snapshot['marks']) . ' student record(s), ' . $scored . ' score(s) entered',
            ]);
        }

        if (isset($snapshot['items']) && is_array($snapshot['items'])) {
            return array_filter([
                'Result status' => $snapshot['status'] ?? null,
                'Percentage' => isset($snapshot['percentage']) ? $snapshot['percentage'] . '%' : null,
                'SGPA' => isset($snapshot['sgpa']) ? (string) $snapshot['sgpa'] : null,
                'CGPA' => isset($snapshot['cgpa']) ? (string) $snapshot['cgpa'] : null,
                'Course results' => count($snapshot['items']) . ' course result(s)',
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
            if (!array_key_exists($key, $snapshot)) {
                continue;
            }

            $value = $snapshot[$key];
            if (str_ends_with($key, '_id')) {
                $value = $value === null ? null : str_replace(' reference', '', $label) . ' #' . $value;
            } else {
                $value = $displayValue($key, $value);
            }
            if ($value !== null && $value !== '') {
                $summary[$label] = $value;
            }
        }

        return $summary;
    };
    $beforeSummary = $summarize($before ?? null);
    $afterSummary = $summarize($after ?? null);
    $changes = [];

    foreach (array_unique([...array_keys($beforeSummary), ...array_keys($afterSummary)]) as $label) {
        $old = $beforeSummary[$label] ?? null;
        $new = $afterSummary[$label] ?? null;
        if ($old !== $new) {
            $changes[] = ['label' => $label, 'before' => $old, 'after' => $new];
        }
    }
@endphp

<div class="teaching-history-evidence">
    @forelse ($changes as $change)
        <div class="teaching-history-change">
            <span class="teaching-history-label">{{ $change['label'] }}</span>
            <div class="teaching-history-values">
                <div><small>Before</small><strong>{{ $change['before'] ?? 'No previous value' }}</strong></div>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <div><small>After</small><strong>{{ $change['after'] ?? 'No saved value' }}</strong></div>
            </div>
        </div>
    @empty
        <div class="teaching-history-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i> A snapshot was retained,
            but no displayable value changed.</div>
    @endforelse
</div>
