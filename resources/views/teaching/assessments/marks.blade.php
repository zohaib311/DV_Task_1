@extends('teaching.layout')
@section('heading', $assessment->title.' — Marks')
@section('description', $offering->course_code.' · '.ucfirst($assessment->component->code).' · Raw maximum '.$assessment->maximum.' · Weight '.$assessment->weight)
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('teaching.assessments.show', $offering) }}">Back to assessments</a>@endsection
@section('teaching-content')
    <div class="academic-note mb-4">Blank means incomplete; enter 0 for an assessed zero. Saved score corrections require a reason. Submitted/reviewed classes and finalized enrollments are read-only.</div>
    @can('assessments.manage-assigned')
        @if(!$locked && !$assessment->marks->whereNotNull('obtained')->count())
            <details class="academic-form-section"><summary>Edit unmarked assessment definition</summary>
                <form method="POST" action="{{ route('teaching.assessments.update', [$offering, $assessment]) }}" class="mt-3">
                    @csrf @method('PUT')
                    <input type="hidden" name="revision" value="{{ old('revision', $assessment->revision) }}"><input type="hidden" name="component_id" value="{{ $assessment->assessment_component_id }}">
                    @include('teaching.assessments.partials.definition-fields')
                    <div class="academic-actions"><button class="btn btn-primary">Update definition</button></div>
                </form>
            </details>
        @endif
    @endcan
    <form method="POST" action="{{ route('teaching.assessments.marks', [$offering, $assessment]) }}">
        @csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ old('revision', $assessment->revision) }}">
        <fieldset @disabled($locked || !auth()->user()->can('marks.manage-assigned') || $assessment->held_on->isAfter(today()))>
            <legend class="h6">Enrolled students · {{ $courses->count() }}</legend>
            <div class="table-responsive"><table class="table academic-table align-middle">
                <thead><tr><th>Student</th><th>Registration no.</th><th>Obtained / {{ $assessment->maximum }}</th></tr></thead>
                <tbody>@foreach($courses as $index => $course)
                    <tr><td>{{ $course->enrollment->student->name }}</td><td>{{ $course->enrollment->student->registration_no }}</td><td><input type="hidden" name="marks[{{ $index }}][course_id]" value="{{ $course->id }}"><input class="form-control" type="number" name="marks[{{ $index }}][obtained]" min="0" max="{{ $assessment->maximum }}" step="0.01" aria-label="Marks for {{ $course->enrollment->student->name }}" value="{{ old('marks.'.$index.'.obtained', $assessment->marks->firstWhere('student_enrollment_course_id', $course->id)?->obtained) }}"></td></tr>
                @endforeach</tbody>
            </table></div>
            <label class="form-label" for="marks-reason">Correction reason (required when changing saved scores)</label><input class="form-control" id="marks-reason" name="reason" maxlength="1000" value="{{ old('reason') }}">
            <div class="academic-actions"><button class="btn btn-primary">Save marks</button></div>
        </fieldset>
    </form>
@endsection
