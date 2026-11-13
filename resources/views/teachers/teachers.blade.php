@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}?v={{ filemtime(public_path('css/students.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white student-list-header">

                    <div class="student-list-heading">
                        <h4 class="mb-0">Teachers List</h4>
                        <span id="teacher-search-count" class="student-search-count" aria-live="polite">{{ $teachers->count() }} teachers</span>
                    </div>

                    <div class="student-search-field student-header-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="teacher-live-search" type="search" autocomplete="off"
                            placeholder="Search by name, email, phone or specialization"
                            aria-label="Search teachers by name, email, phone or specialization">
                        <button id="teacher-search-clear" type="button" aria-label="Clear teacher search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="add__user__btn">
                        <a href="{{ route('addTeacherForm') }}" class="nav-link">
                            <span>Add Teacher</span>
                        </a>
                    </div>

                </div>

                <!-- Teacher Filter Toolbar -->
                <div class="table-filter-toolbar">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-building"></i></span>
                                <select id="teacher-filter-department" class="form-select form-select-sm border-start-0" aria-label="Filter by department">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-person-badge"></i></span>
                                <select id="teacher-filter-account" class="form-select form-select-sm border-start-0" aria-label="Filter by portal account">
                                    <option value="">All Login Accounts</option>
                                    <option value="linked">Linked (Portal Access)</option>
                                    <option value="unlinked">Not Linked</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <button id="teacher-filter-reset" type="button" class="btn btn-filter-reset w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Filter Pills -->
                    <div class="filter-pills-wrap">
                        <span class="filter-pill-label">Quick filter:</span>
                        <button type="button" class="filter-pill-btn is-active teacher-filter-pill" data-filter-type="all">
                            <span>All</span>
                            <span class="pill-count">{{ $teachers->count() }}</span>
                        </button>
                        @foreach ($departments as $department)
                            @php($deptTCount = $teachers->filter(fn($t) => $t->offerings->pluck('department_id')->contains($department->id))->count())
                            @if ($deptTCount > 0)
                                <button type="button" class="filter-pill-btn teacher-filter-pill" data-filter-type="department" data-filter-value="{{ $department->id }}">
                                    <i class="bi bi-building"></i>
                                    <span>{{ $department->name }}</span>
                                    <span class="pill-count">{{ $deptTCount }}</span>
                                </button>
                            @endif
                        @endforeach
                        <button type="button" class="filter-pill-btn teacher-filter-pill" data-filter-type="account" data-filter-value="linked">
                            <i class="bi bi-check-circle text-success"></i>
                            <span>Portal Linked</span>
                            <span class="pill-count">{{ $teachers->whereNotNull('user_id')->count() }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3 mb-0" id="success-message">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger m-3 mb-0" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Login Account</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="teachers-table-body">

                                @forelse ($teachers as $teacher)
                                    <tr class="teacher-search-row"
                                        data-search="{{ strtolower(implode(' ', [
                                            $teacher->name,
                                            $teacher->email,
                                            $teacher->phone,
                                            $teacher->course ?? '',
                                            $teacher->offerings->pluck('department.name')->implode(' '),
                                            $teacher->user_id ? 'linked' : 'unlinked'
                                        ])) }}"
                                        data-account="{{ $teacher->user_id ? 'linked' : 'unlinked' }}"
                                        data-departments="{{ $teacher->offerings->pluck('department_id')->implode(',') }}">

                                        <th scope="row" class="row-serial">
                                            {{ $loop->iteration }}
                                        </th>

                                        <td class="user__image">
                                            <div class="user-avatar-wrap">
                                                <img src="{{ asset('storage/images/' . ($teacher->image ?: 'default-user.png')) }}"
                                                    alt="{{ $teacher->name }}" class="user-image"
                                                    onerror="this.onerror=null;this.src='{{ asset('storage/images/default-user.png') }}';">
                                            </div>
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $teacher->name }}
                                        </td>

                                        <td>
                                            {{ $teacher->email }}
                                        </td>

                                        <td>
                                            {{ $teacher->phone }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $teacher->course ?? 'General Faculty' }}
                                            </span>
                                            @php($deptNames = $teacher->offerings->pluck('department.name')->unique()->filter())
                                            @if ($deptNames->isNotEmpty())
                                                <small class="text-muted d-block mt-1">
                                                    <i class="bi bi-building me-1"></i> {{ $deptNames->implode(', ') }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($teacher->user)
                                                <span class="badge bg-success">Linked</span>
                                            @else
                                                <span class="text-muted small">Not linked</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="action__buttons">

                                                <a href="{{ route('editTeacherForm', $teacher->id) }}"
                                                    class="action__btn action__edit" title="Edit Teacher">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Teacher" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $teacher->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>
                                        </td>

                                    </tr>

                                @empty

                                    <tr id="teachers-empty-state">
                                        <td colspan="8" class="text-center py-4">
                                            No teachers found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr id="teachers-no-search-results" hidden>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No teacher matches your search.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @foreach ($teachers as $teacher)
        <div class="modal fade" id="deleteModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete__modal">

                    <div class="modal-body text-center">

                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>
                            Delete Teacher?
                        </h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $teacher->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">

                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteTeacher', $teacher->id) }}" method="POST">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="delete__confirm">
                                    <i class="bi bi-trash3 me-1"></i>
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('teacher-live-search');
            const clear = document.getElementById('teacher-search-clear');
            const count = document.getElementById('teacher-search-count');
            const deptFilter = document.getElementById('teacher-filter-department');
            const accountFilter = document.getElementById('teacher-filter-account');
            const resetBtn = document.getElementById('teacher-filter-reset');
            const pillBtns = Array.from(document.querySelectorAll('.teacher-filter-pill'));
            const rows = Array.from(document.querySelectorAll('.teacher-search-row'));
            const emptyState = document.getElementById('teachers-empty-state');
            const noResults = document.getElementById('teachers-no-search-results');

            if (!rows.length) return;

            const filterTeachers = function () {
                const query = search ? search.value.trim().toLowerCase() : '';
                const selectedDept = deptFilter ? deptFilter.value : '';
                const selectedAccount = accountFilter ? accountFilter.value : '';
                let matches = 0;

                rows.forEach(function (row) {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    const depts = (row.dataset.departments || '').split(',').map(s => s.trim());
                    const matchesDept = !selectedDept || depts.includes(selectedDept);
                    const matchesAccount = !selectedAccount || row.dataset.account === selectedAccount;

                    const isVisible = matchesSearch && matchesDept && matchesAccount;
                    row.hidden = !isVisible;
                    if (isVisible) {
                        matches++;
                        const serialCell = row.querySelector('.row-serial');
                        if (serialCell) serialCell.textContent = matches;
                    }
                });

                if (noResults) noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query || selectedDept || selectedAccount);
                if (clear) clear.hidden = !query;

                const isFiltered = Boolean(query || selectedDept || selectedAccount);
                if (count) {
                    count.textContent = isFiltered ? `${matches} of ${rows.length} teachers` : `${rows.length} teachers`;
                }

                // Update active state on pills
                pillBtns.forEach(pill => {
                    const type = pill.dataset.filterType;
                    const val = pill.dataset.filterValue;
                    if (type === 'all') {
                        pill.classList.toggle('is-active', !selectedDept && !selectedAccount);
                    } else if (type === 'department') {
                        pill.classList.toggle('is-active', selectedDept === val);
                    } else if (type === 'account') {
                        pill.classList.toggle('is-active', selectedAccount === val);
                    }
                });
            };

            search?.addEventListener('input', filterTeachers);
            clear?.addEventListener('click', function () {
                search.value = '';
                filterTeachers();
                search.focus();
            });

            deptFilter?.addEventListener('change', filterTeachers);
            accountFilter?.addEventListener('change', filterTeachers);

            pillBtns.forEach(pill => {
                pill.addEventListener('click', function () {
                    const type = this.dataset.filterType;
                    const val = this.dataset.filterValue;
                    if (type === 'all') {
                        if (deptFilter) deptFilter.value = '';
                        if (accountFilter) accountFilter.value = '';
                    } else if (type === 'department') {
                        if (deptFilter) deptFilter.value = (deptFilter.value === val ? '' : val);
                    } else if (type === 'account') {
                        if (accountFilter) accountFilter.value = (accountFilter.value === val ? '' : val);
                    }
                    filterTeachers();
                });
            });

            resetBtn?.addEventListener('click', function () {
                if (search) search.value = '';
                if (deptFilter) deptFilter.value = '';
                if (accountFilter) accountFilter.value = '';
                filterTeachers();
            });

            setTimeout(function () {
                const message = document.getElementById('success-message');
                if (!message) return;

                message.style.transition = 'opacity .35s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 350);
            }, 2800);
        });
    </script>
@endsection
