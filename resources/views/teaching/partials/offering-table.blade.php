<div class="table-responsive">
    <table class="table academic-table align-middle mb-0">
        <thead><tr><th scope="col">Course</th><th scope="col">Class</th><th scope="col">Teaching term</th><th scope="col">Students</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
            @forelse($offerings as $offering)
                <tr>
                    <td><strong>{{ $offering->course_name }}</strong><small>{{ $offering->course_code }} · {{ $offering->credit_hours }} credits</small></td>
                    <td>{{ $offering->program?->code ?? 'Legacy program' }}<small>{{ $offering->department->name }} · Section {{ $offering->section->name }} · {{ $offering->semester }}</small></td>
                    <td>{{ $offering->term->name }}<small>{{ $offering->term->academicYear->name }}</small></td>
                    <td>{{ $offering->enrollment_courses_count }}</td>
                    <td>@include('teaching.partials.status', ['status' => $offering->status])</td>
                    <td class="text-end"><a class="teaching-roster-link" href="{{ route('teaching.offerings.show', $offering) }}" aria-label="View roster for {{ $offering->course_code }}, section {{ $offering->section->name }}">View roster <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="academic-empty"><i class="bi bi-journal-bookmark d-block fs-3 mb-2" aria-hidden="true"></i><strong>No assigned courses to display</strong><small>Try different filters, or ask your administrator to assign a course offering to your teacher profile.</small></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
