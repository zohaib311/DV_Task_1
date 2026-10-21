@extends('student-portal.layout')
@section('heading', 'My results & academic history')
@section('description', 'Review your officially published semester outcomes, SGPA, CGPA, and result sheets.')
@section('header-actions')
    <a class="btn academic-header-btn" href="{{ route('student.transcript') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> View transcript</a>
@endsection
@section('student-content')
    <p class="academic-note portal-note-with-icon"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Only published semester results appear here. Drafts and pending reviews remain private until the university releases them.</span></p>
    @if($degreeProgress['published_semesters'])
        <section class="portal-degree-progress">
            <div class="portal-section-heading"><div><h2>Overall degree progress</h2><p>Your current cumulative standing across all published semesters</p></div><span class="portal-badge {{ $degreeProgress['backlog_courses'] ? 'is-warning' : 'is-success' }}"><i class="bi {{ $degreeProgress['backlog_courses'] ? 'bi-arrow-repeat' : 'bi-check-circle' }}" aria-hidden="true"></i>{{ $degreeProgress['backlog_courses'] ? $degreeProgress['backlog_courses'].' backlog course'.($degreeProgress['backlog_courses'] === 1 ? '' : 's') : 'All published courses cleared' }}</span></div>
            <div class="portal-degree-grid">
                <div class="portal-degree-card is-primary"><span><i class="bi bi-mortarboard" aria-hidden="true"></i> Current CGPA</span><strong>{{ number_format((float) $degreeProgress['current_cgpa'], 2) }}</strong><small>Credit-hour weighted cumulative GPA</small></div>
                <div class="portal-degree-card"><span><i class="bi bi-layers" aria-hidden="true"></i> Published semesters</span><strong>{{ $degreeProgress['published_semesters'] }}</strong><small>Official result sheets released</small></div>
                <div class="portal-degree-card"><span><i class="bi bi-check2-circle" aria-hidden="true"></i> Passed credits</span><strong>{{ number_format((float) $degreeProgress['passed_credits'], 1) }}</strong><small>Of {{ number_format((float) $degreeProgress['cgpa_credits'], 1) }} CGPA-counted credits</small></div>
                <div @class(['portal-degree-card', 'is-backlog' => $degreeProgress['backlog_courses']])><span><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Backlog</span><strong>{{ $degreeProgress['backlog_courses'] }}</strong><small>{{ number_format((float) $degreeProgress['backlog_credits'], 1) }} credit{{ (float) $degreeProgress['backlog_credits'] === 1.0 ? '' : 's' }} to repeat</small></div>
            </div>
            <p class="portal-degree-note"><i class="bi bi-info-circle" aria-hidden="true"></i> CGPA uses the latest published attempt for a repeated course. Older published sheets remain part of your academic history.</p>
        </section>
    @endif
    <div class="portal-section-heading"><div><h2>Published semesters</h2><p>Your official semester-by-semester academic record</p></div><span class="portal-badge is-info">{{ $results->total() }} results</span></div>
    <div class="table-responsive portal-table-card"><table class="table academic-table align-middle"><thead><tr><th>Semester</th><th>Academic year</th><th>Percentage</th><th>SGPA</th><th>CGPA</th><th>Outcome</th><th>Result sheet</th></tr></thead><tbody>@forelse($results as $result)@php($failedCourses = $result->items->where('status', 'Fail')->count())<tr><td><strong>{{ $result->enrollment->semester }}</strong></td><td>{{ $result->enrollment->academic_year }}</td><td><strong>{{ $result->semester_percentage }}%</strong></td><td>{{ $result->sgpa }}</td><td>{{ $result->cgpa }}</td><td><span class="portal-badge {{ $result->status === 'Pass' ? 'is-success' : 'is-danger' }}"><i class="bi {{ $result->status === 'Pass' ? 'bi-check-circle' : 'bi-exclamation-circle' }}" aria-hidden="true"></i>{{ $result->status }}</span><small class="portal-outcome-detail">{{ $result->status === 'Pass' ? 'All courses cleared' : $failedCourses.' backlog course'.($failedCourses === 1 ? '' : 's') }}</small></td><td><a class="portal-btn is-outline is-sm" href="{{ route('student.results.show', $result) }}"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> View published sheet</a></td></tr>@empty<tr class="portal-empty-row"><td colspan="7"><div class="portal-empty-state"><i class="bi bi-award" aria-hidden="true"></i><strong>No published semester results yet.</strong><span>Your result history will appear after official publication.</span></div></td></tr>@endforelse</tbody></table></div>
    <div class="portal-pagination">{{ $results->links('pagination::bootstrap-5') }}</div>
@endsection
