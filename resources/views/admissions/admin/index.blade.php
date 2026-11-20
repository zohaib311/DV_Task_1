@extends('welcome')

@section('title', 'Online Admissions Management')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header Section -->
    <div class="admission-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-white-50" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboardView') }}" class="text-white-50 text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Admissions</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-1 text-white d-flex align-items-center gap-2">
                <i class="bi bi-mortarboard-fill"></i>
                Online Admissions Management
            </h2>
            <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                Review student applications, verify credentials, update application status, and enroll approved candidates directly into student rosters.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admissions.apply') }}" target="_blank" class="btn btn-light btn-sm fw-semibold shadow-sm text-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Public Apply Form
            </a>
            <a href="{{ route('admissions.status') }}" target="_blank" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-search me-1"></i> Track Status
            </a>
            <a href="{{ route('admissions.admin.inquiries') }}" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-chat-left-dots me-1"></i> Website Inquiries
            </a>
        </div>
    </div>

    <!-- Session Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Please check the form errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Metric Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index') }}" class="admission-kpi-card {{ !request('status') || request('status') === 'all' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Apps</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['total']) }}</div>
                    </div>
                    <div class="admission-kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-files"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" class="admission-kpi-card {{ request('status') === 'pending' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-warning small fw-semibold text-uppercase">Pending</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['pending']) }}</div>
                    </div>
                    <div class="admission-kpi-icon bg-warning bg-opacity-15 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index', array_merge(request()->except('page'), ['status' => 'under_review'])) }}" class="admission-kpi-card {{ request('status') === 'under_review' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-info small fw-semibold text-uppercase">Reviewing</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['under_review']) }}</div>
                    </div>
                    <div class="admission-kpi-icon bg-info bg-opacity-15 text-info">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}" class="admission-kpi-card {{ request('status') === 'approved' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-success small fw-semibold text-uppercase">Approved</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['approved']) }}</div>
                    </div>
                    <div class="admission-kpi-icon bg-success bg-opacity-15 text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index', array_merge(request()->except('page'), ['status' => 'enrolled'])) }}" class="admission-kpi-card {{ request('status') === 'enrolled' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-indigo small fw-semibold text-uppercase" style="color: #6366f1;">Enrolled</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['enrolled']) }}</div>
                    </div>
                    <div class="admission-kpi-icon text-white" style="background: #6366f1;">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admissions.admin.index', array_merge(request()->except('page'), ['status' => 'rejected'])) }}" class="admission-kpi-card {{ request('status') === 'rejected' ? 'is-active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-danger small fw-semibold text-uppercase">Rejected</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpi['rejected']) }}</div>
                    </div>
                    <div class="admission-kpi-icon bg-danger bg-opacity-15 text-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="admission-filter-card">
        <form method="GET" action="{{ route('admissions.admin.index') }}" class="row g-2 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Search by App No, name, CNIC, email, phone...">
                </div>
            </div>

            <div class="col-lg-3 col-md-3 col-6">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <select name="admission_type" class="form-select">
                    <option value="">All Admission Types</option>
                    <option value="regular" {{ request('admission_type') === 'regular' ? 'selected' : '' }}>Regular (Sem 1)</option>
                    <option value="lateral_adp" {{ request('admission_type') === 'lateral_adp' ? 'selected' : '' }}>ADP Lateral (Sem 5)</option>
                    <option value="migration" {{ request('admission_type') === 'migration' ? 'selected' : '' }}>Transfer / Migration</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <select name="status" class="form-select">
                    <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="enrolled" {{ request('status') === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-lg-1 col-md-6 col-6 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" title="Apply Filters">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if(request()->hasAny(['q', 'department_id', 'admission_type', 'status']))
                    <a href="{{ route('admissions.admin.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="admission-table-card">
        <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span>Applications Callset</span>
                <span class="badge bg-light text-dark border">{{ $applications->total() }} records</span>
            </h5>
            <small class="text-muted">Showing page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}</small>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>App No</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>Program & Dept</th>
                        <th>Pathway</th>
                        <th>HSSC Marks / %</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-end" style="min-width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $index => $app)
                        <tr>
                            <td class="text-muted fw-semibold">{{ $applications->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    {{ $app->application_no }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $app->name }}</div>
                                <div class="text-muted small font-monospace">CNIC: {{ $app->cnic }}</div>
                            </td>
                            <td>
                                <div><a href="mailto:{{ $app->email }}" class="text-decoration-none text-dark small"><i class="bi bi-envelope me-1 text-primary"></i>{{ $app->email }}</a></div>
                                <div class="text-muted small"><i class="bi bi-telephone me-1 text-success"></i>{{ $app->phone }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $app->program->name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $app->department->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if($app->admission_type === 'lateral_adp')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-right-circle me-1"></i>ADP Lateral (Sem 5)</span>
                                @elseif($app->admission_type === 'migration')
                                    <span class="badge bg-secondary"><i class="bi bi-arrow-left-right me-1"></i>Migration / Transfer</span>
                                @else
                                    <span class="badge bg-primary"><i class="bi bi-person-check me-1"></i>Regular (Sem 1)</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $app->inter_obtained_marks }} / {{ $app->inter_total_marks }}</div>
                                <div class="small">
                                    <span class="badge bg-info bg-opacity-25 text-dark">{{ number_format($app->inter_percentage, 1) }}%</span>
                                    <span class="text-muted ms-1">{{ $app->inter_degree }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $app->getStatusBadgeClass() }} px-2.5 py-1.5 fs-7">
                                    {{ $app->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <div>{{ $app->created_at->format('d M Y') }}</div>
                                <div>{{ $app->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <!-- View & Review Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewAppModal{{ $app->id }}" title="View application details & review status">
                                        <i class="bi bi-eye"></i> Details
                                    </button>

                                    <!-- Direct Enroll Button -->
                                    @if($app->isEnrolled())
                                        <a href="{{ route('allStudents') }}?q={{ $app->enrolledStudent->registration_no ?? $app->cnic }}" class="btn btn-sm btn-success text-white" title="View Enrolled Student Record">
                                            <i class="bi bi-mortarboard-fill"></i> Enrolled
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollAppModal{{ $app->id }}" title="Enroll applicant as official student">
                                            <i class="bi bi-person-plus-fill"></i> Enroll
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- VIEW / REVIEW MODAL -->
                        <div class="modal fade" id="viewAppModal{{ $app->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $app->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light border-bottom">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="viewModalLabel{{ $app->id }}">
                                                <span>Application #{{ $app->application_no }}</span>
                                                <span class="badge {{ $app->getStatusBadgeClass() }}">{{ $app->getStatusLabel() }}</span>
                                            </h5>
                                            <small class="text-muted">Submitted on {{ $app->created_at->format('F d, Y \a\t h:i A') }}</small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">

                                        <!-- Section 1: Personal Details -->
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-1"></i> Personal Information</h6>
                                        <div class="row g-2 mb-4">
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Full Name</div>
                                                    <div class="admission-detail-val">{{ $app->name }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Father / Guardian Name</div>
                                                    <div class="admission-detail-val">{{ $app->father_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">CNIC / B-Form</div>
                                                    <div class="admission-detail-val font-monospace">{{ $app->cnic }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Date of Birth</div>
                                                    <div class="admission-detail-val">{{ $app->date_of_birth ? $app->date_of_birth->format('d M Y') : 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Gender</div>
                                                    <div class="admission-detail-val">{{ ucfirst($app->gender ?? 'N/A') }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Email Address</div>
                                                    <div class="admission-detail-val">{{ $app->email }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Phone Number</div>
                                                    <div class="admission-detail-val">{{ $app->phone }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Residential Address</div>
                                                    <div class="admission-detail-val">{{ $app->address ?? 'N/A' }} (Domicile/City: {{ $app->domicile ?? 'N/A' }})</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 2: Academic Qualifications -->
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-book me-1"></i> Intermediate / HSSC Qualification</h6>
                                        <div class="row g-2 mb-4">
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Degree Title</div>
                                                    <div class="admission-detail-val">{{ $app->inter_degree }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Board / Authority</div>
                                                    <div class="admission-detail-val">{{ $app->inter_board }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Roll Number</div>
                                                    <div class="admission-detail-val font-monospace">{{ $app->inter_roll_no }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Marks Obtained</div>
                                                    <div class="admission-detail-val">{{ $app->inter_obtained_marks }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Total Marks</div>
                                                    <div class="admission-detail-val">{{ $app->inter_total_marks }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Calculated Percentage</div>
                                                    <div class="admission-detail-val text-primary">{{ number_format($app->inter_percentage, 2) }}%</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 3: Program & Lateral Info -->
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-diagram-3 me-1"></i> Program Choice & Pathway</h6>
                                        <div class="row g-2 mb-4">
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Department</div>
                                                    <div class="admission-detail-val">{{ $app->department->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Degree Program</div>
                                                    <div class="admission-detail-val">{{ $app->program->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Admission Type</div>
                                                    <div class="admission-detail-val">
                                                        @if($app->admission_type === 'lateral_adp')
                                                            ADP Lateral Entry (Direct to Semester 5)
                                                        @elseif($app->admission_type === 'migration')
                                                            University Transfer / Migration
                                                        @else
                                                            Regular Entry (Semester 1)
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admission-detail-block">
                                                    <div class="admission-detail-label">Scholarship Requested</div>
                                                    <div class="admission-detail-val">
                                                        {{ $app->scholarship_name ?? 'None' }} 
                                                        @if($app->scholarship_percentage > 0)
                                                            ({{ number_format($app->scholarship_percentage, 0) }}%)
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if(in_array($app->admission_type, ['lateral_adp', 'migration'], true))
                                                <div class="col-md-6">
                                                    <div class="admission-detail-block">
                                                        <div class="admission-detail-label">Previous Institution</div>
                                                        <div class="admission-detail-val">{{ $app->previous_institution ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="admission-detail-block">
                                                        <div class="admission-detail-label">Previous Degree</div>
                                                        <div class="admission-detail-val">{{ $app->previous_degree ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="admission-detail-block">
                                                        <div class="admission-detail-label">Previous CGPA</div>
                                                        <div class="admission-detail-val">{{ $app->previous_cgpa ? number_format($app->previous_cgpa, 2) : 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="admission-detail-block">
                                                        <div class="admission-detail-label">Transferred Credits</div>
                                                        <div class="admission-detail-val">{{ $app->transferred_credits ?? 64.0 }} Credits</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Section 4: Attached Documents -->
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-paperclip me-1"></i> Submitted Documents</h6>
                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                            @if($app->cnic_document_path)
                                                <a href="{{ asset('storage/' . $app->cnic_document_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-file-earmark-person"></i> View CNIC Copy
                                                </a>
                                            @endif
                                            @if($app->marksheet_document_path)
                                                <a href="{{ asset('storage/' . $app->marksheet_document_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-file-earmark-text"></i> View HSSC Marksheet
                                                </a>
                                            @endif
                                            @if($app->document_path)
                                                <a href="{{ asset('storage/' . $app->document_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-file-earmark-check"></i> View Transcript / Transfer Certificate
                                                </a>
                                            @endif
                                            @if(!$app->cnic_document_path && !$app->marksheet_document_path && !$app->document_path)
                                                <div class="text-muted small fst-italic">No digital document attachments were uploaded with this submission.</div>
                                            @endif
                                        </div>

                                        <!-- Section 5: Review & Status Update Form -->
                                        @if(!$app->isEnrolled())
                                            <div class="card border border-primary border-opacity-25 bg-primary bg-opacity-10 p-3 rounded-3">
                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-pencil-square me-1"></i> Update Admission Review Status</h6>
                                                <form method="POST" action="{{ route('admissions.admin.status', $app->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="row g-2 align-items-end">
                                                        <div class="col-md-5">
                                                            <label class="form-label small fw-semibold">Application Status</label>
                                                            <select name="status" class="form-select form-select-sm" required>
                                                                <option value="pending" {{ $app->status === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                                                <option value="under_review" {{ $app->status === 'under_review' ? 'selected' : '' }}>Under Academic Review</option>
                                                                <option value="approved" {{ $app->status === 'approved' ? 'selected' : '' }}>Approved for Admission</option>
                                                                <option value="rejected" {{ $app->status === 'rejected' ? 'selected' : '' }}>Rejected / Ineligible</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <label class="form-label small fw-semibold">Review Remarks / Reason</label>
                                                            <input type="text" name="admin_notes" value="{{ $app->admin_notes }}" class="form-control form-control-sm" placeholder="e.g. Verified HSSC credentials, eligible for merit list...">
                                                        </div>
                                                        <div class="col-12 mt-2 text-end">
                                                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                                                <i class="bi bi-check2"></i> Save Status Change
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @else
                                            <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
                                                <i class="bi bi-check-circle-fill fs-5"></i>
                                                <div>
                                                    <strong>Enrolled as Official Student:</strong> Registration Number: 
                                                    <span class="font-monospace fw-bold">{{ $app->enrolledStudent->registration_no ?? 'Assigned' }}</span>
                                                    on {{ $app->enrolled_at ? $app->enrolled_at->format('d M Y') : 'Recently' }}.
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                    <div class="modal-footer bg-light border-top">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                        @if(!$app->isEnrolled())
                                            <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#enrollAppModal{{ $app->id }}">
                                                <i class="bi bi-person-plus-fill me-1"></i> Proceed to Enroll
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ENROLL MODAL -->
                        @if(!$app->isEnrolled())
                            <div class="modal fade" id="enrollAppModal{{ $app->id }}" tabindex="-1" aria-labelledby="enrollModalLabel{{ $app->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST" action="{{ route('admissions.admin.enroll', $app->id) }}">
                                            @csrf
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold" id="enrollModalLabel{{ $app->id }}">
                                                    <i class="bi bi-mortarboard me-1"></i> Enroll Applicant as Student
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                
                                                <div class="alert alert-light border mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-dark">{{ $app->name }}</strong>
                                                        <span class="badge bg-primary">{{ $app->application_no }}</span>
                                                    </div>
                                                    <div class="text-muted small">Program: <strong>{{ $app->program->name ?? 'General' }}</strong></div>
                                                    <div class="text-muted small">Department: <strong>{{ $app->department->name ?? 'General' }}</strong></div>
                                                    <div class="text-muted small mt-1">
                                                        @if($app->admission_type === 'lateral_adp')
                                                            <span class="badge bg-warning text-dark">Admitting to: Semester 5 (ADP Lateral)</span>
                                                        @else
                                                            <span class="badge bg-info text-dark">Admitting to: Semester 1 (Regular)</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Section Selection -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Assign Section <span class="text-danger">*</span></label>
                                                    <select name="section_id" class="form-select" required>
                                                        <option value="">-- Choose Section --</option>
                                                        @php
                                                            // Prioritize sections matching applicant's department
                                                            $deptSections = $sections->where('department_id', $app->department_id);
                                                            $otherSections = $sections->where('department_id', '!=', $app->department_id);
                                                        @endphp
                                                        @if($deptSections->count() > 0)
                                                            <optgroup label="Sections in {{ $app->department->name ?? 'Applied Dept' }}">
                                                                @foreach($deptSections as $sec)
                                                                    <option value="{{ $sec->id }}" {{ $loop->first ? 'selected' : '' }}>
                                                                        {{ $sec->name }} ({{ $sec->department->name ?? '' }})
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                        @if($otherSections->count() > 0)
                                                            <optgroup label="Other University Sections">
                                                                @foreach($otherSections as $sec)
                                                                    <option value="{{ $sec->id }}">
                                                                        {{ $sec->name }} ({{ $sec->department->name ?? '' }})
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                    </select>
                                                    <div class="form-text">Choose the classroom section for student timetable and attendance rosters.</div>
                                                </div>

                                                <!-- Scholarship Percentage -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Scholarship / Fee Waiver %</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" max="100" name="scholarship_percentage" class="form-control" value="{{ $app->scholarship_percentage ?? 0 }}" placeholder="0">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    @if($app->scholarship_name)
                                                        <div class="form-text text-primary">Applicant requested: {{ $app->scholarship_name }}</div>
                                                    @endif
                                                </div>

                                                <!-- Portal User Account -->
                                                <div class="border rounded-3 p-3 bg-light mb-3">
                                                    <div class="form-check form-switch mb-2">
                                                        <input class="form-check-input" type="checkbox" name="create_portal_account" value="1" id="portalAccCheck{{ $app->id }}" checked>
                                                        <label class="form-check-label fw-semibold" for="portalAccCheck{{ $app->id }}">
                                                            Create Student Portal Login Account
                                                        </label>
                                                    </div>
                                                    <div class="portal-password-box mt-2">
                                                        <label class="form-label small text-muted">Initial Password (Default: Password@123)</label>
                                                        <input type="text" name="account_password" class="form-control form-control-sm font-monospace" value="Password@123">
                                                        <div class="form-text small">Student will log in with email: <strong>{{ $app->email }}</strong></div>
                                                    </div>
                                                </div>

                                                <!-- Admin Notes -->
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Enrollment Remarks / Memo</label>
                                                    <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Verified by Admissions Committee, enrolled in Fall 2026.">{{ $app->admin_notes }}</textarea>
                                                </div>

                                            </div>
                                            <div class="modal-footer bg-light border-top">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Confirm & Enroll as Student
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <h5>No admission applications found</h5>
                                    <p class="small mb-0">Try clearing filters or search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="p-3 bg-white border-top d-flex justify-content-center">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection