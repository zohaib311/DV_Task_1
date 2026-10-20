@php
    $before = is_array($before ?? null) ? $before : [];
    $after = is_array($after ?? null) ? $after : [];

    $number = function ($value, int $precision = 2) {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? number_format((float) $value, $precision) : (string) $value;
    };

    $summaryValues = function (array $snapshot) use ($number) {
        return array_filter(
            [
                'Result status' => isset($snapshot['status']) ? ucfirst((string) $snapshot['status']) : null,
                'Semester percentage' => isset($snapshot['percentage']) ? $number($snapshot['percentage']) . '%' : null,
                'SGPA' => isset($snapshot['sgpa']) ? $number($snapshot['sgpa']) : null,
                'CGPA' => isset($snapshot['cgpa']) ? $number($snapshot['cgpa']) : null,
                'Revision' => isset($snapshot['revision']) ? (string) $snapshot['revision'] : null,
                'Course results saved' =>
                    isset($snapshot['items']) && is_array($snapshot['items'])
                        ? count($snapshot['items']) . ' course' . (count($snapshot['items']) === 1 ? '' : 's')
                        : null,
            ],
            fn($value) => $value !== null && $value !== '',
        );
    };

    $beforeSummary = $summaryValues($before);
    $afterSummary = $summaryValues($after);
    $summaryChanges = [];

    foreach (array_unique([...array_keys($beforeSummary), ...array_keys($afterSummary)]) as $label) {
        $old = $beforeSummary[$label] ?? null;
        $new = $afterSummary[$label] ?? null;

        if ($old !== $new) {
            $summaryChanges[] = compact('label', 'old', 'new');
        }
    }

    $courseKey = fn(array $item) => (string) ($item['course_code'] ??
        ($item['course_id'] ?? 'course-' . ($item['id'] ?? 'unknown')));
    $beforeCourses = collect($before['items'] ?? [])
        ->filter(fn($item) => is_array($item))
        ->keyBy($courseKey);
    $afterCourses = collect($after['items'] ?? [])
        ->filter(fn($item) => is_array($item))
        ->keyBy($courseKey);
    $courseChanges = [];

    $courseValues = function (?array $item) use ($number) {
        if (!$item) {
            return [];
        }

        $obtained = array_key_exists('obtained_marks', $item) ? $number($item['obtained_marks']) : null;
        $maximum = array_key_exists('total_marks', $item) ? $number($item['total_marks']) : null;

        return array_filter(
            [
                'Marks' => $obtained !== null ? $obtained . ($maximum !== null ? ' / ' . $maximum : '') : null,
                'Percentage' => isset($item['percentage']) ? $number($item['percentage']) . '%' : null,
                'Grade' => $item['grade'] ?? null,
                'Grade point' => isset($item['grade_point']) ? $number($item['grade_point']) : null,
                'Outcome' => isset($item['status']) ? ucfirst((string) $item['status']) : null,
            ],
            fn($value) => $value !== null && $value !== '',
        );
    };

    foreach (array_unique([...$beforeCourses->keys()->all(), ...$afterCourses->keys()->all()]) as $key) {
        $oldCourse = $beforeCourses->get($key);
        $newCourse = $afterCourses->get($key);
        $oldValues = $courseValues($oldCourse);
        $newValues = $courseValues($newCourse);
        $changes = [];

        foreach (array_unique([...array_keys($oldValues), ...array_keys($newValues)]) as $label) {
            $old = $oldValues[$label] ?? null;
            $new = $newValues[$label] ?? null;
            if ($old !== $new) {
                $changes[] = compact('label', 'old', 'new');
            }
        }

        if (empty($changes)) {
            continue;
        }

        $course = $newCourse ?: $oldCourse;
        $oldAssessments = collect(data_get($oldCourse, 'assessment_snapshot.student.assessments', []))
            ->filter(fn($assessment) => is_array($assessment))
            ->keyBy(
                fn(array $assessment) => (string) ($assessment['assessment_id'] ??
                    ($assessment['title'] ?? 'assessment')),
            );
        $newAssessments = collect(data_get($newCourse, 'assessment_snapshot.student.assessments', []))
            ->filter(fn($assessment) => is_array($assessment))
            ->keyBy(
                fn(array $assessment) => (string) ($assessment['assessment_id'] ??
                    ($assessment['title'] ?? 'assessment')),
            );
        $assessmentChanges = [];

        foreach (
            array_unique([...$oldAssessments->keys()->all(), ...$newAssessments->keys()->all()])
            as $assessmentKey
        ) {
            $oldAssessment = $oldAssessments->get($assessmentKey);
            $newAssessment = $newAssessments->get($assessmentKey);
            $oldMark =
                $oldAssessment && array_key_exists('obtained', $oldAssessment)
                    ? $number($oldAssessment['obtained'])
                    : null;
            $newMark =
                $newAssessment && array_key_exists('obtained', $newAssessment)
                    ? $number($newAssessment['obtained'])
                    : null;

            if ($oldMark === $newMark) {
                continue;
            }

            $assessment = $newAssessment ?: $oldAssessment;
            $maximum = isset($assessment['maximum']) ? $number($assessment['maximum']) : null;
            $assessmentChanges[] = [
                'label' =>
                    ($assessment['title'] ?? 'Assessment') .
                    (isset($assessment['component']) ? ' - ' . ucfirst((string) $assessment['component']) : ''),
                'old' => $oldMark === null ? null : $oldMark . ($maximum ? ' / ' . $maximum : ''),
                'new' => $newMark === null ? null : $newMark . ($maximum ? ' / ' . $maximum : ''),
            ];
        }

        $courseChanges[] = [
            'code' => $course['course_code'] ?? 'Course',
            'name' => $course['course_name'] ?? 'Saved course result',
            'changes' => $changes,
            'assessmentChanges' => $assessmentChanges,
        ];
    }
@endphp

<details class="result-audit-evidence">
    <summary>
        <span><i class="bi bi-clipboard2-check" aria-hidden="true"></i> Readable saved evidence</span>
        <small>{{ count($summaryChanges) + count($courseChanges) }} recorded
            change{{ count($summaryChanges) + count($courseChanges) === 1 ? '' : 's' }}</small>
    </summary>

    <div class="result-audit-evidence-panel">
        <section class="result-audit-section">
            <h4><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Semester summary</h4>
            <div class="result-audit-change-list">
                @forelse ($summaryChanges as $change)
                    <div class="result-audit-change">
                        <span>{{ $change['label'] }}</span>
                        <div class="result-audit-values">
                            <strong>{{ $change['old'] ?? 'Not saved' }}</strong>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            <strong>{{ $change['new'] ?? 'Not saved' }}</strong>
                        </div>
                    </div>
                @empty
                    <p class="result-audit-empty">No summary value changed in this entry.</p>
                @endforelse
            </div>
        </section>

        @if (count($courseChanges))
            <section class="result-audit-section">
                <h4><i class="bi bi-journal-check" aria-hidden="true"></i> Course outcomes</h4>
                <div class="result-audit-course-list">
                    @foreach ($courseChanges as $course)
                        <article class="result-audit-course">
                            <header><strong>{{ $course['code'] }}</strong><span>{{ $course['name'] }}</span></header>
                            @foreach ($course['changes'] as $change)
                                <div class="result-audit-change result-audit-course-change">
                                    <span>{{ $change['label'] }}</span>
                                    <div class="result-audit-values">
                                        <strong>{{ $change['old'] ?? 'Not saved' }}</strong>
                                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        <strong>{{ $change['new'] ?? 'Not saved' }}</strong>
                                    </div>
                                </div>
                            @endforeach
                            @if (count($course['assessmentChanges']))
                                <details class="result-audit-assessments">
                                    <summary>Assessment scores ({{ count($course['assessmentChanges']) }})</summary>
                                    <div>
                                        @foreach ($course['assessmentChanges'] as $assessment)
                                            <div class="result-audit-score">
                                                <span>{{ $assessment['label'] }}</span><strong>{{ $assessment['old'] ?? 'Not saved' }}
                                                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                                    {{ $assessment['new'] ?? 'Not saved' }}</strong></div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (!count($summaryChanges) && !count($courseChanges))
            <p class="result-audit-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i> A full snapshot was
                retained, but no displayable result value changed.</p>
        @endif
    </div>
</details>
