<div class="portal-breakdown">
    @if($item->assessment_snapshot)
        @foreach($item->assessment_snapshot['scheme'] as $component)<div class="portal-breakdown-row"><span>{{ ucfirst($component['code']) }}: {{ $item->assessment_snapshot['student']['components'][$component['code']] }} / {{ $component['allocation'] }}</span></div>@endforeach
    @elseif($item->attendance_marks !== null)
        <div class="portal-breakdown-row"><span>Attendance: {{ $item->attendance_obtained_marks ?? 'N/A' }} / {{ $item->attendance_marks }}</span></div>
        <div class="portal-breakdown-row"><span>Midterm: {{ $item->mid_obtained_marks ?? 'N/A' }} / {{ $item->mid_marks }}</span></div>
        <div class="portal-breakdown-row"><span>Final: {{ $item->final_obtained_marks ?? 'N/A' }} / {{ $item->final_marks }}</span></div>
    @else
        <span class="text-muted">Historical total-only result</span>
    @endif
</div>
