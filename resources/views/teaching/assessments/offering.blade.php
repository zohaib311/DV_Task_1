@extends('teaching.layout')
@section('heading', $offering->course_code.' — Assessments & marks')
@section('description', $offering->course_name.' · '.$offering->term->name)
@section('header-actions')<a href="{{ route('teaching.assessments.index') }}" class="btn academic-header-btn">All classes</a>@endsection
@section('teaching-content')
    @if(!$offering->assessment_scheme_approved_at)
        <div class="academic-note mb-4">Ask an administrator to approve this offering's assessment scheme in Academic Setup → Course Offerings → Assessment scheme.</div>
    @endif
    <div class="teaching-scheme mb-4">
        <strong>Approved allocation</strong>
        @foreach($offering->assessmentComponents as $component)<span>{{ ucfirst($component->code) }} {{ $component->allocation }}</span>@endforeach
        <span>Status: {{ ucfirst(str_replace('_', ' ', $offering->status)) }}</span>
    </div>
    @can('assessments.manage-assigned')
        @if($offering->assessment_scheme_approved_at && $offering->status === 'active' && $offering->term->status === 'active')
            <details class="academic-form-section"><summary class="fw-semibold">Create assessment</summary>
                <p class="text-muted small mt-3">Example: an assignment scored out of 20 can contribute 5 marks to the course. The assessment weights must fit the approved component allocation.</p>
                <form method="POST" enctype="multipart/form-data" action="{{ route('teaching.assessments.store', $offering) }}">
                    @csrf
                    <label class="form-label" for="assessment-component">Component</label>
                    <select class="form-select mb-3" id="assessment-component" name="component_id" required><option value="">Choose component</option>@foreach($offering->assessmentComponents->where('code', '!=', 'attendance') as $component)<option value="{{ $component->id }}" @selected(old('component_id') == $component->id)>{{ ucfirst($component->code) }} — {{ $component->allocation }} allocated, {{ $component->assessments->sum('weight') }} used</option>@endforeach</select>
                    @include('teaching.assessments.partials.definition-fields')
                    <div class="academic-actions"><button class="btn btn-primary" type="submit">Create assessment</button></div>
                </form>
            </details>
        @endif
    @endcan
    <h2>Assessment register</h2>
    <div class="table-responsive"><table class="table academic-table align-middle">
        <thead><tr><th>Assessment</th><th>Component</th><th>Date</th><th>Raw maximum</th><th>Weight</th><th>Action</th></tr></thead>
        <tbody>@forelse($offering->assessmentComponents->flatMap->assessments as $item)
            <tr><td>{{ $item->title }}@if($item->question_file_path)<small><a href="{{ route('teaching.assessments.question-file', [$offering, $item]) }}">{{ $item->question_original_filename }}</a></small>@endif</td><td>{{ ucfirst($offering->assessmentComponents->firstWhere('id', $item->assessment_component_id)->code) }}</td><td>{{ $item->held_on->format('d M Y') }}</td><td>{{ $item->maximum }}</td><td>{{ $item->weight }}</td><td><a href="{{ route('teaching.assessments.edit', [$offering, $item]) }}">Open marks</a></td></tr>
        @empty<tr><td colspan="6" class="academic-empty">No assessments created yet. Attendance is calculated separately from sessions.</td></tr>@endforelse</tbody>
    </table></div>
    <h2 class="mt-4">Course marks preview</h2>
    @include('assessments.partials.preview')
    @can('results.submit')
        @if($offering->status === 'active')
            <form method="POST" action="{{ route('teaching.assessments.submit', $offering) }}" class="academic-actions">@csrf<button class="btn btn-primary" @disabled(!$preview['complete'])>Submit course marks for review</button></form>
        @endif
    @endcan
    <h2 class="mt-4">Submission history</h2>
    @forelse($submissions as $submission)
        <details class="teaching-footnote"><summary>#{{ $submission->id }} · {{ ucfirst($submission->status) }} · {{ $submission->created_at->format('d M Y H:i') }}</summary>
            <p>{{ $submission->review_note ?? 'Awaiting review' }}</p>
            @include('assessments.partials.preview', ['preview' => $submission->snapshot])
        </details>
    @empty<p class="text-muted small">No submissions yet.</p>@endforelse
    <h2 class="mt-4">Change history</h2>
    @foreach($audits as $audit)
        <details class="teaching-footnote"><summary>{{ ucfirst(str_replace('_', ' ', $audit->action)) }} · {{ $audit->user?->name ?? 'Former account' }} · {{ $audit->created_at->format('d M Y H:i') }}</summary>
            <p>{{ $audit->reason }}</p><pre class="small overflow-auto">{{ json_encode(['before' => $audit->before, 'after' => $audit->after], JSON_PRETTY_PRINT) }}</pre>
        </details>
    @endforeach
    <div class="mt-3">{{ $audits->links('pagination::bootstrap-5') }}</div>
@endsection
