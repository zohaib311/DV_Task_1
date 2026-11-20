@extends('website.layout')

@section('title', 'Applicant Portal Login & Registration | My Form Task University')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
<!-- Hero Strip -->
<section class="admissions-hero py-4">
    <div class="container text-center">
        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">
            <i class="bi bi-mortarboard-fill me-1"></i> Admissions 2026-2027
        </span>
        <h1 class="h2 fw-bold text-white mb-1">Online Admission &amp; Applicant Portal</h1>
        <p class="text-white-50 mb-0 small">
            Sign in to submit your admission form, track your document verification, or check your merit status.
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <!-- Alerts -->
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Form Errors:</div>
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Auth Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-fill bg-light border-bottom" id="applicantAuthTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-3 text-dark {{ !old('is_register') && !$errors->has('name') ? 'active' : '' }}" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab" aria-selected="{{ !old('is_register') && !$errors->has('name') ? 'true' : 'false' }}">
                            <i class="bi bi-box-arrow-in-right me-1 text-primary"></i> Sign In to Apply
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-3 text-dark {{ old('is_register') || $errors->has('name') ? 'active' : '' }}" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab" aria-selected="{{ old('is_register') || $errors->has('name') ? 'true' : 'false' }}">
                            <i class="bi bi-person-plus-fill me-1 text-success"></i> New Applicant Register
                        </button>
                    </li>
                </ul>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content" id="applicantAuthContent">

                        <!-- TAB 1: SIGN IN -->
                        <div class="tab-pane fade {{ !old('is_register') && !$errors->has('name') ? 'show active' : '' }}" id="login-pane" role="tabpanel" aria-labelledby="login-tab">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark">Welcome Back</h4>
                                <p class="text-muted small">Enter your email and password to access your application or start a new form.</p>
                            </div>

                            <form action="{{ route('admissions.login.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0" placeholder="your.email@example.com" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm mb-3">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to My Application
                                </button>
                            </form>

                            <div class="text-center text-muted small">
                                Don't have an applicant account? 
                                <a href="javascript:void(0)" onclick="bootstrap.Tab.getInstance(document.getElementById('register-tab'))?.show() || new bootstrap.Tab(document.getElementById('register-tab')).show();" class="text-primary fw-semibold text-decoration-none">Create one now</a>
                            </div>
                        </div>

                        <!-- TAB 2: REGISTER -->
                        <div class="tab-pane fade {{ old('is_register') || $errors->has('name') ? 'show active' : '' }}" id="register-pane" role="tabpanel" aria-labelledby="register-tab">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark">Create Applicant Account</h4>
                                <p class="text-muted small">Sign up in seconds to submit your application and track your merit progress.</p>
                            </div>

                            <form action="{{ route('admissions.register.submit') }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_register" value="1">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                        <input type="text" name="name" value="{{ old('name') }}" class="form-control border-start-0" placeholder="Muhammad Ali" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0" placeholder="ali@example.com" required>
                                    </div>
                                    <div class="form-text small">Your admission updates &amp; portal access will be sent to this email.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Mobile Number / WhatsApp <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control border-start-0" placeholder="03001234567" required>
                                    </div>
                                </div>

                                <div class="row g-2 mb-4">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                            <input type="password" name="password" class="form-control border-start-0" placeholder="Min. 4 chars" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                            <input type="password" name="password_confirmation" class="form-control border-start-0" placeholder="Repeat password" required>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm mb-3">
                                    <i class="bi bi-person-check me-1"></i> Create Account &amp; Proceed to Apply
                                </button>
                            </form>

                            <div class="text-center text-muted small">
                                Already registered? 
                                <a href="javascript:void(0)" onclick="bootstrap.Tab.getInstance(document.getElementById('login-tab'))?.show() || new bootstrap.Tab(document.getElementById('login-tab')).show();" class="text-primary fw-semibold text-decoration-none">Sign in here</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Helpful Notice Box -->
            <div class="mt-4 p-3 bg-light rounded-4 border text-center text-muted small">
                <i class="bi bi-shield-lock-fill text-primary me-1"></i>
                Already an officially enrolled student? 
                <a href="{{ route('login') }}" class="text-primary fw-semibold">Go to Student Portal Login</a>
            </div>

        </div>
    </div>
</div>
@endsection
