@extends('academic.layout')
@section('heading', 'Review '.$submission->offering->course_code.' — #'.$submission->id)
@section('description', $submission->offering->term->name.' · Section '.$submission->offering->section->name.' · '.ucfirst($submission->status))
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('academic.assessment-reviews.index') }}">Review queue</a>@endsection
@section('academic-content')
    <div class="academic-note mb-4">This is the immutable snapshot submitted by {{ $submission->submitter?->name ?? 'a former account' }} on {{ $submission->created_at->format('d M Y H:i') }}. Review does not change raw marks or publish semester results.</div>
    @include('assessments.partials.preview', ['preview' => $submission->snapshot])
    @if($submission->status === 'submitted')
        <form method="POST" action="{{ route('academic.assessment-reviews.update', $submission) }}">
            @csrf
            <label for="review-reason" class="form-label">Review note / correction instructions</label><textarea id="review-reason" class="form-control" name="reason" minlength="5" maxlength="1000" required>{{ old('reason') }}</textarea>
            <div class="academic-actions"><button class="btn btn-outline-secondary" name="decision" value="returned">Return for correction</button><button class="btn btn-primary" name="decision" value="approved">Approve course submission</button></div>
        </form>
    @else
        <p class="academic-note">{{ ucfirst($submission->status) }} by {{ $submission->reviewer?->name ?? 'Former account' }}: {{ $submission->review_note }}</p>
        @if($submission->status === 'approved') @can('results.view-all')<a class="btn btn-primary" href="{{ route('results.moderation.index') }}">Continue to semester moderation</a>@endcan @endif
    @endif
@endsection
