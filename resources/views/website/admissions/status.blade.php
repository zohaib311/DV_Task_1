@extends('website.layout')

@section('title', 'Track Admission Application Status | My Form Task University')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
    <div class="admissions-hero py-5 text-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #2563eb 100%); color: #ffffff; padding: 60px 0 45px;">
        <div class="container">
            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">
                <i class="bi bi-search me-1"></i> Live Application Tracking
            </span>
            <h2 class="display-6 fw-extrabold text-white mb-2">Check Your Admission Application Status</h2>
            <p class="text-light text-opacity-90 max-w-xl mx-auto mb-4" style="max-width: 600px;">
                Enter your Application Tracking Number (e.g. <code>APP-2026-0001</code>), Email, or CNIC to check the current review stage.
            </p>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form action="{{ route('admissions.status') }}" method="GET">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden bg-white p-1">
                            <span class="input-group-text bg-white border-0 ps-3 text-primary"><i class="bi bi-search fs-5"></i></span>
                            <input type="text" name="query" class="form-control border-0 shadow-none" placeholder="Enter Application No, Email, or CNIC..." value="{{ $query ?? '' }}" required>
                            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @if($application)
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header py-3 px-4 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Application Record</small>
                                <span class="fs-5 fw-bold text-dark font-monospace">{{ $application->application_no }}</span>
                            </div>
                            <span class="badge {{ $application->getStatusBadgeClass() }} px-3 py-2 rounded-pill fs-6">
                                {{ $application->getStatusLabel() }}
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Applicant Summary -->
                            <div class="row g-3 pb-3 mb-4 border-bottom">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Applicant Name</small>
                                    <strong class="text-dark">{{ $application->name }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Applied Program</small>
                                    <strong class="text-primary">{{ $application->program->name ?? 'General Program' }}</strong>
                                    <small class="text-muted d-block">{{ $application->department->name ?? '' }}</small>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Submission Date</small>
                                    <span>{{ $application->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Admission Type</small>
                                    <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $application->admission_type)) }}</span>
                                </div>
                            </div>

                            <!-- Visual Status Progress Timeline -->
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Application Milestone Progress</h6>

                            <div class="application-status-timeline mb-4">
                                <!-- Step 1 -->
                                <div class="timeline-item done">
                                    <div class="timeline-dot"></div>
                                    <strong class="d-block text-dark small">1. Application Received</strong>
                                    <small class="text-muted">Your online submission was recorded successfully on {{ $application->created_at->format('d M Y') }}.</small>
                                </div>

                                <!-- Step 2 -->
                                <div class="timeline-item {{ in_array($application->status, ['under_review', 'approved', 'enrolled']) ? 'done' : 'active' }}">
                                    <div class="timeline-dot"></div>
                                    <strong class="d-block text-dark small">2. Scrutiny &amp; Verification</strong>
                                    <small class="text-muted">
                                        @if($application->status === 'pending')
                                            In queue for document verification.
                                        @else
                                            Document marks and eligibility verified by admissions officer.
                                        @endif
                                    </small>
                                </div>

                                <!-- Step 3 -->
                                <div class="timeline-item {{ in_array($application->status, ['approved', 'enrolled']) ? 'done' : ($application->status === 'under_review' ? 'active' : '') }}">
                                    <div class="timeline-dot"></div>
                                    <strong class="d-block text-dark small">3. Merit Approval &amp; Shortlist</strong>
                                    <small class="text-muted">
                                        @if(in_array($application->status, ['approved', 'enrolled']))
                                            Candidate qualified for admission. Offer confirmed!
                                        @else
                                            Pending merit list release.
                                        @endif
                                    </small>
                                </div>

                                <!-- Step 4 -->
                                <div class="timeline-item {{ $application->isEnrolled() ? 'done' : '' }}">
                                    <div class="timeline-dot"></div>
                                    <strong class="d-block text-dark small">4. University Enrollment &amp; Portal Account</strong>
                                    <small class="text-muted">
                                        @if($application->isEnrolled())
                                            <span class="text-success fw-bold">Officially Enrolled! Student Registration Number: {{ $application->enrolledStudent->registration_no }}</span>
                                        @else
                                            Issued upon final fee submission and document sign-off.
                                        @endif
                                    </small>
                                </div>
                            </div>

                            @if($application->isEnrolled())
                                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div>
                                        <strong class="text-success d-block"><i class="bi bi-mortarboard-fill me-1"></i> Welcome to My Form Task University!</strong>
                                        <small class="text-dark">Your student profile is active. You can now login to your Student Portal.</small>
                                    </div>
                                    <a href="{{ route('login') }}" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Student Portal Login
                                    </a>
                                </div>
                            @elseif($application->admin_notes)
                                <div class="p-3 rounded-3 bg-light border">
                                    <small class="text-muted d-block fw-bold mb-1">Admissions Office Remarks:</small>
                                    <span class="small text-dark">{{ $application->admin_notes }}</span>
                                </div>
                            @endif

                        </div>
                    </div>
                @elseif($query)
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
                        <i class="bi bi-search text-muted opacity-50 display-3 mb-3"></i>
                        <h5 class="fw-bold text-dark">No Application Found</h5>
                        <p class="text-muted small mb-3">We could not locate any application matching "<strong>{{ $query }}</strong>". Please double check your Application Number or Email.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('admissions.apply') }}" class="btn btn-primary btn-sm rounded-3">Submit a New Application</a>
                            <a href="{{ url('/') }}" class="btn btn-light border btn-sm rounded-3">Back to Website</a>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white mb-4">
                        <i class="bi bi-info-circle text-primary display-4 mb-3"></i>
                        <h5 class="fw-bold text-dark">Ready to Track Your Admission?</h5>
                        <p class="text-muted small mb-0">Enter your Application Number (e.g. <code>APP-2026-0001</code>) or your registered Email in the search bar above to see real-time updates.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
