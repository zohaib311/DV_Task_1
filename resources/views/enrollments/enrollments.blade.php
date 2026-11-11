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

                <!-- Table Filter Toolbar -->
                <div class="table-filter-toolbar">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-building"></i></span>
                                <select id="enrollment-filter-department" class="form-select form-select-sm border-start-0" aria-label="Filter by department">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-calendar3"></i></span>
                                <select id="enrollment-filter-semester" class="form-select form-select-sm border-start-0" aria-label="Filter by semester">
                                    <option value="">All Semesters</option>
                                    @for ($s = 1; $s <= 8; $s++)
                                        <option value="Semester {{ $s }}">Semester {{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-activity"></i></span>
                                <select id="enrollment-filter-status" class="form-select form-select-sm border-start-0" aria-label="Filter by status">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="promoted">Promoted</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-award"></i></span>
                                <select id="enrollment-filter-result" class="form-select form-select-sm border-start-0" aria-label="Filter by result">
                                    <option value="">All Results</option>
                                    <option value="Pass">Passed</option>
                                    <option value="Fail">Failed / Backlog</option>
                                    <option value="Not entered">Not Entered</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <button id="enrollment-filter-reset" type="button" class="btn btn-filter-reset w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Filter Pills -->
                    <div class="filter-pills-wrap">
                        <span class="filter-pill-label">Quick filter:</span>
                        <button type="button" class="filter-pill-btn is-active enrollment-filter-pill" data-filter-type="all">
                            <span>All</span>
                            <span class="pill-count">{{ $enrollments->count() }}</span>
                        </button>
                        @foreach ($departments as $department)
                            @php($deptECount = $enrollments->where('department_id', $department->id)->count())
                            @if ($deptECount > 0)
                                <button type="button" class="filter-pill-btn enrollment-filter-pill" data-filter-type="department" data-filter-value="{{ $department->id }}">
                                    <i class="bi bi-building"></i>
                                    <span>{{ $department->name }}</span>
                                    <span class="pill-count">{{ $deptECount }}</span>
                                </button>
                            @endif
                        @endforeach
                        <button type="button" class="filter-pill-btn enrollment-filter-pill" data-filter-type="status" data-filter-value="active">
                            <i class="bi bi-play-circle text-primary"></i>
                            <span>Active</span>
                            <span class="pill-count">{{ $enrollments->where('status', 'active')->count() }}</span>
                        </button>
                        <button type="button" class="filter-pill-btn enrollment-filter-pill" data-filter-type="status" data-filter-value="promoted">
                            <i class="bi bi-check2-all text-success"></i>
                            <span>Promoted</span>
                            <span class="pill-count">{{ $enrollments->where('status', 'promoted')->count() }}</span>
                        </button>
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
                                    <th style="width: 50px;">#</th>
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
                                            $enrollment->semesterResult?->status ?? ''
                                        ])) }}"
                                        data-department-id="{{ $enrollment->department_id }}"
                                        data-semester="{{ $enrollment->semester }}"
                                        data-status="{{ $enrollment->status }}"
                                        data-result-status="{{ $enrollment->semesterResult?->status ?? 'Not entered' }}">
                                        <th scope="row" class="row-serial">{{ $loop->iteration }}</th>
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
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x d-block fs-3 mb-2"></i>
                                            No semester enrollments created yet.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="enrollments-no-search-results" hidden>
                                    <td colspan="11" class="text-center py-5 text-muted">
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
            const deptFilter = document.getElementById('enrollment-filter-department');
            const semFilter = document.getElementById('enrollment-filter-semester');
            const statusFilter = document.getElementById('enrollment-filter-status');
            const resultFilter = document.getElementById('enrollment-filter-result');
            const resetBtn = document.getElementById('enrollment-filter-reset');
            const pillBtns = Array.from(document.querySelectorAll('.enrollment-filter-pill'));
            const rows = Array.from(document.querySelectorAll('.enrollment-search-row'));
            const emptyState = document.getElementById('enrollments-empty-state');
            const noResults = document.getElementById('enrollments-no-search-results');

            if (!rows.length) return;

            const filterEnrollments = function () {
                const query = search ? search.value.trim().toLowerCase() : '';
                const selectedDept = deptFilter ? deptFilter.value : '';
                const selectedSem = semFilter ? semFilter.value : '';
                const selectedStatus = statusFilter ? statusFilter.value : '';
                const selectedResult = resultFilter ? resultFilter.value : '';
                let matches = 0;

                rows.forEach(function (row) {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    const matchesDept = !selectedDept || row.dataset.departmentId === selectedDept;
                    const matchesSem = !selectedSem || row.dataset.semester === selectedSem;
                    const matchesStatus = !selectedStatus || row.dataset.status === selectedStatus;
                    const matchesResult = !selectedResult || row.dataset.resultStatus === selectedResult;

                    const isVisible = matchesSearch && matchesDept && matchesSem && matchesStatus && matchesResult;
                    row.hidden = !isVisible;
                    if (isVisible) {
                        matches++;
                        const serialCell = row.querySelector('.row-serial');
                        if (serialCell) serialCell.textContent = matches;
                    }
                });

                if (noResults) noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query || selectedDept || selectedSem || selectedStatus || selectedResult);
                if (clear) clear.hidden = !query;

                const isFiltered = Boolean(query || selectedDept || selectedSem || selectedStatus || selectedResult);
                if (count) {
                    count.textContent = isFiltered ? `${matches} of ${rows.length} enrollments` : `${rows.length} enrollments`;
                }

                // Update active state on pills
                pillBtns.forEach(pill => {
                    const type = pill.dataset.filterType;
                    const val = pill.dataset.filterValue;
                    if (type === 'all') {
                        pill.classList.toggle('is-active', !selectedDept && !selectedSem && !selectedStatus && !selectedResult);
                    } else if (type === 'department') {
                        pill.classList.toggle('is-active', selectedDept === val);
                    } else if (type === 'status') {
                        pill.classList.toggle('is-active', selectedStatus === val);
                    }
                });
            };

            search?.addEventListener('input', filterEnrollments);
            clear?.addEventListener('click', function () {
                search.value = '';
                filterEnrollments();
                search.focus();
            });

            deptFilter?.addEventListener('change', filterEnrollments);
            semFilter?.addEventListener('change', filterEnrollments);
            statusFilter?.addEventListener('change', filterEnrollments);
            resultFilter?.addEventListener('change', filterEnrollments);

            pillBtns.forEach(pill => {
                pill.addEventListener('click', function () {
                    const type = this.dataset.filterType;
                    const val = this.dataset.filterValue;
                    if (type === 'all') {
                        if (deptFilter) deptFilter.value = '';
                        if (semFilter) semFilter.value = '';
                        if (statusFilter) statusFilter.value = '';
                        if (resultFilter) resultFilter.value = '';
                    } else if (type === 'department') {
                        if (deptFilter) deptFilter.value = (deptFilter.value === val ? '' : val);
                    } else if (type === 'status') {
                        if (statusFilter) statusFilter.value = (statusFilter.value === val ? '' : val);
                    }
                    filterEnrollments();
                });
            });

            resetBtn?.addEventListener('click', function () {
                if (search) search.value = '';
                if (deptFilter) deptFilter.value = '';
                if (semFilter) semFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                if (resultFilter) resultFilter.value = '';
                filterEnrollments();
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
