@extends('academic.layout')
@section('title', 'Manage Academic Term')
@section('heading', 'Manage Academic Term')
@section('description', 'Keep the teaching calendar and enrollment availability up to date.')
@section('academic-content')
    <p class="academic-note"><i class="bi bi-info-circle"></i> Active terms accept enrollments. Once offerings exist, the term name, year, and dates are preserved. Closed terms are read-only.</p>
    <form method="POST" action="{{ route('academic.terms.update', $term) }}">@csrf @method('PUT')
        <fieldset @disabled($term->status === 'closed')>@include('academic.partials.term-fields')</fieldset>
        <div class="academic-actions"><a class="btn btn-light" href="{{ route('academic.terms.index') }}">Back</a>@if($term->status !== 'closed')<button class="btn btn-primary">Save term</button>@endif</div>
    </form>
@endsection
