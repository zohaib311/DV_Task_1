<div class="academic-note mb-4">
    <strong>Attendance policy</strong>
    <p class="mb-0 mt-1">Present: {{ $policy['present_credit'] * 100 }}% credit · Late: {{ $policy['late_credit'] * 100 }}% · Absent: {{ $policy['absent_credit'] * 100 }}% · Excused: {{ $policy['excused_excluded'] ? 'excluded from the denominator' : 'counts as zero credit' }}.</p>
    <small>Percentage = earned attendance credit ÷ counted sessions × 100. Marks = percentage × saved course attendance maximum ÷ 100. Draft/cancelled sessions are excluded. No counted sessions means N/A, not zero. These live totals do not rewrite published results.</small>
</div>
