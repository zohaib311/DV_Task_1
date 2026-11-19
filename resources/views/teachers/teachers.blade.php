@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}?v={{ filemtime(public_path('css/students.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-modal.css') }}?v={{ filemtime(public_path('css/teacher/teacher-modal.css')) }}">
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

                    <div class="add__user__btn d-flex align-items-center gap-2">
                        <a href="{{ route('teachers.export') }}" class="btn text-white d-inline-flex align-items-center gap-1.5 border-0 px-3 py-2 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.20);" title="Export all teachers to CSV spreadsheet">
                            <i class="bi bi-file-earmark-arrow-down-fill"></i>
                            <span>Export CSV</span>
                        </a>
                        <button type="button" class="btn text-white d-inline-flex align-items-center gap-1.5 border-0 px-3 py-2 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.20);" data-bs-toggle="modal" data-bs-target="#importTeachersModal" title="Import teachers from CSV file">
                            <i class="bi bi-file-earmark-arrow-up-fill"></i>
                            <span>Import CSV</span>
                        </button>
                        <a href="{{ route('addTeacherForm') }}" class="btn text-white d-inline-flex align-items-center gap-2 border-0 px-3 py-2 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.28);">
                            <i class="bi bi-person-plus-fill"></i>
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
                            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger m-3 mb-0" role="alert">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if (session('import_errors') && count(session('import_errors')) > 0)
                        <div class="alert alert-warning m-3 mb-0" role="alert">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Import Notes / Skipped Rows:</div>
                            <ul class="mb-0 small ps-3">
                                @foreach (array_slice(session('import_errors'), 0, 5) as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                                @if (count(session('import_errors')) > 5)
                                    <li><em>...and {{ count(session('import_errors')) - 5 }} more skipped rows.</em></li>
                                @endif
                            </ul>
                        </div>
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

                                                <button type="button" class="action__btn action__info"
                                                    title="View Teacher Profile" data-bs-toggle="modal"
                                                    data-bs-target="#infoTeacherModal{{ $teacher->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>

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

    {{-- Bulk Import Teachers Modal --}}
    <div class="modal fade" id="importTeachersModal" tabindex="-1" aria-labelledby="importTeachersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #4338ca, #6366f1);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                        <h5 class="modal-title mb-0 fw-bold" id="importTeachersModalLabel">Bulk Import Teachers from CSV</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('teachers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 bg-light">
                        <!-- Guidance Alert -->
                        <div class="p-3 mb-3 rounded-3 bg-white border d-flex gap-3 align-items-start shadow-sm">
                            <div class="p-2 rounded-3 bg-primary-subtle text-primary fs-4">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="small text-secondary">
                                <strong class="d-block text-dark mb-1">Safe &amp; Flexible Import:</strong>
                                <span>Required fields are <strong>Teacher Name</strong> and <strong>Email Address</strong>. Phone, Course/Specialization, and Department are optional and will safely use smart defaults if empty.</span>
                            </div>
                        </div>

                        <!-- Template Download Box -->
                        <div class="p-3 mb-4 rounded-3 bg-white border d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm">
                            <div>
                                <strong class="d-block text-dark small"><i class="bi bi-filetype-csv text-success me-1"></i> Pre-formatted Sample Template</strong>
                                <span class="text-muted small">Download our sample template with standard column headers for faculty members.</span>
                            </div>
                            <a href="{{ route('teachers.template') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-3">
                                <i class="bi bi-download"></i>
                                <span>Download Template (.csv)</span>
                            </a>
                        </div>

                        <!-- File Input -->
                        <div class="mb-3">
                            <label for="teacher_csv_file" class="form-label fw-bold text-dark small">
                                Select CSV File <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="csv_file" id="teacher_csv_file" class="form-control rounded-3" accept=".csv,text/csv,text/plain" required>
                            <div class="form-text small text-muted">Supports <code>.csv</code> files up to 5MB. Duplicate email rows will be safely reported.</div>
                        </div>
                    </div>

                    <div class="modal-footer px-4 py-3 bg-white border-top">
                        <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span>Upload &amp; Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($teachers as $teacher)
        {{-- Teacher Detailed Profile & Portfolio Modal --}}
        <div class="modal fade teacher-detail-modal" id="infoTeacherModal{{ $teacher->id }}" tabindex="-1"
            aria-labelledby="infoTeacherModalLabel{{ $teacher->id }}" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                <div class="modal-content">

                    <div class="modal-header teacher-modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-workspace fs-5"></i>
                            <h5 class="modal-title mb-0" id="infoTeacherModalLabel{{ $teacher->id }}">
                                Complete Faculty Profile &amp; Teaching Portfolio
                            </h5>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-primary fw-bold px-2 py-1">Teacher #{{ $teacher->id }}</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="modal-body teacher-modal-body">
                        @include('teachers.partials.teacher-detail-content', ['teacher' => $teacher, 'isFullPage' => false])
                    </div>

                </div>
            </div>
        </div>

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
