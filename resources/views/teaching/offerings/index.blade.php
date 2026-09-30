@extends('teaching.layout')
@section('heading', 'My assigned courses')
@section('description', 'Browse your classes by term and open their student rosters.')
@section('teaching-content')
    <form method="GET" action="{{ route('teaching.offerings.index') }}" class="teaching-filters">
        <div><label for="course-search" class="form-label">Find a course</label><input id="course-search" type="search" name="q" maxlength="100" value="{{ request('q') }}" class="form-control" placeholder="Course name or code"></div>
        <div><label for="term-filter" class="form-label">Teaching term</label><select id="term-filter" name="term_id" class="form-select"><option value="">All assigned terms</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>{{ $term->name }} · {{ $term->academicYear->name }}</option>@endforeach</select></div>
        <div><label for="status-filter" class="form-label">Offering status</label><select id="status-filter" name="status" class="form-select"><option value="">All statuses</option>@foreach(['planned', 'active', 'marks_submitted', 'reviewed', 'completed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
        <div class="teaching-filter-actions"><button class="btn btn-primary" type="submit">Apply filters</button><a href="{{ route('teaching.offerings.index') }}" class="btn btn-light">Reset</a></div>
    </form>
    <div class="teaching-section-heading"><h2>Assigned offerings</h2><span>{{ $offerings->total() }} matching offerings</span></div>
    @include('teaching.partials.offering-table')
    <div class="mt-4">{{ $offerings->links('pagination::bootstrap-5') }}</div>
@endsection
