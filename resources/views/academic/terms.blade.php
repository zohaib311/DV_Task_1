@extends('academic.layout')
@section('title', 'Academic Years & Terms')
@section('heading', 'Academic Years & Terms')
@section('description', 'Set the academic calendar before creating course offerings and student enrollments.')
@section('header-actions')
    <button class="btn academic-header-btn" data-bs-toggle="collapse" data-bs-target="#newYear" type="button"><i class="bi bi-plus-lg"></i> Academic year</button>
    <button class="btn academic-header-btn" data-bs-toggle="collapse" data-bs-target="#newTerm" type="button"><i class="bi bi-calendar-plus"></i> New term</button>
@endsection
@section('academic-content')
    <div class="collapse {{ $years->isEmpty() || (old('name') && !old('academic_year_id')) ? 'show' : '' }}" id="newYear">
        <form action="{{ route('academic.years.store') }}" method="POST" class="academic-form-section">
            @csrf
            <h2>Create an academic year</h2><p class="text-muted small">The date range defines which teaching terms belong to this year.</p>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="year_name">Year</label><input class="form-control" id="year_name" name="name" value="{{ old('academic_year_id') ? '' : old('name') }}" placeholder="2026-2027" pattern="\d{4}-\d{4}" required></div>
                <div class="col-md-4"><label class="form-label" for="year_start">Starts on</label><input class="form-control" id="year_start" name="starts_on" type="date" value="{{ old('academic_year_id') ? '' : old('starts_on') }}" required></div>
                <div class="col-md-4"><label class="form-label" for="year_end">Ends on</label><input class="form-control" id="year_end" name="ends_on" type="date" value="{{ old('academic_year_id') ? '' : old('ends_on') }}" required></div>
            </div><div class="academic-actions"><button class="btn btn-primary">Create academic year</button></div>
        </form>
    </div>
    <div class="collapse {{ old('academic_year_id') ? 'show' : '' }}" id="newTerm">
        <form action="{{ route('academic.terms.store') }}" method="POST" class="academic-form-section">@csrf<h2>Create a teaching term</h2>@include('academic.partials.term-fields')<div class="academic-actions"><button class="btn btn-primary" @disabled($years->isEmpty())>Create term</button></div></form>
    </div>
    <div class="academic-year-strip">@forelse ($years as $year)<span><strong>{{ $year->name }}</strong> {{ $year->starts_on->format('d M Y') }} – {{ $year->ends_on->format('d M Y') }}</span>@empty<span>Create an academic year to begin.</span>@endforelse</div>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Term</th><th>Academic year</th><th>Teaching dates</th><th>Offerings</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse ($terms as $term)
            <tr><td class="fw-semibold">{{ $term->name }}</td><td>{{ $term->academicYear->name }}</td><td>{{ $term->starts_on->format('d M Y') }} – {{ $term->ends_on->format('d M Y') }}</td><td>{{ $term->offerings_count }}</td><td><span class="academic-badge {{ $term->status === 'active' ? 'is-active' : '' }}">{{ ucfirst($term->status) }}</span></td><td><a href="{{ route('academic.terms.edit', $term) }}">{{ $term->status === 'closed' ? 'View' : 'Manage' }}</a></td></tr>
        @empty<tr><td colspan="6" class="academic-empty">No teaching terms yet. Add Fall, Spring, or Summer under an academic year.</td></tr>@endforelse
    </tbody></table></div>{{ $terms->links() }}
@endsection
