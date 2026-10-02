@extends('academic.layout')
@section('title', '8-Semester Program Plans')
@section('heading', '8-Semester Program Plans')
@section('description', 'Build an approved semester-wise course plan for every academic program.')
@section('header-actions')<a href="{{ route('academic.curricula.create') }}" class="btn academic-header-btn"><i class="bi bi-plus-lg"></i> Add semester plan</a>@endsection
@section('academic-content')
    <div class="academic-note mb-4"><i class="bi bi-map"></i> Create the program first, then configure Semester 1 through Semester 8. Approved plans are preserved for academic history.</div>
    @forelse($programs as $programPlan)
        @php($program = $programPlan['program'])
        <section class="academic-form-section mb-4">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><h2 class="mb-1">{{ $program->name }} <small class="text-muted">({{ $program->code }})</small></h2><p class="text-muted mb-0">{{ $program->department->name }} · Program curriculum map</p></div><span class="academic-badge is-active">{{ $programPlan['semesters']->filter(fn ($plan) => $plan->status === 'approved')->count() }} / {{ $program->total_semesters }} approved</span></div>
            <div class="table-responsive"><table class="table academic-table align-middle"><thead><tr><th>Semester</th><th>Course plan</th><th>Courses</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @foreach(range(1, $program->total_semesters) as $number)
                    @php($plan = $programPlan['semesters']->get("Semester $number"))
                    <tr><td><strong>Semester {{ $number }}</strong></td>
                    @if($plan)
                        <td>{{ $plan->version }}</td><td>{{ $plan->courses_count }}</td><td><span class="academic-badge {{ $plan->status === 'approved' ? 'is-active' : '' }}">{{ ucfirst($plan->status) }}</span></td><td><a href="{{ route('academic.curricula.edit', $plan) }}">{{ $plan->status === 'approved' ? 'View plan' : 'Complete plan' }}</a></td>
                    @else
                        <td class="text-muted">Not configured</td><td>—</td><td><span class="academic-badge">Missing</span></td><td><a href="{{ route('academic.curricula.create', ['program_id' => $program->id, 'semester' => 'Semester '.$number]) }}">Configure semester {{ $number }}</a></td>
                    @endif
                    </tr>
                @endforeach
            </tbody></table></div>
        </section>
    @empty
        <div class="academic-empty">No program plans yet. Create a program first, then configure its semester plan.</div>
    @endforelse
@endsection
