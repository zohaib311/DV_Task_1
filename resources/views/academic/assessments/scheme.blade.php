@extends('academic.layout')
@section('heading', 'Assessment scheme — '.$offering->course_code)
@section('description', $offering->course_name.' · '.$offering->term->name.' · Section '.$offering->section->name)
@section('header-actions')<a class="btn academic-header-btn" href="{{ route('academic.offerings.index') }}">Course offerings</a>@endsection
@section('academic-content')
    <div class="academic-note mb-4">
        <strong>{{ $offering->assessment_scheme_approved_at ? 'Approved scheme — read-only' : 'Review the allocation before approving' }}</strong>
        <p class="mb-0 mt-2">Components must total {{ $offering->total_marks }} marks. Attendance keeps its saved {{ $offering->attendance_marks }} marks. Set unused components to zero. Approval is permanent for this offering and switches it to teacher-managed assessments; legacy semester entry is then unavailable until the Phase 9F publication integration.</p>
    </div>
    @php($saved = $offering->assessmentComponents->pluck('allocation', 'code'))
    <form method="POST" action="{{ route('academic.assessment-schemes.store', $offering) }}">
        @csrf
        <fieldset @disabled($offering->assessment_scheme_approved_at)>
            <div class="table-responsive"><table class="table academic-table align-middle">
                <thead><tr><th>Component</th><th>Allocation (course marks)</th><th>Source</th></tr></thead>
                <tbody>@foreach($codes as $code)
                    @php($default = $offering->assessment_scheme_approved_at ? ($saved[$code] ?? 0) : match($code) { 'attendance' => $offering->attendance_marks, 'midterm' => $offering->mid_marks, 'final' => $offering->final_marks, default => 0 })
                    <tr><td><label for="allocation-{{ $code }}">{{ ucfirst($code) }}</label></td><td><input class="form-control" id="allocation-{{ $code }}" name="allocations[{{ $code }}]" type="number" min="0" max="{{ $offering->total_marks }}" step="0.01" value="{{ old('allocations.'.$code, $default) }}" @readonly($code === 'attendance') required></td><td>{{ $code === 'attendance' ? 'Calculated from attendance sessions' : 'Teacher assessments within this allocation' }}</td></tr>
                @endforeach</tbody>
            </table></div>
            @unless($offering->assessment_scheme_approved_at)<div class="academic-actions"><button class="btn btn-primary" type="submit">Approve &amp; lock scheme</button></div>@endunless
        </fieldset>
    </form>
@endsection
