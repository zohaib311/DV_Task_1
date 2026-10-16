@extends('academic.layout')

@section('workspace-eyebrow', 'ACADEMIC OVERSIGHT')
@section('heading', 'Academic audit viewer')
@section('description', 'Read-only evidence for attendance, assessment, and semester-result changes.')

@section('workspace-navigation')
    <nav class="academic-tabs">
        @can('reports.view')
            <a href="{{ route('academic.reports.index') }}">Reports</a>
        @endcan
        <a class="active" href="{{ route('academic.audits.index') }}">Audit viewer</a>
    </nav>
@endsection

@section('academic-content')
    <form method="GET" class="row g-3 mb-4 audit-filter">
        <div class="col-md-4">
            <label for="type" class="form-label">Record type</label>
            <select id="type" class="form-select" name="type">
                <option value="">All audit records</option>
                @foreach (['result' => 'Result', 'assessment' => 'Assessment', 'attendance' => 'Attendance'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label for="search" class="form-label">Search action, actor, reason, or subject</label>
            <input id="search" class="form-control" name="search" value="{{ request('search') }}" maxlength="100"
                placeholder="e.g. approved, Hamza, CS101">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary">Filter records</button>
            @if (request()->filled('type') || request()->filled('search'))
                <a href="{{ route('academic.audits.index') }}" class="btn btn-light">Reset</a>
            @endif
        </div>
    </form>

    <div class="academic-note audit-note mb-3">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <div><strong>What is saved evidence?</strong> It records the previous and updated values for an important action. These entries are read-only and help administrators verify academic changes.</div>
    </div>

    <div class="table-responsive">
        <table class="table academic-table audit-table align-middle">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Type / subject</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Reason</th>
                    <th>Saved evidence</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($audits as $audit)
                    <tr>
                        <td class="audit-when"><i class="bi bi-clock-history" aria-hidden="true"></i>{{ $audit['at']?->format('d M Y H:i') ?? '—' }}</td>
                        <td><span class="audit-type">{{ $audit['type'] }}</span><small>{{ $audit['subject'] ?? 'Historical record' }}</small></td>
                        <td>{{ $audit['actor'] ?? 'Former account' }}</td>
                        <td><span class="audit-action">{{ str_replace('_', ' ', ucfirst($audit['action'])) }}</span></td>
                        <td class="audit-reason">{{ $audit['reason'] ?? 'No reason was required for this action.' }}</td>
                        <td>
                            <details class="audit-evidence">
                                <summary>
                                    <span><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Before / after</span>
                                    <small>{{ count($audit['evidence']['changes']) }} saved change{{ count($audit['evidence']['changes']) === 1 ? '' : 's' }}</small>
                                </summary>
                                <div class="audit-evidence-panel">
                                    @forelse ($audit['evidence']['changes'] as $change)
                                        <div class="audit-change">
                                            <span class="audit-change-label">{{ $change['label'] }}</span>
                                            <div class="audit-change-values">
                                                <div><small>Before</small><strong>{{ $change['before'] ?? 'No previous value' }}</strong></div>
                                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                                <div><small>After</small><strong>{{ $change['after'] ?? 'No saved value' }}</strong></div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="audit-no-change"><i class="bi bi-check2-circle" aria-hidden="true"></i> A snapshot was retained, but no displayable value changed.</div>
                                    @endforelse
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="academic-empty">
                            <i class="bi bi-search d-block fs-3 mb-2" aria-hidden="true"></i>
                            <strong>No audit records match this filter.</strong>
                            <small>Try a different record type or search term.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $audits->links('pagination::bootstrap-5') }}
@endsection
