@if(!empty($preview['issues']))
    <div class="academic-note mb-3"><strong>Not ready for submission</strong><ul class="mb-0 mt-2">@foreach($preview['issues'] as $issue)<li>{{ $issue }}</li>@endforeach</ul></div>
@endif
<div class="table-responsive">
    <table class="table academic-table align-middle">
        <thead><tr><th>Student</th>@foreach($preview['scheme'] as $component)<th>{{ ucfirst($component['code']) }} / {{ $component['allocation'] }}</th>@endforeach<th>Total</th><th>Percentage</th><th>Evidence</th></tr></thead>
        <tbody>@forelse($preview['students'] as $row)
            <tr>
                <td><strong>{{ $row['student_name'] }}</strong><small>{{ $row['registration_no'] }}</small></td>
                @foreach($preview['scheme'] as $component)<td>{{ $row['components'][$component['code']] ?? 'Incomplete' }}</td>@endforeach
                <td>{{ $row['obtained'] ?? 'Incomplete' }} / {{ $row['maximum'] }}</td><td>{{ $row['percentage'] === null ? '—' : $row['percentage'].'%' }}</td>
                <td><details><summary>Breakdown</summary>
                    @foreach($row['assessments'] as $item)<small>{{ $item['title'] }}: {{ $item['obtained'] ?? 'Blank' }} / {{ $item['maximum'] }} → weight {{ $item['weight'] }}</small>@endforeach
                    @if($row['attendance'])<small>Attendance: {{ $row['attendance']['percentage'] ?? 'N/A' }}% · {{ $row['attendance']['valid_sessions'] }} counted sessions</small>@endif
                </details></td>
            </tr>
        @empty<tr><td colspan="{{ count($preview['scheme']) + 4 }}" class="academic-empty">No enrolled students.</td></tr>@endforelse</tbody>
    </table>
</div>
<p class="text-muted small">Component score = sum of (raw score ÷ assessment maximum × assessment weight). Blank marks remain incomplete. Course totals are previews, not published semester results.</p>
