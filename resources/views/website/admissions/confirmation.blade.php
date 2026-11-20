@extends('website.layout')

@section('title', 'Application Receipt #' . $application->application_no . ' | My Form Task University')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Success Banner -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-success text-white fs-1 shadow-sm mb-3">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1">Admission Application Submitted!</h2>
                    <p class="text-muted">
                        Thank you, <strong>{{ $application->name }}</strong>. Your admission application has been safely received by the Admissions Committee.
                    </p>
                </div>

                <!-- Printable Receipt Slip -->
                <div class="admissions-receipt-card mb-4" id="printableReceipt">
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ asset('images/logo.jfif') }}" alt="MFTU Logo" style="height: 48px;" onerror="this.onerror=null;this.src='{{ asset('storage/images/default-user.png') }}';">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">My Form Task University</h5>
                                <small class="text-muted">Admissions Office · Session Fall 2026-2027</small>
                            </div>
                        </div>

                        <div class="text-end">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Application Tracking No.</small>
                            <span class="fs-4 fw-black text-primary font-monospace">{{ $application->application_no }}</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Applicant Name</small>
                            <strong class="text-dark fs-6">{{ $application->name }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Father / Guardian Name</small>
                            <strong class="text-dark fs-6">{{ $application->father_name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Email Address</small>
                            <span class="text-dark">{{ $application->email }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Contact Phone</small>
                            <span class="text-dark">{{ $application->phone }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">CNIC / Form-B</small>
                            <span class="font-monospace text-dark">{{ $application->cnic ?? 'Not Provided' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Application Submission Date</small>
                            <span class="text-dark">{{ $application->created_at->format('l, d F Y - h:i A') }}</span>
                        </div>
                    </div>

                    <!-- Program Selection Box -->
                    <div class="p-3 rounded-3 bg-light border mb-4">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <small class="text-muted d-block">Applied Degree Program</small>
                                <strong class="text-primary fs-6">{{ $application->program->name ?? 'General Program' }}</strong>
                                <span class="d-block small text-muted">{{ $application->department->name ?? '' }}</span>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <small class="text-muted d-block">Admission Pathway</small>
                                @if($application->admission_type === 'lateral_adp')
                                    <span class="badge bg-warning text-dark px-2 py-1">ADP Lateral Entry (5th Sem)</span>
                                @elseif($application->admission_type === 'migration')
                                    <span class="badge bg-info text-dark px-2 py-1">Transfer / Migration</span>
                                @else
                                    <span class="badge bg-primary text-white px-2 py-1">Regular Intake (1st Sem)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($application->inter_percentage)
                        <div class="row g-3 mb-4 pb-3 border-bottom">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Intermediate Degree</small>
                                <strong class="text-dark">{{ $application->inter_degree ?? 'HSSC' }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Board</small>
                                <strong class="text-dark">{{ $application->inter_board ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Aggregate Marks</small>
                                <strong class="text-success">{{ $application->inter_obtained_marks }} / {{ $application->inter_total_marks }} ({{ number_format((float) $application->inter_percentage, 2) }}%)</strong>
                            </div>
                        </div>
                    @endif

                    <!-- Current Status Alert -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            <div>
                                <strong class="text-dark d-block">Application Status: {{ $application->getStatusLabel() }}</strong>
                                <small class="text-secondary">Your profile is queued for academic committee merit calculation.</small>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill">Pending Verification</span>
                    </div>
                </div>

                <!-- Next Steps Guide -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-compass text-primary me-2"></i>What Happens Next?</h5>
                    <ol class="small text-secondary mb-0 ps-3" style="line-height: 2;">
                        <li><strong>Scrutiny &amp; Verification:</strong> The admissions board verifies educational documents and compute merit scores.</li>
                        <li><strong>Merit List Announcement:</strong> Candidates will be notified via email/SMS regarding merit rankings and interview dates.</li>
                        <li><strong>Official Enrollment:</strong> Once approved, the university issues your official Student Registration Number (<code>REG-2026-XXXX</code>) and student portal credentials!</li>
                    </ol>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3 no-print">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i>
                        <span>Print Application Slip</span>
                    </button>

                    <a href="{{ route('admissions.status', ['query' => $application->application_no]) }}" class="btn btn-primary px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-search"></i>
                        <span>Track Live Status</span>
                    </a>

                    <a href="{{ url('/') }}" class="btn btn-light border px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-house"></i>
                        <span>Return to Website</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
