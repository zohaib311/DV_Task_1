@extends('results.moderation.layout')
@section('academic-content')
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6"><label for="search" class="form-label">Student name or registration number</label><input id="search" class="form-control" name="search" value="{{ request('search') }}" maxlength="100"></div>
        <div class="col-md-4"><label for="state" class="form-label">Stage</label><select id="state" class="form-select" name="state"><option value="">All stages</option>@foreach(['pending' => 'Awaiting semester review', 'approved' => 'Approved / unpublished', 'published' => 'Published'] as $value => $label)<option value="{{ $value }}" @selected(request('state') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary">Filter</button></div>
    </form>
    <div class="academic-note mb-3">Every enrolled course must have an approved teacher submission before semester approval. Course approval and semester publication are separate steps.</div>
    <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Student</th><th>Semester / term</th><th>Section</th><th>Stage</th><th>Action</th></tr></thead><tbody>
        @forelse($enrollments as $enrollment)
            <tr><td>{{ $enrollment->student->name }}<small>{{ $enrollment->student->registration_no }}</small></td><td>{{ $enrollment->semester }}<small>{{ $enrollment->term?->name }} · {{ $enrollment->academic_year }}</small></td><td>{{ $enrollment->section?->name }}</td><td>{{ $enrollment->semesterResult?->published_at ? 'Published' : ($enrollment->semesterResult?->reviewed_at ? 'Approved / unpublished' : 'Awaiting semester review') }}</td><td><a href="{{ route('results.moderation.show', $enrollment) }}">Open semester sheet</a></td></tr>
        @empty<tr><td colspan="5" class="academic-empty">No teacher-managed semester enrollments match these filters.</td></tr>@endforelse
    </tbody></table></div>
    {{ $enrollments->links('pagination::bootstrap-5') }}
@endsection
