@extends('results.moderation.layout')
@section('academic-content')
    @php
        $items = $result ? $result->items->toArray() : $preview['items'];
        $summary = $result ? $result->toArray() : $preview['summary'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between gap-3 border-bottom pb-3 mb-4">
        <div><h2 class="h5">{{ $enrollment->student->name }}</h2><span class="text-muted">{{ $enrollment->student->registration_no }} · {{ $enrollment->semester }} · {{ $enrollment->academic_year }} · {{ $enrollment->section?->name }}</span></div>
        <strong class="text-primary">{{ $result?->published_at ? 'Published · '.$result->status : ($result ? 'Approved / awaiting publication' : 'Semester review') }}</strong>
    </div>
    @if($result)<div class="academic-note mb-4">Saved snapshot · Revision {{ $result->revision }} · Reviewed {{ $result->reviewed_at?->format('d M Y H:i') }} @if($result->published_at) · Published {{ $result->published_at->format('d M Y H:i') }} @endif</div>@endif
    @if($preview && $preview['issues'])<div class="alert alert-warning" role="alert"><strong>Not ready for semester approval</strong><ul class="mb-0">@foreach($preview['issues'] as $issue)<li>{{ $issue }}</li>@endforeach</ul></div>@endif
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Course</th><th>Credits</th><th>Approved assessment breakdown</th><th>Obtained</th><th>%</th><th>Grade / GP</th><th>Outcome</th></tr></thead><tbody>
        @foreach($items as $item)
            <tr><td>{{ $item['course_name'] }}<small>{{ $item['course_code'] }} · Submission #{{ $item['assessment_submission_id'] }}</small></td><td>{{ $item['credit_hours'] }}</td><td>@foreach($item['assessment_snapshot']['scheme'] as $component)<div>{{ ucfirst($component['code']) }}: {{ number_format($item['assessment_snapshot']['student']['components'][$component['code']], 2) }} / {{ $component['allocation'] }}</div>@endforeach</td><td>{{ $item['obtained_marks'] }} / {{ $item['total_marks'] }}</td><td>{{ $item['percentage'] }}%</td><td>{{ $item['grade'] }} / {{ number_format($item['grade_point'], 2) }}</td><td>{{ $item['status'] }}</td></tr>
        @endforeach
    </tbody></table></div>
    @if($summary)<div class="d-flex flex-wrap gap-4 border-top border-bottom py-3 my-3"><span>Semester percentage <strong>{{ $summary['semester_percentage'] }}%</strong></span><span>SGPA <strong>{{ $summary['sgpa'] }} / 4.00</strong></span><span>CGPA <strong>{{ $result?->cgpa ?? 'Calculated on publication' }}</strong></span></div>@endif
    @foreach($items as $item)
        <details class="border-bottom py-3"><summary>Assessment evidence · {{ $item['course_code'] }}</summary>
            <div class="table-responsive mt-3"><table class="table academic-table"><thead><tr><th>Assessment</th><th>Component / date</th><th>Raw mark</th><th>Course weight</th></tr></thead><tbody>@foreach($item['assessment_snapshot']['student']['assessments'] as $assessment)<tr><td>{{ $assessment['title'] }}</td><td>{{ ucfirst($assessment['component']) }} · {{ $assessment['held_on'] }}</td><td>{{ $assessment['obtained'] }} / {{ $assessment['maximum'] }}</td><td>{{ $assessment['weight'] }}</td></tr>@endforeach</tbody></table></div>
            @if($attendance = $item['assessment_snapshot']['student']['attendance'])<p>Frozen attendance: {{ $attendance['percentage'] }}% · {{ $attendance['valid_sessions'] }} counted sessions · {{ $attendance['marks'] }} / {{ $attendance['maximum'] }} marks.</p>@endif
        </details>
    @endforeach
    @if(!$result && !$preview['issues'])
        @can('results.approve')<form class="mt-4" method="POST" action="{{ route('results.moderation.approve', $enrollment) }}">@csrf<input type="hidden" name="token" value="{{ $preview['token'] }}"><label for="review-note" class="form-label">Semester review note</label><textarea id="review-note" class="form-control" name="reason" required minlength="5" maxlength="1000">{{ old('reason') }}</textarea><div class="academic-actions"><button class="btn btn-primary">Approve semester sheet</button></div></form>@endcan
    @elseif($result && !$result->published_at)
        @can('results.publish')<form method="POST" action="{{ route('results.moderation.publish', $enrollment) }}">@csrf<input type="hidden" name="revision" value="{{ $result->revision }}"><div class="academic-actions"><button class="btn btn-primary">Publish approved result</button></div></form>@endcan
    @elseif($result)
        @can('update', $result) @can('results.publish') @can('results.edit-published')
            <details class="mt-4" @if(old('marks')) open @endif><summary class="text-primary fw-bold">Correct published assessment marks</summary><p class="text-muted mt-3">Corrections preserve the original submission and grading policy. Attendance evidence is read-only. The revised result remains published; changes and your reason are audited.</p>
                <form method="POST" action="{{ route('results.moderation.correct', $enrollment) }}">@csrf @method('PUT')<input type="hidden" name="revision" value="{{ old('revision', $result->revision) }}">
                    @foreach($result->items as $item)<h3 class="h6 mt-3">{{ $item->course_code }} · {{ $item->course_name }}</h3><div class="row g-3">@foreach($item->assessment_snapshot['student']['assessments'] as $assessment) @php($key = $item->id.'_'.$assessment['assessment_id'])<div class="col-sm-6 col-lg-4"><label class="form-label" for="mark-{{ $key }}">{{ $assessment['title'] }} / {{ $assessment['maximum'] }}</label><input id="mark-{{ $key }}" class="form-control" type="number" min="0" max="{{ $assessment['maximum'] }}" step="0.01" name="marks[{{ $key }}]" value="{{ old('marks.'.$key, $assessment['obtained']) }}" required></div>@endforeach</div>@endforeach
                    <label class="form-label mt-3" for="correction-reason">Correction reason</label><textarea class="form-control" id="correction-reason" name="reason" required minlength="5" maxlength="1000">{{ old('reason') }}</textarea><div class="academic-actions"><button class="btn btn-primary">Save audited correction</button></div>
                </form>
            </details>
        @endcan @endcan @endcan
    @endif
    @if($result) @can('audit.view')<details class="mt-4"><summary>Result audit history ({{ $result->audits->count() }})</summary><div class="table-responsive mt-3"><table class="table academic-table"><thead><tr><th>When / who</th><th>Action / reason</th><th>Change</th></tr></thead><tbody>@foreach($result->audits->sortByDesc('id') as $audit)<tr><td>{{ $audit->created_at }}<small>{{ $audit->updatedBy?->name ?? 'Former account' }}</small></td><td>{{ str_replace('_', ' ', ucfirst($audit->action)) }}<small>{{ $audit->reason }}</small></td><td>SGPA: {{ $audit->before['sgpa'] ?? '—' }} → {{ $audit->after['sgpa'] ?? '—' }}<details><summary>Saved evidence</summary><pre class="text-wrap">{{ json_encode(['before' => $audit->before, 'after' => $audit->after], JSON_PRETTY_PRINT) }}</pre></details></td></tr>@endforeach</tbody></table></div></details>@endcan @endif
@endsection
