@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}?v={{ filemtime(public_path('css/students.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/student-model.css') }}?v={{ filemtime(public_path('css/student/student-model.css')) }}">
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
                        @php($scholarshipCount = $students->filter(fn($s) => $s->hasActiveScholarship())->count())
                        @if ($scholarshipCount > 0)
                            <button type="button" class="filter-pill-btn student-filter-pill" data-filter-type="scholarship" data-filter-value="active">
                                <i class="bi bi-gift-fill text-success"></i>
                                <span>Scholarship Holders</span>
                                <span class="pill-count">{{ $scholarshipCount }}</span>
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
                                            $student->cnic ?? '',
                                            $student->department?->name ?? '',
                                            $student->program?->name ?? '',
                                            $student->program?->code ?? '',
                                            $student->section?->name ?? '',
                                            $student->activeSemesterEnrollment?->semester ?? $student->semester ?? '',
                                            $student->admission_type ?? 'regular',
                                            $student->hasActiveScholarship() ? 'scholarship ' . $student->scholarship_name : '',
                                        ])) }}"
                                        data-department-id="{{ $student->department_id }}"
                                        data-semester="{{ $student->activeSemesterEnrollment?->semester ?? $student->semester ?? '' }}"
                                        data-admission-type="{{ $student->admission_type ?? 'regular' }}"
                                        data-scholarship="{{ $student->hasActiveScholarship() ? 'active' : 'none' }}">
                                        <th scope="row" class="row-serial">{{ $loop->iteration }}</th>
                                        <td>
                                            <span class="badge reg-no-badge">
                                                {{ $student->registration_no ?? 'N/A' }}
                                            </span>
                                            @if($student->admission_type === 'lateral_adp')
                                                <span class="badge badge-adp d-block mt-1">
                                                    <i class="bi bi-award me-1"></i> ADP Lateral
                                                </span>
                                            @elseif($student->admission_type === 'migration')
                                                <span class="badge badge-transfer d-block mt-1">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Transfer
                                                </span>
                                            @endif
                                            @if($student->hasActiveScholarship())
                                                <span class="badge badge-scholarship d-inline-flex align-items-center mt-1" style="background-color: #ecfdf5 !important; color: #047857 !important; border: 1px solid #6ee7b7 !important; font-weight: 700 !important; font-size: 0.72rem !important; padding: 4px 8px !important; border-radius: 999px !important; box-shadow: 0 1px 2px rgba(16, 185, 129, 0.12) !important;" title="{{ $student->scholarship_name }}">
                                                    <i class="bi bi-gift-fill me-1" style="color: #047857 !important;"></i> {{ number_format((float) $student->scholarship_percentage, 0) }}% Scholarship
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
                                        <td>
                                            <div>{{ $student->phone }}</div>
                                            @if($student->cnic)
                                                <small class="text-muted d-block" style="font-size: 0.72rem;"><i class="bi bi-card-heading me-1"></i>{{ $student->cnic }}</small>
                                            @endif
                                        </td>
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

            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                <div class="modal-content">

                    <div class="modal-header student-modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-vcard-fill fs-5"></i>
                            <h5 class="modal-title mb-0" id="infoStudentModalLabel{{ $student->id }}">
                                Complete Student Profile & Academic Record
                            </h5>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-primary fw-bold px-2 py-1">{{ $student->registration_no ?? 'REG-N/A' }}</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="modal-body student-modal-body">
                        <div class="student-modal-layout">

                            {{-- Left Profile Card --}}
                            <div class="student-profile-card">
                                <div class="student-profile-image">
                                    <img src="{{ asset('storage/images/' . ($student->image ?: 'default-user.png')) }}"
                                        alt="{{ $student->name }}" class="student-modal-avatar">
                                </div>

                                <div class="student-profile-info">
                                    <h3>{{ $student->name }}</h3>
                                    <div class="badge px-3 py-1.5 rounded-pill mb-2" style="background: linear-gradient(135deg, #6c63ff, #8b5cf6); color: #fff; font-size: 0.82rem;">
                                        {{ $student->registration_no ?? 'REG-N/A' }}
                                    </div>

                                    <div class="student-profile-pills">
                                        @if($student->admission_type === 'lateral_adp')
                                            <span class="badge" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a;">
                                                <i class="bi bi-award me-1"></i> ADP Lateral Entry
                                            </span>
                                        @elseif($student->admission_type === 'migration')
                                            <span class="badge" style="background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4;">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Transfer / Migration
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border">
                                                <i class="bi bi-check-circle me-1 text-success"></i> Regular Intake
                                            </span>
                                        @endif

                                        @if($student->hasActiveScholarship())
                                            <span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #6ee7b7; font-weight:700;">
                                                <i class="bi bi-gift-fill me-1 text-success"></i> {{ number_format((float) $student->scholarship_percentage, 0) }}% Tuition Waiver
                                            </span>
                                            <small class="text-success fw-semibold" style="font-size:0.75rem;">{{ $student->scholarship_name }}</small>
                                        @endif

                                        @if($student->user)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-shield-check me-1"></i> Portal Account Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle">
                                                <i class="bi bi-shield-slash me-1"></i> No Portal Login
                                            </span>
                                        @endif
                                    </div>

                                    <div class="student-profile-contact-list">
                                        <div class="student-profile-item">
                                            <i class="bi bi-envelope"></i>
                                            <span>{{ $student->email }}</span>
                                        </div>
                                        <div class="student-profile-item">
                                            <i class="bi bi-telephone"></i>
                                            <span>{{ $student->phone }}</span>
                                        </div>
                                        @if($student->cnic)
                                            <div class="student-profile-item">
                                                <i class="bi bi-card-heading"></i>
                                                <span class="fw-semibold text-dark">{{ $student->cnic }}</span>
                                            </div>
                                        @endif
                                        <div class="student-profile-item">
                                            <i class="bi bi-building"></i>
                                            <span>{{ $student->department->name ?? 'N/A' }}</span>
                                        </div>
                                        @if($student->program)
                                            <div class="student-profile-item">
                                                <i class="bi bi-mortarboard"></i>
                                                <span>{{ $student->program->name }} ({{ $student->program->code }})</span>
                                            </div>
                                        @endif
                                        @if($student->section)
                                            <div class="student-profile-item">
                                                <i class="bi bi-diagram-3"></i>
                                                <span>Section {{ $student->section->name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-3 w-100 pt-2 border-top">
                                        <a href="{{ route('editStudentForm', $student->id) }}" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1 rounded-3">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit Profile</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Details Stack --}}
                            <div class="student-details-stack">

                                {{-- 1. Student Personal & Identification Details --}}
                                <div class="student-details-box">
                                    <div class="student-details-heading heading-personal">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-badge-fill"></i>
                                            <span>Identity & Personal Details</span>
                                        </div>
                                        <span class="badge bg-primary text-white">Student #{{ $student->id }}</span>
                                    </div>

                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-hash"></i> Student ID</span>
                                        <span class="student-detail-value fw-bold text-dark">#{{ $student->id }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-card-heading"></i> Registration No</span>
                                        <span class="student-detail-value fw-bold text-primary">{{ $student->registration_no ?? 'N/A' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-person"></i> Full Name</span>
                                        <span class="student-detail-value fw-semibold text-dark">{{ $student->name }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-person-vcard"></i> CNIC / Form-B</span>
                                        <span class="student-detail-value">
                                            @if($student->cnic)
                                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6">{{ $student->cnic }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Not Provided</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-envelope"></i> Email Address</span>
                                        <span class="student-detail-value">{{ $student->email }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-telephone"></i> Phone Number</span>
                                        <span class="student-detail-value">{{ $student->phone }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-shield-lock"></i> Student Portal User</span>
                                        <span class="student-detail-value">
                                            @if($student->user)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Active ({{ $student->user->email }})
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle">
                                                    <i class="bi bi-x-circle me-1"></i> No Linked Portal Account
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-calendar-plus"></i> Admission Date</span>
                                        <span class="student-detail-value">{{ $student->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                {{-- 2. Academic Program & Placement --}}
                                <div class="student-details-box">
                                    <div class="student-details-heading heading-academic">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-mortarboard-fill"></i>
                                            <span>Academic Program & Enrollment</span>
                                        </div>
                                        <span class="badge" style="background:#4338ca; color:#fff;">
                                            {{ $student->activeSemesterEnrollment?->semester ?? ($student->semester ? 'Semester ' . $student->semester : 'Not Enrolled') }}
                                        </span>
                                    </div>

                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-building"></i> Department</span>
                                        <span class="student-detail-value fw-semibold text-dark">{{ $student->department->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-book"></i> Degree Program</span>
                                        <span class="student-detail-value">
                                            @if($student->program)
                                                <span class="fw-bold text-dark">{{ $student->program->name }}</span>
                                                <span class="badge bg-light text-primary border ms-1">{{ $student->program->code }}</span>
                                                <span class="text-muted ms-1 small">({{ ucfirst($student->program->degree_level ?? 'undergraduate') }})</span>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-diagram-3"></i> Section</span>
                                        <span class="student-detail-value">{{ $student->section->name ?? 'Not assigned' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-door-open"></i> Admission Type</span>
                                        <span class="student-detail-value">
                                            @if($student->admission_type === 'lateral_adp')
                                                <span class="badge" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a;">
                                                    <i class="bi bi-award me-1"></i> ADP Lateral Entry
                                                </span>
                                                <span class="text-muted small ms-1">(Direct intake into Semester {{ $student->admitted_semester ?? 5 }})</span>
                                            @elseif($student->admission_type === 'migration')
                                                <span class="badge" style="background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4;">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Transfer / Migration
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-check-circle me-1 text-success"></i> Regular Intake (Semester 1)
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-calendar3"></i> Admitted Semester</span>
                                        <span class="student-detail-value">Semester {{ $student->admitted_semester ?? 1 }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-clock-history"></i> Current Active Semester</span>
                                        <span class="student-detail-value fw-semibold text-primary">
                                            {{ $student->activeSemesterEnrollment?->semester ?? ($student->semester ? 'Semester ' . $student->semester : 'Not Enrolled') }}
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-calendar-range"></i> Academic Term / Year</span>
                                        <span class="student-detail-value">
                                            {{ $student->activeSemesterEnrollment?->term?->name ?? ($student->activeSemesterEnrollment?->academic_year ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-calendar-check"></i> Enrollment Date</span>
                                        <span class="student-detail-value">
                                            {{ $student->activeSemesterEnrollment?->enrolled_at?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- 3. Previous Academic Record & Merit Scholarship --}}
                                <div class="student-details-box">
                                    <div class="student-details-heading heading-merit">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-award-fill"></i>
                                            <span>Previous Academic Record & Merit Scholarship</span>
                                        </div>
                                        @if($student->hasActiveScholarship())
                                            <span class="badge" style="background:#047857; color:#fff;">
                                                <i class="bi bi-gift-fill me-1"></i> {{ number_format((float) $student->scholarship_percentage, 0) }}% Off
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Standard Intake</span>
                                        @endif
                                    </div>

                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-journal-text"></i> Inter Degree / Group</span>
                                        <span class="student-detail-value fw-semibold text-dark">{{ $student->inter_degree ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-building-check"></i> Board / Institute</span>
                                        <span class="student-detail-value">{{ $student->inter_board ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-upc-scan"></i> Inter Roll Number</span>
                                        <span class="student-detail-value font-monospace">{{ $student->inter_roll_no ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-bar-chart-steps"></i> Intermediate Marks</span>
                                        <span class="student-detail-value">
                                            @if($student->inter_obtained_marks)
                                                <span class="fw-bold text-dark">{{ number_format((float) $student->inter_obtained_marks, 0) }}</span>
                                                <span class="text-muted">/ {{ number_format((float) ($student->inter_total_marks ?? 1100), 0) }} Marks</span>
                                            @else
                                                <span class="text-muted fst-italic">Marks record not entered</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-percent"></i> Inter Percentage</span>
                                        <span class="student-detail-value">
                                            @if($student->inter_percentage)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 fw-bold">
                                                    {{ number_format((float) $student->inter_percentage, 2) }}%
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">N/A</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="student-detail-row">
                                        <span class="student-detail-label"><i class="bi bi-gift-fill"></i> Awarded Scholarship</span>
                                        <span class="student-detail-value">
                                            @if($student->hasActiveScholarship())
                                                <div class="p-2 rounded-3 border" style="background:#ecfdf5; border-color:#a7f3d0 !important;">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge" style="background:#047857; color:#fff; font-weight:700;">
                                                                {{ number_format((float) $student->scholarship_percentage, 0) }}% Off
                                                            </span>
                                                            <strong class="text-success">{{ $student->scholarship_name }}</strong>
                                                        </div>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase" style="font-size:0.7rem;">
                                                            {{ $student->scholarship_status }}
                                                        </span>
                                                    </div>
                                                    <div class="text-muted small mt-1" style="font-size:0.75rem;">
                                                        <i class="bi bi-check2-circle text-success me-1"></i> Discount is strictly applied to the tuition fee component of semester invoices.
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bi bi-dash-circle me-1"></i> No scholarship awarded (Standard 100% tuition fee applies)
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- 4. ADP Lateral Entry & Credit Transfer (Rendered if Lateral/Transfer) --}}
                                @if($student->admission_type === 'lateral_adp' || $student->admission_type === 'migration' || $student->creditTransfer)
                                    @php($transfer = $student->creditTransfer)
                                    <div class="student-details-box">
                                        <div class="student-details-heading heading-lateral">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-journal-arrow-up"></i>
                                                <span>ADP Lateral Entry & Credit Transfer Record</span>
                                            </div>
                                            <span class="badge" style="background:#92400e; color:#fff;">
                                                {{ number_format($student->getTransferredCredits(), 1) }} Cr. Hrs Block
                                            </span>
                                        </div>

                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-building"></i> Prior Institution</span>
                                            <span class="student-detail-value fw-semibold text-dark">{{ $transfer?->previous_institution ?: 'N/A' }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-mortarboard"></i> Prior Qualification / Degree</span>
                                            <span class="student-detail-value">{{ $transfer?->previous_degree ?: 'Associate Degree in Computing' }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-calculator"></i> Prior CGPA / Percentage</span>
                                            <span class="student-detail-value font-monospace">
                                                {{ $transfer?->previous_cgpa ? number_format((float) $transfer->previous_cgpa, 2) . ' / 4.00' : 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-layers"></i> Transferred Credits</span>
                                            <span class="student-detail-value">
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold fs-6">
                                                    {{ number_format($student->getTransferredCredits(), 1) }} Cr. Hrs Block Exemption
                                                </span>
                                            </span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-box-arrow-in-right"></i> Admitted to Semester</span>
                                            <span class="student-detail-value fw-bold text-dark">Semester {{ $transfer?->admitted_to_semester ?? ($student->admitted_semester ?? 5) }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-file-earmark-check"></i> Equivalence Document</span>
                                            <span class="student-detail-value">
                                                @if($transfer?->document_path)
                                                    <a href="{{ route('students.transfer-document', $student) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-2">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                        <span>Download Equivalence Document ({{ $transfer->document_name ?? 'Document' }})</span>
                                                    </a>
                                                @else
                                                    <span class="text-muted fst-italic">No equivalence document uploaded</span>
                                                @endif
                                            </span>
                                        </div>
                                        @if($transfer?->remarks)
                                            <div class="student-detail-row">
                                                <span class="student-detail-label"><i class="bi bi-chat-left-quote"></i> Equivalence Remarks</span>
                                                <span class="student-detail-value text-muted">{{ $transfer->remarks }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- 5. Active Semester Registered Courses --}}
                                <div class="student-details-box">
                                    <div class="student-details-heading heading-courses">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-book-half"></i>
                                            <span>Active Semester Registered Courses</span>
                                        </div>
                                        @if($student->activeSemesterEnrollment && $student->activeSemesterEnrollment->courses->isNotEmpty())
                                            <span class="badge" style="background:#5b21b6; color:#fff;">
                                                {{ $student->activeSemesterEnrollment->courses->count() }} Courses
                                            </span>
                                        @endif
                                    </div>

                                    @if($student->activeSemesterEnrollment && $student->activeSemesterEnrollment->courses->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="modal-courses-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th style="width: 140px;">Course Code</th>
                                                        <th>Course Title</th>
                                                        <th style="width: 120px; text-align: right;">Credit Hours</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php($totalCredits = 0)
                                                    @foreach($student->activeSemesterEnrollment->courses as $enrollmentCourse)
                                                        @php($totalCredits += (float) $enrollmentCourse->credit_hours)
                                                        <tr>
                                                            <td class="text-muted">{{ $loop->iteration }}</td>
                                                            <td>
                                                                <span class="badge bg-light text-dark border font-monospace">
                                                                    {{ $enrollmentCourse->course->code ?? 'N/A' }}
                                                                </span>
                                                            </td>
                                                            <td class="fw-semibold text-dark">
                                                                {{ $enrollmentCourse->course->name ?? 'Course Removed' }}
                                                            </td>
                                                            <td style="text-align: right;">
                                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                                    {{ number_format((float) $enrollmentCourse->credit_hours, 1) }} Cr. Hrs
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" class="text-end text-muted">Total Registered Credit Hours:</td>
                                                        <td style="text-align: right;">
                                                            <span class="badge bg-primary text-white fs-6">
                                                                {{ number_format($totalCredits, 1) }} Cr. Hrs
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @elseif($student->assigned_courses->isNotEmpty())
                                        <div class="p-3">
                                            <div class="student-course-list">
                                                @foreach ($student->assigned_courses as $c)
                                                    <span class="student-course-badge">
                                                        <span class="font-monospace me-1">{{ $c->code }}</span>
                                                        {{ $c->name }}
                                                        <small class="ms-1">({{ $c->credit_hours }} Cr. Hrs)</small>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-muted">
                                            <i class="bi bi-journal-x me-1"></i> No course registrations found for the active semester.
                                        </div>
                                    @endif
                                </div>

                                {{-- 6. Fee & Financial Invoice Snapshot --}}
                                @php($invoice = $student->activeSemesterEnrollment?->feeInvoice)
                                <div class="student-details-box">
                                    <div class="student-details-heading heading-fee">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-cash-stack"></i>
                                            <span>Active Semester Fee & Challan Status</span>
                                        </div>
                                        @if($invoice)
                                            @if($invoice->status === 'paid')
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                            @elseif($invoice->status === 'partial')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Partial</span>
                                            @elseif($invoice->status === 'overdue')
                                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Overdue</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> {{ ucfirst($invoice->status) }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border">No Invoice</span>
                                        @endif
                                    </div>

                                    @if($invoice)
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-receipt"></i> Invoice Number</span>
                                            <span class="student-detail-value font-monospace fw-bold text-dark">{{ $invoice->invoice_number }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-calendar-event"></i> Billing Term / Sem</span>
                                            <span class="student-detail-value">{{ $student->activeSemesterEnrollment->semester }} ({{ $student->activeSemesterEnrollment->academic_year }})</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-calculator"></i> Gross Subtotal</span>
                                            <span class="student-detail-value">PKR {{ number_format((float) $invoice->subtotal, 2) }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-gift"></i> Scholarship Discount</span>
                                            <span class="student-detail-value">
                                                @if((float) $invoice->discount_amount > 0)
                                                    <span class="text-success fw-bold">
                                                        - PKR {{ number_format((float) $invoice->discount_amount, 2) }}
                                                    </span>
                                                    <span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #6ee7b7; font-size:0.7rem; margin-left:6px;">
                                                        {{ number_format((float) $student->scholarship_percentage, 0) }}% Off Tuition
                                                    </span>
                                                @else
                                                    <span class="text-muted">PKR 0.00</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-wallet2"></i> Net Total Payable</span>
                                            <span class="student-detail-value">
                                                <strong class="text-primary fs-6">PKR {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                                            </span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-calendar-x"></i> Due Date</span>
                                            <span class="student-detail-value">{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</span>
                                        </div>
                                        <div class="student-detail-row">
                                            <span class="student-detail-label"><i class="bi bi-printer"></i> Bank Challan</span>
                                            <span class="student-detail-value">
                                                <a href="{{ route('fees.invoices.challan-pdf', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-2">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                    <span>Print 3-Copy Bank Challan PDF</span>
                                                </a>
                                            </span>
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No fee invoice has been generated for this student's active semester enrollment.
                                        </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer student-modal-footer">
                        <button type="button" class="btn student-close-btn" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Close
                        </button>
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

            let activeScholarshipFilter = false;

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
                    const matchesSchol = !activeScholarshipFilter || row.dataset.scholarship === 'active';

                    const isVisible = matchesSearch && matchesDept && matchesSem && matchesAdm && matchesSchol;
                    row.hidden = !isVisible;
                    if (isVisible) {
                        matches++;
                        const serialCell = row.querySelector('.row-serial');
                        if (serialCell) serialCell.textContent = matches;
                    }
                });

                if (noResults) noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query || selectedDept || selectedSem || selectedAdm || activeScholarshipFilter);
                if (clear) clear.hidden = !query;

                const isFiltered = Boolean(query || selectedDept || selectedSem || selectedAdm || activeScholarshipFilter);
                if (count) {
                    count.textContent = isFiltered ? `${matches} of ${rows.length} students` : `${rows.length} students`;
                }

                // Update active state on pills
                pillBtns.forEach(pill => {
                    const type = pill.dataset.filterType;
                    const val = pill.dataset.filterValue;
                    if (type === 'all') {
                        pill.classList.toggle('is-active', !selectedDept && !selectedSem && !selectedAdm && !activeScholarshipFilter);
                    } else if (type === 'department') {
                        pill.classList.toggle('is-active', selectedDept === val);
                    } else if (type === 'admission') {
                        pill.classList.toggle('is-active', selectedAdm === val);
                    } else if (type === 'scholarship') {
                        pill.classList.toggle('is-active', activeScholarshipFilter);
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
                        activeScholarshipFilter = false;
                    } else if (type === 'department') {
                        if (deptFilter) deptFilter.value = (deptFilter.value === val ? '' : val);
                    } else if (type === 'admission') {
                        if (admFilter) admFilter.value = (admFilter.value === val ? '' : val);
                    } else if (type === 'scholarship') {
                        activeScholarshipFilter = !activeScholarshipFilter;
                    }
                    filterStudents();
                });
            });

            resetBtn?.addEventListener('click', function () {
                if (search) search.value = '';
                if (deptFilter) deptFilter.value = '';
                if (semFilter) semFilter.value = '';
                if (admFilter) admFilter.value = '';
                activeScholarshipFilter = false;
                filterStudents();
            });
        });
    </script>
@endsection
