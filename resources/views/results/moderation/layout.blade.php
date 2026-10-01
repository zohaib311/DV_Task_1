@extends('academic.layout')
@section('workspace-eyebrow', 'ACADEMIC RECORDS')
@section('workspace-navigation')
    <nav class="academic-tabs" aria-label="Results"><a href="{{ route('allResults') }}">Student results</a><a class="active" href="{{ route('results.moderation.index') }}">Moderation &amp; publication</a>@can('assessments.review')<a href="{{ route('academic.assessment-reviews.index') }}">Course submission reviews</a>@endcan</nav>
@endsection
@section('heading', 'Semester result moderation')
@section('description', 'Approved teacher marks. Reviewed semester sheets. Controlled publication.')
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('allResults') }}">Results</a><a class="btn academic-header-btn" href="{{ route('results.moderation.index') }}">Moderation queue</a>@endsection
