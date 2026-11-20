@extends('website.layout')

@section('title', 'Online Admission Application - Fall 2026 | My Form Task University')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
    <!-- Admissions Hero Header -->
    <section class="admissions-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #2563eb 100%); color: #ffffff; padding: 60px 0 45px;">
        <div class="container text-center">
            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold mb-2">
                <i class="bi bi-mortarboard-fill me-1"></i> Admissions Open · Fall 2026-2027
            </span>
            <h1 class="display-5 fw-extrabold text-white mb-2">Official Online Admission Portal</h1>
            <p class="lead text-light text-opacity-90 max-w-2xl mx-auto mb-4" style="max-width: 680px;">
                Complete your application in a few straightforward steps. Our Admissions Committee reviews applications on a rolling basis.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('admissions.status') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-search"></i>
                    <span>Track Submitted Application</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Application Form Container -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                @if($errors->any())
                    <div class="alert alert-danger shadow-sm rounded-3 mb-4" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the highlighted errors before submitting:</div>
                        <ul class="mb-0 small ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="admissions-card">
                    <div class="admissions-card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="fw-bold text-dark mb-0">Undergraduate &amp; Transfer Admission Form</h4>
                                <small class="text-muted">Academic Session: <strong>Fall 2026 - Spring 2027</strong></small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">
                                HEC Accredited Programs
                            </span>
                        </div>
                        @auth
                            <div class="mt-3 p-2 px-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                                <div>
                                    <i class="bi bi-person-check-fill text-primary me-1"></i>
                                    Applying as: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                                </div>
                                <a href="{{ route('admissions.applicant.dashboard') }}" class="text-primary fw-bold text-decoration-none">
                                    <i class="bi bi-speedometer2 me-1"></i> My Applicant Dashboard
                                </a>
                            </div>
                        @endauth
                    </div>

                    <form action="{{ route('admissions.apply.submit') }}" method="POST" enctype="multipart/form-data" id="admissionForm">
                        @csrf

                        <!-- SECTION 1: Personal & Contact Details -->
                        <div class="admissions-form-section">
                            <h5 class="admissions-section-title">
                                <i class="bi bi-person-badge-fill"></i>
                                <span>1. Personal &amp; Contact Information</span>
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Applicant Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                        <input type="text" name="name" class="form-control" placeholder="e.g. Muhammad Ali Khan" required value="{{ old('name', auth()->user()?->name) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Father / Guardian Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person-heart"></i></span>
                                        <input type="text" name="father_name" class="form-control" placeholder="e.g. Tariq Mehmood Khan" required value="{{ old('father_name') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" placeholder="applicant@example.com" required value="{{ old('email', auth()->user()?->email) }}">
                                    </div>
                                    <div class="form-text small">Your application tracking receipt and admission offer will be sent here.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Phone Number / WhatsApp <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" class="form-control" placeholder="03001234567" required value="{{ old('phone', auth()->user()?->phone) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">CNIC / B-Form Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                        <input type="text" name="cnic" class="form-control font-monospace" placeholder="35202-1234567-1" value="{{ old('cnic') }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-secondary">Residential Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="House #, Street, Colony / Area" value="{{ old('address') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">City</label>
                                    <input type="text" name="city" class="form-control" placeholder="e.g. Lahore, Islamabad, Karachi" value="{{ old('city') }}">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: Academic Program Choice -->
                        <div class="admissions-form-section">
                            <h5 class="admissions-section-title">
                                <i class="bi bi-mortarboard-fill"></i>
                                <span>2. Desired Program &amp; Admission Pathway</span>
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Admission Pathway <span class="text-danger">*</span></label>
                                    <select name="admission_type" id="admission_type" class="form-select" required>
                                        <option value="regular" {{ old('admission_type', 'regular') === 'regular' ? 'selected' : '' }}>
                                            Regular Intake (Semester 1 Entry)
                                        </option>
                                        <option value="lateral_adp" {{ old('admission_type') === 'lateral_adp' ? 'selected' : '' }}>
                                            ADP Lateral Entry (5th Semester Direct Transfer)
                                        </option>
                                        <option value="migration" {{ old('admission_type') === 'migration' ? 'selected' : '' }}>
                                            University Migration / Credit Transfer
                                        </option>
                                    </select>
                                    <div class="form-text small" id="admission_type_hint">
                                        For students completing 12 years of education (FSc, ICS, A-Levels).
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Academic Faculty / Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="department_id" class="form-select" required>
                                        <option value="">-- Choose Department --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Degree Program <span class="text-danger">*</span></label>
                                    <select name="program_id" id="program_id" class="form-select" required>
                                        <option value="">-- Choose Program --</option>
                                        @foreach($allPrograms as $prog)
                                            <option value="{{ $prog->id }}" data-department="{{ $prog->department_id }}"
                                                {{ (old('program_id') == $prog->id || $selectedProgramId == $prog->id) ? 'selected' : '' }}>
                                                {{ $prog->name }} ({{ $prog->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: Prior Academic Qualification -->
                        <div class="admissions-form-section">
                            <h5 class="admissions-section-title">
                                <i class="bi bi-book-half"></i>
                                <span>3. Prior Qualification (Intermediate / HSSC / DAE)</span>
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Certificate / Degree Title</label>
                                    <input type="text" name="inter_degree" class="form-control" placeholder="e.g. FSc Pre-Engineering, ICS, DAE" value="{{ old('inter_degree') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Board of Intermediate / Technical Education</label>
                                    <input type="text" name="inter_board" class="form-control" placeholder="e.g. BISE Lahore, Federal Board" value="{{ old('inter_board') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Exam Roll Number</label>
                                    <input type="text" name="inter_roll_no" class="form-control font-monospace" placeholder="e.g. 109284" value="{{ old('inter_roll_no') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Total Marks</label>
                                    <input type="number" id="inter_total_marks" name="inter_total_marks" class="form-control" placeholder="e.g. 1100" value="{{ old('inter_total_marks', 1100) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Obtained Marks</label>
                                    <input type="number" id="inter_obtained_marks" name="inter_obtained_marks" class="form-control" placeholder="e.g. 920" value="{{ old('inter_obtained_marks') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Computed Percentage (%)</label>
                                    <div class="input-group">
                                        <input type="text" id="inter_percentage_display" class="form-control fw-bold text-primary" placeholder="e.g. 83.64%" readonly>
                                        <span class="input-group-text bg-light fw-bold">%</span>
                                    </div>
                                    <div class="form-text small">Automatically computed from marks.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: ADP Lateral Transfer Section (Conditionally Visible) -->
                        <div class="admissions-form-section" id="lateral_entry_section" style="display: none; background: #fffdf5;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning text-dark fs-6 px-3 py-1.5 rounded-pill">
                                    <i class="bi bi-award-fill me-1"></i> ADP 5th Semester Lateral Transfer Track
                                </span>
                            </div>

                            <div class="p-3 mb-3 rounded-3 bg-white border border-warning-subtle small text-muted">
                                <i class="bi bi-info-circle-fill text-warning me-1"></i>
                                Applicants with a completed 2-year Associate Degree (ADP / BSc) can enter directly into the 5th semester. Transferred credits: <strong>{{ number_format((float) $defaultLateralCredits, 1) }} Credit Hours</strong>.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Previous College / University <span class="text-danger">*</span></label>
                                    <input type="text" name="previous_institution" id="previous_institution" class="form-control" placeholder="e.g. Punjab University Affiliated College" value="{{ old('previous_institution') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Completed 2-Year Associate Degree <span class="text-danger">*</span></label>
                                    <input type="text" name="previous_degree" id="previous_degree" class="form-control" placeholder="e.g. ADP in Computer Science (2 Years)" value="{{ old('previous_degree') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Previous CGPA / Marks</label>
                                    <input type="text" name="previous_cgpa" class="form-control" placeholder="e.g. 3.45 / 4.00" value="{{ old('previous_cgpa') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Standard Transferred Credit Hours</label>
                                    <input type="number" step="0.5" name="transferred_credits" class="form-control" value="{{ old('transferred_credits', $defaultLateralCredits) }}">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: Scholarship Application & Attachments -->
                        <div class="admissions-form-section">
                            <h5 class="admissions-section-title">
                                <i class="bi bi-gift-fill"></i>
                                <span>4. Scholarships &amp; Supporting Documents</span>
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Apply for Scholarship / Tuition Waiver</label>
                                    <select name="scholarship_name" class="form-select">
                                        <option value="">-- No Scholarship Requested (Self Finance) --</option>
                                        <option value="Merit Waiver" {{ old('scholarship_name') === 'Merit Waiver' ? 'selected' : '' }}>
                                            Merit Scholarship (Up to 100% Tuition Waiver)
                                        </option>
                                        <option value="Need-based Assistance" {{ old('scholarship_name') === 'Need-based Assistance' ? 'selected' : '' }}>
                                            Need-Based Financial Assistance
                                        </option>
                                        <option value="Kinship Concession" {{ old('scholarship_name') === 'Kinship Concession' ? 'selected' : '' }}>
                                            Kinship Concession (Sibling attending MFTU)
                                        </option>
                                    </select>
                                    <div class="form-text small">Subject to verification of marks and university quota.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Upload Marksheet / Result Card (Optional)</label>
                                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text small">PDF, JPG, or PNG up to 5MB. Original documents will be verified at interview.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-secondary">Additional Remarks / Statement of Purpose (Optional)</label>
                                    <textarea name="applicant_remarks" class="form-control" rows="3" placeholder="Tell the admissions committee about your academic interests, extracurricular achievements, or specific requirements...">{{ old('applicant_remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-4 bg-light d-flex flex-wrap align-items-center justify-content-between gap-3 border-top">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock-fill text-success fs-4"></i>
                                <small class="text-muted">
                                    Your information is kept confidential and processed strictly according to HEC admission policies.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                                <i class="bi bi-send-fill"></i>
                                <span>Submit Official Application</span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const admTypeSelect = document.getElementById('admission_type');
    const lateralSection = document.getElementById('lateral_entry_section');
    const admTypeHint = document.getElementById('admission_type_hint');
    const prevInst = document.getElementById('previous_institution');
    const prevDegree = document.getElementById('previous_degree');

    function toggleLateral() {
        const val = admTypeSelect.value;
        if (val === 'lateral_adp' || val === 'migration') {
            lateralSection.style.display = 'block';
            prevInst.setAttribute('required', 'required');
            prevDegree.setAttribute('required', 'required');
            admTypeHint.innerHTML = 'Direct 5th Semester admission with 64 transferred credit hours.';
        } else {
            lateralSection.style.display = 'none';
            prevInst.removeAttribute('required');
            prevDegree.removeAttribute('required');
            admTypeHint.innerHTML = 'For students completing 12 years of education (FSc, ICS, A-Levels).';
        }
    }

    admTypeSelect.addEventListener('change', toggleLateral);
    toggleLateral();

    // Marks computation
    const totalInput = document.getElementById('inter_total_marks');
    const obtInput = document.getElementById('inter_obtained_marks');
    const pctDisplay = document.getElementById('inter_percentage_display');

    function computePercentage() {
        const tot = parseFloat(totalInput.value);
        const obt = parseFloat(obtInput.value);
        if (!isNaN(tot) && !isNaN(obt) && tot > 0) {
            const pct = ((obt / tot) * 100).toFixed(2);
            pctDisplay.value = pct + '%';
        } else {
            pctDisplay.value = '';
        }
    }

    totalInput.addEventListener('input', computePercentage);
    obtInput.addEventListener('input', computePercentage);
    computePercentage();

    // Department to Program cascading filter
    const deptSelect = document.getElementById('department_id');
    const progSelect = document.getElementById('program_id');
    const progOptions = Array.from(progSelect.querySelectorAll('option[data-department]'));

    deptSelect.addEventListener('change', function () {
        const selectedDeptId = this.value;
        progOptions.forEach(opt => {
            if (!selectedDeptId || opt.dataset.department === selectedDeptId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
                if (opt.selected) opt.selected = false;
            }
        });
    });

    if (deptSelect.value) {
        deptSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
