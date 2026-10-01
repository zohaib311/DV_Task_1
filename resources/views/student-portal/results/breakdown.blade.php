@if($item->assessment_snapshot)
    @foreach($item->assessment_snapshot['scheme'] as $component)<div>{{ ucfirst($component['code']) }}: {{ $item->assessment_snapshot['student']['components'][$component['code']] }} / {{ $component['allocation'] }}</div>@endforeach
@elseif($item->attendance_marks !== null)
    <div>Attendance: {{ $item->attendance_obtained_marks ?? 'N/A' }} / {{ $item->attendance_marks }}</div><div>Midterm: {{ $item->mid_obtained_marks ?? 'N/A' }} / {{ $item->mid_marks }}</div><div>Final: {{ $item->final_obtained_marks ?? 'N/A' }} / {{ $item->final_marks }}</div>
@else
    Historical total-only result
@endif
