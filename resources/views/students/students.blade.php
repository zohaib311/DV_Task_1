@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/student-model.css') }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="table__content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white student-list-header">
                    <div class="student-list-heading">
                        <h4 class="mb-0">Students List</h4>
                        <span id="student-search-count" class="student-search-count" aria-live="polite">{{ $students->count() }} students</span>
                    </div>
                    <div class="student-search-field student-header-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="student-live-search" type="search" autocomplete="off"
                            placeholder="Search by registration no., name, email or phone"
                            aria-label="Search students by registration number, name, email or phone">
                        <button id="student-search-clear" type="button" aria-label="Clear student search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="add__user__btn">
                        <a href="{{ route('addStudentForm') }}" class="btn text-white d-inline-flex align-items-center gap-2 border-0 px-3 py-2 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.25);">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>Add Student</span>
                        </a>
                    </div>
                </div>

                <!-- Table Filter Toolbar -->
                <div class="table-filter-toolbar">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-building"></i></span>
                                <select id="student-filter-department" class="form-select form-select-sm border-start-0" aria-label="Filter by department">
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
                                <select id="student-filter-semester" class="form-select form-select-sm border-start-0" aria-label="Filter by semester">
                                    <option value="">All Semesters</option>
                                    @for ($s = 1; $s <= 8; $s++)
                                        <option value="Semester {{ $s }}">Semester {{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-mortarboard"></i></span>
                                <select id="student-filter-admission" class="form-select form-select-sm border-start-0" aria-label="Filter by admission mode">
                                    <option value="">All Admission Types</option>
                                    <option value="regular">Regular Entry (Sem 1)</option>
                                    <option value="lateral_adp">ADP Lateral Entry (Sem 5)</option>
                                    <option value="migration">Transfer / Migration</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <button id="student-filter-reset" type="button" class="btn btn-filter-reset w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Filter Pills -->
                    <div class="filter-pills-wrap">
                        <span class="filter-pill-label">Quick filter:</span>
                        <button type="button" class="filter-pill-btn is-active student-filter-pill" data-filter-type="all">
                            <span>All</span>
                            <span class="pill-count">{{ $students->count() }}</span>
                        </button>
                        @foreach ($departments as $department)
                            @php($deptCount = $students->where('department_id', $department->id)->count())
                            @if ($deptCount > 0)
                                <button type="button" class="filter-pill-btn student-filter-pill" data-filter-type="department" data-filter-value="{{ $department->id }}">
                                    <i class="bi bi-building"></i>
                                    <span>{{ $department->name }}</span>
                                    <span class="pill-count">{{ $deptCount }}</span>
                                </button>
                            @endif
                        @endforeach
                        @php($lateralCount = $students->where('admission_type', 'lateral_adp')->count())
                        @if ($lateralCount > 0)
                            <button type="button" class="filter-pill-btn student-filter-pill" data-filter-type="admission" data-filter-value="lateral_adp">
                                <i class="bi bi-award text-warning"></i>
                                <span>ADP Lateral</span>
                                <span class="pill-count">{{ $lateralCount }}</span>
                            </button>
                        @endif
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
                                    <th scope="col" style="width: 45px;">#</th>
                                    <th scope="col">Reg No</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Department & Program</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Enrollment</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="students-table-body">
                                @forelse ($students as $student)
                                    <tr class="student-search-row"
                                        data-search="{{ strtolower(implode(' ', [
                                            $student->registration_no ?? '',
                                            $student->name,
                                            $student->email,
                                            $student->phone,
                                            $student->department?->name ?? '',
                                            $student->program?->name ?? '',
                                            $student->program?->code ?? '',
                                            $student->section?->name ?? '',
                                            $student->activeSemesterEnrollment?->semester ?? $student->semester ?? '',
                                            $student->admission_type ?? 'regular'
                                        ])) }}"
                                        data-department-id="{{ $student->department_id }}"
                                        data-semester="{{ $student->activeSemesterEnrollment?->semester ?? $student->semester ?? '' }}"
                                        data-admission-type="{{ $student->admission_type ?? 'regular' }}">
                                        <th scope="row" class="row-serial">{{ $loop->iteration }}</th>
                                        <td>
                                            <span class="badge bg-secondary text-wrap reg-no-badge">
                                                {{ $student->registration_no ?? 'N/A' }}
                                            </span>
                                            @if($student->admission_type === 'lateral_adp')
                                                <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-award me-1"></i> ADP Lateral
                                                </span>
                                            @elseif($student->admission_type === 'migration')
                                                <span class="badge bg-info text-dark d-block mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Transfer
                                                </span>
                                            @endif
                                        </td>
                                        <td class="user__image">
                                            <img src="{{ asset('storage/images/' . $student->image) }}"
                                                alt="{{ $student->name }}" class="user-image">
                                        </td>
                                        <td class="fw-semibold">{{ $student->name }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $student->department->name ?? 'N/A' }}</div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-mortarboard me-1"></i> Program: {{ $student->program->name ?? ($student->program->code ?? 'N/A') }}
                                                @if($student->section)
                                                    · Sec {{ $student->section->name }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>{{ $student->email }}</td>
                                        <td>{{ $student->phone }}</td>
                                        <td>
                                            @if ($student->activeSemesterEnrollment)
                                                <span class="badge bg-info text-dark">
                                                    {{ $student->activeSemesterEnrollment->semester }}
                                                </span>
                                                <small class="d-block text-muted mt-1">
                                                    {{ $student->activeSemesterEnrollment->academic_year }}
                                                </small>
                                            @else
                                                <span class="text-muted small">Not enrolled</span>
                                            @endif
                                        </td>


                                        <td>
                                            <div class="action__buttons">
                                                <button type="button" class="action__btn action__info" title="View Student"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#infoStudentModal{{ $student->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="{{ route('editStudentForm', $student->id) }}"
                                                    class="action__btn action__edit" title="Edit Student">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Student" data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal{{ $student->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="students-empty-state">
                                        <td colspan="9" class="text-center py-4">No students found.</td>
                                    </tr>
                                @endforelse
                                <tr id="students-no-search-results" hidden>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No student matches your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($students as $student)
        <div class="modal fade" id="deleteStudentModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content delete__modal">
                    <div class="modal-body text-center">
                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <h4>Delete Student?</h4>
                        <p>
                            Are you sure you want to delete
                            <strong>{{ $student->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>
                        <div class="delete__modal__actions">
                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('deleteStudent', $student->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete__confirm">
                                    <i class="bi bi-trash3 me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($students as $student)
        <div class="modal fade student-detail-modal" id="infoStudentModal{{ $student->id }}" tabindex="-1"
            aria-labelledby="infoStudentModalLabel{{ $student->id }}" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">

                    <div class="modal-header student-modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-vcard-fill"></i>
                            <h5 class="modal-title mb-0" id="infoStudentModalLabel{{ $student->id }}">
                                Student Details
                            </h5>
                        </div>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>

                    <div class="modal-body student-modal-body">

                        <div class="student-modal-layout">

                            <div class="student-profile-card">

                                <div class="student-profile-image">
                                    <img src="{{ asset('storage/images/' . ($student->image ?: 'default-user.png')) }}"
                                        alt="{{ $student->name }}" class="student-modal-avatar">
                                </div>

                                <div class="student-profile-info">
                                    <h3>{{ $student->name }}</h3>

                                    <div class="student-profile-item">
                                        <i class="bi bi-envelope"></i>
                                        <span>{{ $student->email }}</span>
                                    </div>

                                    <div class="student-profile-item">
                                        <i class="bi bi-telephone"></i>
                                        <span>{{ $student->phone }}</span>
                                    </div>
                                </div>

                            </div>

                            <div class="student-details-box">

                                <div class="student-details-heading">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Student Information</span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-hash"></i>
                                        Student ID
                                    </span>
                                    <span class="student-detail-value">
                                        #{{ $student->id }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-card-heading"></i>
                                        Registration No
                                    </span>
                                    <span class="student-detail-value fw-bold text-primary">
                                        {{ $student->registration_no ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-person"></i>
                                        Full Name
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->name }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-envelope"></i>
                                        Email Address
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->email }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-telephone"></i>
                                        Phone Number
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->phone }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-building"></i>
                                        Department
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->department->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-diagram-3"></i>
                                        Section
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->section->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar3"></i>
                                        Semester
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->activeSemesterEnrollment?->semester ?? 'Not enrolled' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar-range"></i>
                                        Academic Year
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->activeSemesterEnrollment?->academic_year ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar-check"></i>
                                        Enrolled On
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->activeSemesterEnrollment?->enrolled_at?->format('d M Y') ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row student-courses-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-book"></i>
                                        Assigned Courses
                                    </span>

                                    <span class="student-detail-value student-course-list">
                                        @forelse ($student->activeSemesterEnrollment?->courses ?? [] as $enrollmentCourse)
                                            <span class="student-course-badge">
                                                {{ $enrollmentCourse->course->name ?? 'Course removed' }}
                                                <small>({{ $enrollmentCourse->credit_hours }} Cr. Hrs)</small>
                                            </span>
                                        @empty
                                            <span class="text-muted">No enrollment courses assigned</span>
                                        @endforelse
                                    </span>
                                </div>

                                {{-- <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar-plus"></i>
                                        Registered At
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->created_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-clock-history"></i>
                                        Last Updated
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                    </span>
                                </div> --}}

                            </div>

                        </div>

                    </div>



                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script src="{{ asset('js/student/alert-dismiss.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('student-live-search');
            const clear = document.getElementById('student-search-clear');
            const count = document.getElementById('student-search-count');
            const deptFilter = document.getElementById('student-filter-department');
            const semFilter = document.getElementById('student-filter-semester');
            const admFilter = document.getElementById('student-filter-admission');
            const resetBtn = document.getElementById('student-filter-reset');
            const pillBtns = Array.from(document.querySelectorAll('.student-filter-pill'));
            const rows = Array.from(document.querySelectorAll('.student-search-row'));
            const emptyState = document.getElementById('students-empty-state');
            const noResults = document.getElementById('students-no-search-results');

            if (!rows.length) return;

            const filterStudents = function () {
                const query = search ? search.value.trim().toLowerCase() : '';
                const selectedDept = deptFilter ? deptFilter.value : '';
                const selectedSem = semFilter ? semFilter.value : '';
                const selectedAdm = admFilter ? admFilter.value : '';
                let matches = 0;

                rows.forEach(function (row) {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    const matchesDept = !selectedDept || row.dataset.departmentId === selectedDept;
                    const matchesSem = !selectedSem || row.dataset.semester === selectedSem;
                    const matchesAdm = !selectedAdm || row.dataset.admissionType === selectedAdm;

                    const isVisible = matchesSearch && matchesDept && matchesSem && matchesAdm;
                    row.hidden = !isVisible;
                    if (isVisible) {
                        matches++;
                        const serialCell = row.querySelector('.row-serial');
                        if (serialCell) serialCell.textContent = matches;
                    }
                });

                if (noResults) noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query || selectedDept || selectedSem || selectedAdm);
                if (clear) clear.hidden = !query;

                const isFiltered = Boolean(query || selectedDept || selectedSem || selectedAdm);
                if (count) {
                    count.textContent = isFiltered ? `${matches} of ${rows.length} students` : `${rows.length} students`;
                }

                // Update active state on pills
                pillBtns.forEach(pill => {
                    const type = pill.dataset.filterType;
                    const val = pill.dataset.filterValue;
                    if (type === 'all') {
                        pill.classList.toggle('is-active', !selectedDept && !selectedSem && !selectedAdm);
                    } else if (type === 'department') {
                        pill.classList.toggle('is-active', selectedDept === val);
                    } else if (type === 'admission') {
                        pill.classList.toggle('is-active', selectedAdm === val);
                    }
                });
            };

            search?.addEventListener('input', filterStudents);
            clear?.addEventListener('click', function () {
                search.value = '';
                filterStudents();
                search.focus();
            });

            deptFilter?.addEventListener('change', filterStudents);
            semFilter?.addEventListener('change', filterStudents);
            admFilter?.addEventListener('change', filterStudents);

            pillBtns.forEach(pill => {
                pill.addEventListener('click', function () {
                    const type = this.dataset.filterType;
                    const val = this.dataset.filterValue;
                    if (type === 'all') {
                        if (deptFilter) deptFilter.value = '';
                        if (semFilter) semFilter.value = '';
                        if (admFilter) admFilter.value = '';
                    } else if (type === 'department') {
                        if (deptFilter) deptFilter.value = (deptFilter.value === val ? '' : val);
                    } else if (type === 'admission') {
                        if (admFilter) admFilter.value = (admFilter.value === val ? '' : val);
                    }
                    filterStudents();
                });
            });

            resetBtn?.addEventListener('click', function () {
                if (search) search.value = '';
                if (deptFilter) deptFilter.value = '';
                if (semFilter) semFilter.value = '';
                if (admFilter) admFilter.value = '';
                filterStudents();
            });
        });
    </script>
@endsection
