@extends('website.layout')

@section('title', 'Applicant Admission Workspace | My Form Task University')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
<!-- Hero Strip -->
<section class="admissions-hero py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">
                <i class="bi bi-mortarboard-fill me-1"></i> Applicant Admission Workspace
            </span>
            <h1 class="h2 fw-bold text-white mb-1">Welcome, {{ $user->name }}</h1>
            <p class="text-white-50 mb-0 small">
                Logged in as <strong>{{ $user->email }}</strong> (Phone: {{ $user->phone }})
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($application)

                <!-- CASE 1: OFFICIALLY ENROLLED AS STUDENT -->
                @if($application->isEnrolled())
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%); color: #ffffff;">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                                <div>
                                    <span class="badge bg-white text-success px-3 py-1.5 rounded-pill fw-bold mb-3 shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> Admission Confirmed &amp; Enrolled
                                    </span>
                                    <h2 class="display-6 fw-bold mb-2">Congratulations, {{ $user->name }}!</h2>
                                    <p class="lead mb-4 text-white-50">
                                        You are officially enrolled as a University Student in <strong>{{ $application->program->name ?? 'Degree Program' }}</strong>.
                                    </p>
                                    <div class="row g-3 text-white mb-4">
                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.15);">
                                                <small class="d-block text-white-50 text-uppercase fw-bold" style="font-size: 0.72rem;">Registration No.</small>
                                                <span class="fs-5 fw-bold font-monospace">{{ $application->enrolledStudent->registration_no ?? 'Assigned' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.15);">
                                                <small class="d-block text-white-50 text-uppercase fw-bold" style="font-size: 0.72rem;">Class Section</small>
                                                <span class="fs-5 fw-bold">{{ $application->enrolledStudent->section->name ?? 'Assigned' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.15);">
                                                <small class="d-block text-white-50 text-uppercase fw-bold" style="font-size: 0.72rem;">Semester</small>
                                                <span class="fs-5 fw-bold">{{ $application->enrolledStudent->semester ?? 'Semester 1' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.15);">
                                                <small class="d-block text-white-50 text-uppercase fw-bold" style="font-size: 0.72rem;">Department</small>
                                                <span class="fs-6 fw-bold">{{ $application->department->name ?? 'Faculty' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('student.dashboard') }}" class="btn btn-light btn-lg text-success fw-bold px-4 py-2.5 rounded-3 shadow">
                                            <i class="bi bi-mortarboard-fill me-1"></i> Enter Official Student Portal
                                        </a>
                                        <a href="{{ route('admissions.confirmation', $application->application_no) }}" class="btn btn-outline-light btn-lg px-4 py-2.5 rounded-3">
                                            <i class="bi bi-printer me-1"></i> Print Admission Receipt
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- CASE 2: APPLICATION IN REVIEW -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header py-3 px-4 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Application Reference</small>
                            <span class="fs-5 fw-bold text-dark font-monospace">{{ $application->application_no }}</span>
                        </div>
                        <span class="badge {{ $application->getStatusBadgeClass() }} px-3 py-2 rounded-pill fs-6">
                            {{ $application->getStatusLabel() }}
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Summary info -->
                        <div class="row g-3 pb-3 mb-4 border-bottom">
                            <div class="col-sm-6 col-md-3">
                                <small class="text-muted d-block">Applied Degree Program</small>
                                <strong class="text-primary">{{ $application->program->name ?? 'General Program' }}</strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <small class="text-muted d-block">Department</small>
                                <strong class="text-dark">{{ $application->department->name ?? 'Faculty' }}</strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <small class="text-muted d-block">Admission Pathway</small>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $application->admission_type)) }}
                                </span>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <small class="text-muted d-block">Submission Date</small>
                                <span>{{ $application->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>

                        <!-- Milestone Progress Tracker -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Application Milestone Progress</h6>

                        <div class="application-status-timeline mb-4">
                            <!-- Step 1: Submission -->
                            <div class="timeline-item done">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong class="d-block text-dark small">1. Online Application Submitted</strong>
                                    <small class="text-muted">Application filed with tracking number {{ $application->application_no }}.</small>
                                </div>
                            </div>

                            <!-- Step 2: Verification -->
                            <div class="timeline-item {{ in_array($application->status, ['under_review', 'approved', 'enrolled'], true) ? 'done' : 'active' }}">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong class="d-block text-dark small">2. Academic &amp; Document Scrutiny</strong>
                                    <small class="text-muted">
                                        @if($application->status === 'pending')
                                            In queue for credential verification by the Admissions Office.
                                        @else
                                            HSSC credentials and identity documents verified.
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Step 3: Merit & Approval -->
                            <div class="timeline-item {{ in_array($application->status, ['approved', 'enrolled'], true) ? 'done' : ($application->status === 'under_review' ? 'active' : '') }}">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong class="d-block text-dark small">3. Merit Evaluation &amp; Admission Offer</strong>
                                    <small class="text-muted">
                                        @if(in_array($application->status, ['approved', 'enrolled'], true))
                                            Candidate qualified for admission offer. Approved for enrollment!
                                        @else
                                            Merit evaluation in progress according to department intake quotas.
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Step 4: Portal Unlock -->
                            <div class="timeline-item {{ $application->isEnrolled() ? 'done' : '' }}">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong class="d-block text-dark small">4. Final Enrollment &amp; Student Portal Access</strong>
                                    <small class="text-muted">
                                        @if($application->isEnrolled())
                                            Roll number assigned and Student Portal access unlocked!
                                        @else
                                            <span class="text-secondary"><i class="bi bi-lock-fill me-1"></i> Locked · Unlocks automatically upon official enrollment by Admissions Committee.</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks if any -->
                        @if($application->admin_notes)
                            <div class="p-3 rounded-3 bg-light border mb-4">
                                <small class="text-muted d-block fw-bold mb-1">Admissions Committee Notes / Instructions:</small>
                                <span class="small text-dark">{{ $application->admin_notes }}</span>
                            </div>
                        @endif

                        <!-- Action Toolbar -->
                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <a href="{{ route('admissions.confirmation', $application->application_no) }}" class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-printer me-1"></i> Print Application Slip
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#applicantDetailsModal">
                                <i class="bi bi-card-checklist me-1"></i> View Submitted Details
                            </button>
                            @if($application->isEnrolled())
                                <a href="{{ route('student.dashboard') }}" class="btn btn-success btn-sm rounded-3 ms-auto fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Open Student Portal
                                </a>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- DETAILS MODAL -->
                <div class="modal fade" id="applicantDetailsModal" tabindex="-1" aria-labelledby="applicantDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold" id="applicantDetailsModalLabel">
                                    Application #{{ $application->application_no }} Details
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h6 class="fw-bold text-primary mb-3">Personal &amp; Contact Details</h6>
                                <div class="row g-2 mb-4">
                                    <div class="col-6"><strong>Full Name:</strong> {{ $application->name }}</div>
                                    <div class="col-6"><strong>Father / Guardian:</strong> {{ $application->father_name ?? 'N/A' }}</div>
                                    <div class="col-6"><strong>CNIC / B-Form:</strong> {{ $application->cnic ?? 'N/A' }}</div>
                                    <div class="col-6"><strong>Gender:</strong> {{ ucfirst($application->gender ?? 'N/A') }}</div>
                                    <div class="col-6"><strong>Email:</strong> {{ $application->email }}</div>
                                    <div class="col-6"><strong>Phone:</strong> {{ $application->phone }}</div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3">Intermediate / HSSC Qualifications</h6>
                                <div class="row g-2 mb-4">
                                    <div class="col-4"><strong>Degree:</strong> {{ $application->inter_degree ?? 'N/A' }}</div>
                                    <div class="col-4"><strong>Board:</strong> {{ $application->inter_board ?? 'N/A' }}</div>
                                    <div class="col-4"><strong>Roll No:</strong> {{ $application->inter_roll_no ?? 'N/A' }}</div>
                                    <div class="col-4"><strong>Obtained Marks:</strong> {{ $application->inter_obtained_marks ?? 'N/A' }}</div>
                                    <div class="col-4"><strong>Total Marks:</strong> {{ $application->inter_total_marks ?? 'N/A' }}</div>
                                    <div class="col-4"><strong>Percentage:</strong> {{ $application->inter_percentage ? number_format($application->inter_percentage, 1).'%' : 'N/A' }}</div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3">Program &amp; Department Preference</h6>
                                <div class="row g-2">
                                    <div class="col-6"><strong>Department:</strong> {{ $application->department->name ?? 'N/A' }}</div>
                                    <div class="col-6"><strong>Program:</strong> {{ $application->program->name ?? 'N/A' }}</div>
                                    <div class="col-6"><strong>Pathway:</strong> {{ ucfirst(str_replace('_', ' ', $application->admission_type)) }}</div>
                                    <div class="col-6"><strong>Scholarship Requested:</strong> {{ $application->scholarship_name ?? 'None' }}</div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            @else

                <!-- CASE 3: NO APPLICATION FILED YET -->
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
                    <div class="mb-4">
                        <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                            <i class="bi bi-mortarboard-fill display-4"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Ready to Begin Your Admission Application?</h3>
                        <p class="text-muted max-w-lg mx-auto" style="max-width: 540px;">
                            Your applicant account is ready. Fill out your academic credentials and degree preferences to submit your application for Fall 2026.
                        </p>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admissions.apply') }}" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 shadow-sm">
                            <i class="bi bi-pencil-square me-2"></i> Start Admission Application
                        </a>
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>
@endsection
