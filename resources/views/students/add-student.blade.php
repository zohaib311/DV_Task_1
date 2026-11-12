@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="admission-container">

        <!-- Top Header & Breadcrumb -->
        <div class="admission-header mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboardView') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('allStudents') }}">Students</a></li>
                            <li class="breadcrumb-item active" aria-current="page">New Admission</li>
                        </ol>
                    </nav>
                    <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill text-primary"></i>
                        <span>Student Admission & Registration</span>
                    </h3>
                    <p class="text-muted mb-0 small">
                        Register a new student, choose regular or lateral admission mode, and configure academic placement.
                    </p>
                </div>
                <div>
                    <a href="{{ route('allStudents') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to Students</span>
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">Please correct the following errors before submitting:</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data" data-student-form novalidate>
            @csrf

            <!-- STEP 1: Admission & Academic Entry Mode (Hero Card) -->
            <div class="card admission-card mb-4 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">1</span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Admission & Academic Entry Mode</h5>
                            <small class="text-muted">Select whether the candidate is a regular freshman (Semester 1) or Lateral Entry graduate (Post-ADP 5th Semester).</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- 3 Mode Cards -->
                    <div class="row g-3">
                        <!-- Regular Admission -->
                        <div class="col-md-4">
                            <label class="admission-mode-card" for="admission_regular">
                                <input type="radio" name="admission_type" id="admission_regular" value="regular" class="admission-mode-radio" @checked(old('admission_type', 'regular') === 'regular')>
                                <div class="admission-mode-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="mode-icon"><i class="bi bi-mortarboard"></i></div>
                                        <span class="badge bg-secondary-subtle text-secondary">Semester 1</span>
                                    </div>
                                    <h6 class="fw-bold mb-1">Regular Admission</h6>
                                    <p class="text-muted small mb-0">Standard freshman intake starting their degree from Semester 1.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Lateral Entry (Post-ADP) -->
                        <div class="col-md-4">
                            <label class="admission-mode-card featured" for="admission_lateral_adp">
                                <input type="radio" name="admission_type" id="admission_lateral_adp" value="lateral_adp" class="admission-mode-radio" @checked(old('admission_type') === 'lateral_adp')>
                                <div class="admission-mode-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="mode-icon text-primary"><i class="bi bi-stars"></i></div>
                                        <span class="badge bg-primary-subtle text-primary">BS 5th Semester</span>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-primary">Lateral Entry (Post-ADP)</h6>
                                    <p class="text-muted small mb-0">2-Year ADP graduate with 64.0 transferred credits admitted directly to 5th semester.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Credit Transfer / Migration -->
                        <div class="col-md-4">
                            <label class="admission-mode-card" for="admission_migration">
                                <input type="radio" name="admission_type" id="admission_migration" value="migration" class="admission-mode-radio" @checked(old('admission_type') === 'migration')>
                                <div class="admission-mode-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="mode-icon"><i class="bi bi-arrow-left-right"></i></div>
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Credit Transfer</span>
                                    </div>
                                    <h6 class="fw-bold mb-1">Transfer / Migration</h6>
                                    <p class="text-muted small mb-0">Course-by-course credit exemption transferred from another accredited institute.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Collapsible Lateral Entry & Credit Transfer Fields -->
                    <div class="lateral-details-panel mt-4 {{ old('admission_type', 'regular') === 'regular' ? 'd-none' : '' }}" id="lateral_entry_section">
                        <div class="p-3 mb-3 rounded-3 bg-primary-subtle border border-primary-subtle d-flex align-items-start gap-3">
                            <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                            <div class="small">
                                <strong class="d-block text-primary">HEC Advanced Standing & Credit Exemption Policy</strong>
                                <span>Lateral entry candidates receive block credit exemption (standard 64.0 credit hours). These credits satisfy BS degree completion requirements without affecting university CGPA. Institutional CGPA begins fresh from the admitted semester.</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="previous_institution" class="form-label">
                                    Previous College / University <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <input type="text" name="previous_institution" id="previous_institution" value="{{ old('previous_institution') }}" class="form-control @error('previous_institution') is-invalid @enderror" placeholder="e.g. Govt Post Graduate College / University of Punjab">
                                </div>
                                @error('previous_institution')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="previous_degree" class="form-label">
                                    Previous Qualification / Degree <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-award"></i></span>
                                    <input type="text" name="previous_degree" id="previous_degree" value="{{ old('previous_degree') }}" class="form-control @error('previous_degree') is-invalid @enderror" placeholder="e.g. Associate Degree in Computer Science (ADCS)">
                                </div>
                                @error('previous_degree')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="transferred_credits" class="form-label">
                                    Transferred Credit Hours <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                                    <input type="number" step="0.5" name="transferred_credits" id="transferred_credits" value="{{ old('transferred_credits', $defaultLateralCredits ?? 64.0) }}" class="form-control @error('transferred_credits') is-invalid @enderror">
                                </div>
                                <small class="text-muted">Standard ADP: 60 - 66 credits</small>
                                @error('transferred_credits')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="previous_cgpa" class="form-label">
                                    Prior CGPA / Percentage <small class="text-muted fw-normal">(Optional)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-graph-up"></i></span>
                                    <input type="text" name="previous_cgpa" id="previous_cgpa" value="{{ old('previous_cgpa') }}" class="form-control @error('previous_cgpa') is-invalid @enderror" placeholder="e.g. 3.45 / 4.00 or 74%">
                                </div>
                                @error('previous_cgpa')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="admitted_to_semester" class="form-label">
                                    Admitted to Semester
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <select name="admitted_to_semester" id="admitted_to_semester" class="form-select @error('admitted_to_semester') is-invalid @enderror">
                                        @for($s = 3; $s <= 6; $s++)
                                            <option value="{{ $s }}" @selected(old('admitted_to_semester', $defaultAdmittedSemester ?? 5) == $s)>Semester {{ $s }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="transcript_document" class="form-label">
                                    Equivalence Document / Previous Transcript <small class="text-muted fw-normal">(Optional)</small>
                                </label>
                                <input type="file" name="transcript_document" id="transcript_document" class="form-control @error('transcript_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Supported formats: PDF, PNG, JPG (Max: 5 MB)</small>
                                @error('transcript_document')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="remarks" class="form-label">
                                    Equivalence Committee Order No. / Remarks <small class="text-muted fw-normal">(Optional)</small>
                                </label>
                                <input type="text" name="remarks" id="remarks" value="{{ old('remarks') }}" class="form-control @error('remarks') is-invalid @enderror" placeholder="e.g. Equivalence Committee Ref # EC-2026/08">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Personal & Contact Information -->
            <div class="card admission-card mb-4 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">2</span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Personal & Contact Details</h5>
                            <small class="text-muted">Enter student's personal identity and credentials.</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="user_id" class="form-label">
                                Link to Existing User Account <small class="text-muted fw-normal">(Optional)</small>
                            </label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">Create new profile without linking pre-existing account</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} — {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Selecting an existing user will auto-fill name, email, and phone number.</small>
                            @error('user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Ali Raza" required>
                            </div>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="student@example.com" required>
                            </div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX" required>
                            </div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="cnic" class="form-label">CNIC / B-Form <small class="text-muted fw-normal">(Optional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="cnic" id="cnic" value="{{ old('cnic') }}" class="form-control @error('cnic') is-invalid @enderror" placeholder="35201-1234567-1" maxlength="25">
                            </div>
                            @error('cnic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label for="image" class="form-label">Profile Image <small class="text-muted fw-normal">(Optional)</small></label>
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/png,image/jpeg">
                            <small class="text-muted">JPG or PNG, max 2 MB</small>
                            @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <!-- Student Portal Account Creation Box -->
                        <div class="col-12 mt-3">
                            <div class="portal-account-card p-3 rounded-3 border">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch" name="create_portal_account" value="1" id="create_portal_account" @checked(old('create_portal_account', !old('user_id')))>
                                    <label class="form-check-label fw-bold" for="create_portal_account">
                                        Create Student Portal Login Account
                                    </label>
                                </div>
                                <small class="text-muted d-block mb-3">Allows the student to log in to the student portal immediately with their email address.</small>

                                <div class="row g-3" data-account-passwords>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_password">Portal Password</label>
                                        <input class="form-control @error('account_password') is-invalid @enderror" type="password" name="account_password" id="account_password" autocomplete="new-password" placeholder="At least 8 characters">
                                        @error('account_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_password_confirmation">Confirm Password</label>
                                        <input class="form-control" type="password" name="account_password_confirmation" id="account_password_confirmation" autocomplete="new-password" placeholder="Re-enter password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Previous Academic Record & Merit Scholarship -->
            <div class="card admission-card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">3</span>
                            <div>
                                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                                    <span>Previous Academic Record & Merit Scholarship</span>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-award-fill me-1"></i> Fee Concession</span>
                                </h5>
                                <small class="text-muted">Enter Intermediate/12th grade marks to automatically calculate percentage and award tuition fee scholarship.</small>
                            </div>
                        </div>
                        <div id="scholarship_status_badge">
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 fs-6">No Scholarship Selected</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="inter_degree" class="form-label">Intermediate Degree / Group</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-book"></i></span>
                                <select name="inter_degree" id="inter_degree" class="form-select @error('inter_degree') is-invalid @enderror">
                                    <option value="">Select Qualification</option>
                                    <option value="F.Sc Pre-Engineering" @selected(old('inter_degree') === 'F.Sc Pre-Engineering')>F.Sc Pre-Engineering</option>
                                    <option value="F.Sc Pre-Medical" @selected(old('inter_degree') === 'F.Sc Pre-Medical')>F.Sc Pre-Medical</option>
                                    <option value="ICS (Computer Science)" @selected(old('inter_degree') === 'ICS (Computer Science)')>ICS (Computer Science)</option>
                                    <option value="I.Com (Commerce)" @selected(old('inter_degree') === 'I.Com (Commerce)')>I.Com (Commerce)</option>
                                    <option value="FA (Humanities)" @selected(old('inter_degree') === 'FA (Humanities)')>FA (Humanities)</option>
                                    <option value="DAE (3-Year Diploma)" @selected(old('inter_degree') === 'DAE (3-Year Diploma)')>DAE (3-Year Diploma)</option>
                                    <option value="A-Levels (Cambridge)" @selected(old('inter_degree') === 'A-Levels (Cambridge)')>A-Levels (Cambridge)</option>
                                    <option value="Other Equivalent" @selected(old('inter_degree') === 'Other Equivalent')>Other Equivalent</option>
                                </select>
                            </div>
                            @error('inter_degree')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-5">
                            <label for="inter_board" class="form-label">Examination Board / Institute</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <input type="text" name="inter_board" id="inter_board" value="{{ old('inter_board') }}" class="form-control @error('inter_board') is-invalid @enderror" placeholder="e.g. BISE Lahore / Federal Board Islamabad">
                            </div>
                            @error('inter_board')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="inter_roll_no" class="form-label">Inter Roll Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="text" name="inter_roll_no" id="inter_roll_no" value="{{ old('inter_roll_no') }}" class="form-control @error('inter_roll_no') is-invalid @enderror" placeholder="e.g. 543219">
                            </div>
                            @error('inter_roll_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="inter_total_marks" class="form-label">Total Marks</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="number" step="1" min="1" max="5000" name="inter_total_marks" id="inter_total_marks" value="{{ old('inter_total_marks', 1100) }}" class="form-control @error('inter_total_marks') is-invalid @enderror" placeholder="1100">
                            </div>
                            @error('inter_total_marks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="inter_obtained_marks" class="form-label">Obtained Marks</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                                <input type="number" step="1" min="0" max="5000" name="inter_obtained_marks" id="inter_obtained_marks" value="{{ old('inter_obtained_marks') }}" class="form-control @error('inter_obtained_marks') is-invalid @enderror" placeholder="e.g. 935">
                            </div>
                            @error('inter_obtained_marks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="inter_percentage" class="form-label">Result Percentage</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold">%</span>
                                <input type="number" step="0.01" min="0" max="100" name="inter_percentage" id="inter_percentage" value="{{ old('inter_percentage') }}" class="form-control fw-bold bg-white @error('inter_percentage') is-invalid @enderror" placeholder="0.00" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="btn_toggle_manual_pct" title="Unlock to enter manually"><i class="bi bi-pencil"></i></button>
                            </div>
                            <small class="text-muted">Auto-computed from obtained / total marks.</small>
                            @error('inter_percentage')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-7">
                            <label for="scholarship_select" class="form-label fw-semibold">
                                Offered Scholarship / Fee Concession
                                <span class="badge bg-primary-subtle text-primary ms-1">Auto-Suggests on %</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-gift-fill text-warning"></i></span>
                                <select id="scholarship_select" class="form-select fw-medium">
                                    <option value="0" data-name="" data-pct="0">Standard Intake (No Scholarship — 0% Discount)</option>
                                    <optgroup label="🌟 Merit Scholarship Slabs (Inter Marks Based)">
                                        <option value="50" data-name="Merit Scholarship (Gold - 50% Tuition Waiver)" data-pct="50" data-min="85">
                                            Merit Scholarship Gold (50% Tuition Waiver) [≥ 85% Marks]
                                        </option>
                                        <option value="30" data-name="Merit Scholarship (Silver - 30% Tuition Waiver)" data-pct="30" data-min="75">
                                            Merit Scholarship Silver (30% Tuition Waiver) [75% – 84.9% Marks]
                                        </option>
                                        <option value="15" data-name="Merit Scholarship (Bronze - 15% Tuition Waiver)" data-pct="15" data-min="65">
                                            Merit Scholarship Bronze (15% Tuition Waiver) [65% – 74.9% Marks]
                                        </option>
                                    </optgroup>
                                    <optgroup label="🤝 Special Quotas & Financial Aid">
                                        <option value="50" data-name="Need-Based Financial Aid (50% Tuition Waiver)" data-pct="50">
                                            Need-Based Financial Aid (50% Tuition Waiver)
                                        </option>
                                        <option value="25" data-name="Need-Based Financial Aid (25% Tuition Waiver)" data-pct="25">
                                            Need-Based Financial Aid (25% Tuition Waiver)
                                        </option>
                                        <option value="20" data-name="Kinship / Sibling Concession (20% Tuition Waiver)" data-pct="20">
                                            Kinship / Sibling Concession (20% Tuition Waiver)
                                        </option>
                                        <option value="30" data-name="Sports & Talent Quota (30% Tuition Waiver)" data-pct="30">
                                            Sports & Talent Quota (30% Tuition Waiver)
                                        </option>
                                        <option value="50" data-name="Faculty & Staff Ward Concession (50% Tuition Waiver)" data-pct="50">
                                            Faculty & Staff Ward Concession (50% Tuition Waiver)
                                        </option>
                                    </optgroup>
                                </select>
                            </div>
                            <!-- Hidden inputs for backend binding -->
                            <input type="hidden" name="scholarship_name" id="scholarship_name" value="{{ old('scholarship_name') }}">
                            <input type="hidden" name="scholarship_percentage" id="scholarship_percentage" value="{{ old('scholarship_percentage', 0) }}">
                            <input type="hidden" name="scholarship_status" id="scholarship_status" value="{{ old('scholarship_status', 'none') }}">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tuition Discount Effect</label>
                            <div id="scholarship_summary_card" class="p-2 px-3 rounded-2 border bg-white d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill text-muted fs-4" id="scholarship_icon"></i>
                                <div>
                                    <div class="fw-bold small" id="scholarship_summary_title">No concession applied</div>
                                    <small class="text-muted" id="scholarship_summary_desc">Standard tuition fee rates will apply to semester invoices.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Academic Placement -->
            <div class="card admission-card mb-4 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">4</span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Academic Placement</h5>
                            <small class="text-muted">Assign department, academic degree program, and initial section.</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="program_id" class="form-label">Degree Program <span class="text-danger">*</span></label>
                            <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                <option value="">Select department first</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" data-department-id="{{ $program->department_id }}" @selected(old('program_id') == $program->id)>
                                        {{ $program->code }} — {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="section_id" class="form-label">Class Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
                                <option value="">Select department first</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }} ({{ $section->department->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-2">
                            <div class="alert alert-secondary py-2 small mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-right-circle text-primary fs-5"></i>
                                <span>A unique registration number will be automatically generated. After saving, you will immediately proceed to course offering and semester enrollment.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Submit / Action Bar -->
            <div class="admission-footer-bar p-3 bg-white rounded-3 shadow-sm border d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5">
                <a href="{{ route('allStudents') }}" class="btn btn-outline-secondary px-4 py-2">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-check2-circle fs-5"></i>
                    <span class="fw-bold">Create Student & Proceed to Enrollment</span>
                </button>
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/student/department-section-filter.js') }}"></script>
    <script src="{{ asset('js/student/student-account-flow.js') }}"></script>
    <script>
        document.querySelectorAll('input[name="admission_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const sec = document.getElementById('lateral_entry_section');
                if (!sec) return;
                if (e.target.value === 'lateral_adp' || e.target.value === 'migration') {
                    sec.classList.remove('d-none');
                } else {
                    sec.classList.add('d-none');
                }
            });
        });

        // Inter Marks & Scholarship Automation
        const totalMarksInput = document.getElementById('inter_total_marks');
        const obtainedMarksInput = document.getElementById('inter_obtained_marks');
        const percentageInput = document.getElementById('inter_percentage');
        const toggleManualBtn = document.getElementById('btn_toggle_manual_pct');
        const scholarshipSelect = document.getElementById('scholarship_select');
        const scholarshipNameHidden = document.getElementById('scholarship_name');
        const scholarshipPctHidden = document.getElementById('scholarship_percentage');
        const scholarshipStatusHidden = document.getElementById('scholarship_status');
        const scholarshipStatusBadge = document.getElementById('scholarship_status_badge');
        const summaryCard = document.getElementById('scholarship_summary_card');
        const summaryTitle = document.getElementById('scholarship_summary_title');
        const summaryDesc = document.getElementById('scholarship_summary_desc');
        const summaryIcon = document.getElementById('scholarship_icon');

        let isManualPct = false;
        let userManuallyChangedScholarship = false;

        if (toggleManualBtn) {
            toggleManualBtn.addEventListener('click', () => {
                isManualPct = !isManualPct;
                percentageInput.readOnly = !isManualPct;
                toggleManualBtn.classList.toggle('btn-primary', isManualPct);
                toggleManualBtn.classList.toggle('btn-outline-secondary', !isManualPct);
                if (isManualPct) percentageInput.focus();
            });
        }

        function calculatePercentageAndScholarship() {
            const total = parseFloat(totalMarksInput.value) || 0;
            const obtained = parseFloat(obtainedMarksInput.value);

            if (!isManualPct && total > 0 && !isNaN(obtained)) {
                const pct = Math.min(100, Math.max(0, (obtained / total) * 100));
                percentageInput.value = pct.toFixed(2);
                if (!userManuallyChangedScholarship) {
                    autoSelectScholarship(pct);
                }
            } else if (!isManualPct && isNaN(obtained)) {
                percentageInput.value = '';
                if (!userManuallyChangedScholarship) {
                    resetScholarship();
                }
            }
            updateSummaryUI();
        }

        function autoSelectScholarship(pct) {
            if (pct >= 85) {
                setSelectByValue(50, 'Merit Scholarship (Gold - 50% Tuition Waiver)');
            } else if (pct >= 75) {
                setSelectByValue(30, 'Merit Scholarship (Silver - 30% Tuition Waiver)');
            } else if (pct >= 65) {
                setSelectByValue(15, 'Merit Scholarship (Bronze - 15% Tuition Waiver)');
            } else {
                resetScholarship();
            }
        }

        function setSelectByValue(pct, name) {
            for (let option of scholarshipSelect.options) {
                if (parseFloat(option.value) === pct && (option.dataset.name === name || !option.dataset.name)) {
                    scholarshipSelect.value = option.value;
                    scholarshipNameHidden.value = option.dataset.name || name;
                    scholarshipPctHidden.value = pct;
                    scholarshipStatusHidden.value = 'active';
                    break;
                }
            }
        }

        function resetScholarship() {
            scholarshipSelect.value = '0';
            scholarshipNameHidden.value = '';
            scholarshipPctHidden.value = '0';
            scholarshipStatusHidden.value = 'none';
        }

        function updateSummaryUI() {
            const selectedOpt = scholarshipSelect.options[scholarshipSelect.selectedIndex];
            const pct = parseFloat(selectedOpt?.value) || 0;
            const name = selectedOpt?.dataset.name || (pct > 0 ? selectedOpt.text : '');

            scholarshipPctHidden.value = pct;
            scholarshipNameHidden.value = name;
            scholarshipStatusHidden.value = pct > 0 ? 'active' : 'none';

            if (pct > 0) {
                scholarshipStatusBadge.innerHTML = `<span class="badge bg-success px-3 py-2 fs-6"><i class="bi bi-check-circle-fill me-1"></i> ${pct}% Tuition Waiver Active</span>`;
                summaryTitle.textContent = name;
                summaryTitle.className = 'fw-bold small text-success';
                summaryDesc.textContent = `${pct}% will be automatically deducted from Tuition Fee in every Semester Fee Invoice.`;
                summaryIcon.className = 'bi bi-award-fill text-success fs-3';
                summaryCard.className = 'p-2 px-3 rounded-2 border border-success bg-success-subtle d-flex align-items-center gap-2';
            } else {
                scholarshipStatusBadge.innerHTML = `<span class="badge bg-secondary-subtle text-secondary px-3 py-2 fs-6">No Scholarship Selected</span>`;
                summaryTitle.textContent = 'Standard Intake';
                summaryTitle.className = 'fw-bold small text-dark';
                summaryDesc.textContent = 'Standard tuition fee rates will apply to semester invoices.';
                summaryIcon.className = 'bi bi-info-circle-fill text-muted fs-4';
                summaryCard.className = 'p-2 px-3 rounded-2 border bg-white d-flex align-items-center gap-2';
            }
        }

        if (totalMarksInput && obtainedMarksInput) {
            totalMarksInput.addEventListener('input', calculatePercentageAndScholarship);
            obtainedMarksInput.addEventListener('input', calculatePercentageAndScholarship);
        }

        if (percentageInput) {
            percentageInput.addEventListener('input', () => {
                const pct = parseFloat(percentageInput.value);
                if (!isNaN(pct) && !userManuallyChangedScholarship) {
                    autoSelectScholarship(pct);
                }
                updateSummaryUI();
            });
        }

        if (scholarshipSelect) {
            scholarshipSelect.addEventListener('change', () => {
                userManuallyChangedScholarship = true;
                updateSummaryUI();
            });
        }

        if (obtainedMarksInput && obtainedMarksInput.value) {
            calculatePercentageAndScholarship();
        }
    </script>
@endsection
