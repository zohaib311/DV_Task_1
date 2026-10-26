@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enrollment/enrollment.css') }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="table__content">
            <div class="card shadow-sm border-0">
                <div class="card-header student-list-header">
                    <div class="student-list-heading">
                        <div>
                            <h4 class="mb-1">Semester Enrollments</h4>
                            <small class="text-white-50">Academic history and assigned-course snapshots</small>
                        </div>
                        <span id="enrollment-search-count" class="student-search-count" aria-live="polite">{{ $enrollments->count() }} enrollments</span>
                    </div>
                    <div class="student-search-field student-header-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="enrollment-live-search" type="search" autocomplete="off"
                            placeholder="Search by reg. no., student, email, term or semester"
                            aria-label="Search enrollments by registration number, student, email, term or semester">
                        <button id="enrollment-search-clear" type="button" aria-label="Clear enrollment search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="add__user__btn">
                        @can('enrollments.create')
                        <a href="{{ route('addEnrollmentForm') }}">
                            <i class="bi bi-journal-plus"></i>
                            <span>New Enrollment</span>
                        </a>
                        @endcan
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success enrollment-alert mb-0" id="success-message" role="status">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reg. No.</th>
                                    <th>Student</th>
                                    <th>Academic Year / Term</th>
                                    <th>Semester</th>
                                    <th>Department / Section</th>
                                    <th>Courses</th>
                                    <th>Result</th>
                                    <th>Status</th>
                                    <th>Enrolled On</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollments-table-body">
                                @forelse ($enrollments as $enrollment)
                                    <tr class="enrollment-search-row"
                                        data-search="{{ strtolower(implode(' ', [
                                            $enrollment->student->registration_no ?? '',
                                            $enrollment->student->name ?? '',
                                            $enrollment->student->email ?? '',
                                            $enrollment->academic_year ?? '',
                                            $enrollment->term?->name ?? '',
                                            $enrollment->semester ?? '',
                                            $enrollment->department?->name ?? '',
                                            $enrollment->section?->name ?? '',
                                            $enrollment->status ?? '',
                                        ])) }}">
                                        <td><span class="enrollment-registration">{{ $enrollment->student->registration_no ?? 'N/A' }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $enrollment->student->name }}</div>
                                            <small class="text-muted">{{ $enrollment->student->email }}</small>
                                        </td>
                                        <td>{{ $enrollment->academic_year }}<small class="d-block text-muted">{{ $enrollment->term?->name ?? 'Historical enrollment' }}</small></td>
                                        <td><span class="enrollment-semester">{{ $enrollment->semester }}</span></td>
                                        <td>
                                            {{ $enrollment->department->name }}
                                            <small class="text-muted d-block">Section {{ $enrollment->section->name }}</small>
                                        </td>
                                        <td><details><summary>{{ $enrollment->courses_count }} courses</summary>
                                            @foreach($enrollment->courses as $course)
                                                <small class="d-block mt-2">{{ $course->course_name ?? $course->offering?->course_name ?? $course->course?->name ?? 'Course #'.$course->course_id }}<span class="d-block text-muted">{{ $course->offering?->teachers->pluck('name')->implode(', ') ?: 'No teaching assignment recorded' }}</span></small>
                                            @endforeach
                                        </details></td>
                                        <td>
                                            @if ($enrollment->semesterResult)
                                                <span class="enrollment-result-state {{ $enrollment->semesterResult->status === 'Pass' ? 'is-pass' : ($enrollment->semesterResult->status === 'Fail' ? 'is-fail' : 'is-draft') }}">
                                                    {{ $enrollment->semesterResult->published_at ? 'Published · ' : '' }}{{ $enrollment->semesterResult->status }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Not entered</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $enrollment->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $enrollment->enrolled_at->format('d M Y') }}</td>
                                        <td class="text-center">
                                            @if ($enrollment->status === 'active' && auth()->user()->can('enrollments.promote'))
                                                <a href="{{ route('promoteEnrollmentForm', $enrollment) }}" class="promotion-action">
                                                    <i class="bi bi-arrow-up-right-circle"></i> Promote
                                                </a>
                                            @else
                                                <span class="text-muted small">History preserved</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="enrollments-empty-state">
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x d-block fs-3 mb-2"></i>
                                            No semester enrollments created yet.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="enrollments-no-search-results" hidden>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-search d-block fs-4 mb-2"></i>
                                        No enrollment matches your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('enrollment-live-search');
            const clear = document.getElementById('enrollment-search-clear');
            const count = document.getElementById('enrollment-search-count');
            const rows = Array.from(document.querySelectorAll('.enrollment-search-row'));
            const emptyState = document.getElementById('enrollments-empty-state');
            const noResults = document.getElementById('enrollments-no-search-results');

            if (!search || !count || !noResults) return;

            const filterEnrollments = function () {
                const query = search.value.trim().toLowerCase();
                let matches = 0;

                rows.forEach(function (row) {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    row.hidden = !matchesSearch;
                    if (matchesSearch) matches++;
                });

                noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query);
                if (clear) clear.hidden = !query;
                count.textContent = query ? `${matches} of ${rows.length} enrollments` : `${rows.length} enrollments`;
            };

            search.addEventListener('input', filterEnrollments);
            clear?.addEventListener('click', function () {
                search.value = '';
                filterEnrollments();
                search.focus();
            });
        });

        setTimeout(function() {
            const message = document.getElementById('success-message');
            if (message) {
                message.style.transition = 'opacity .35s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 350);
            }
        }, 2800);
    </script>
@endsection
