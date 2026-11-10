@props([
    'departments' => [],
    'programs' => [],
    'sections' => [],
    'users' => [],
    'defaultLateralCredits' => 64.0,
    'defaultAdmittedSemester' => 5,
])

<div class="offcanvas offcanvas-end add-student-drawer" tabindex="-1" id="addStudentDrawer"
    aria-labelledby="addStudentDrawerLabel">
    <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data"
        class="d-flex flex-column h-100 m-0" novalidate data-student-form>
        @csrf

        <div class="drawer-header">
            <div class="drawer-title-wrap">
                <span class="drawer-title-icon"><i class="bi bi-person-plus-fill"></i></span>
                <div>
                    <h5 id="addStudentDrawerLabel">Add New Student</h5>
                    <p>Create a student profile, then enroll them in a semester.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="drawer-body">
            @if ($errors->any())
                <div class="student-validation-summary" role="alert" aria-live="assertive">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Please correct the following errors:</strong>
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="student-drawer-note">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="d-flex flex-column gap-1">
                        <span>A registration number will be created automatically. Semester and courses are assigned from Enrollments.</span>
                        <a href="{{ route('addStudentForm') }}" class="fw-semibold text-primary text-decoration-underline small">Prefer full-page admission form? Click here <i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </div>
            @endif

            <section class="student-form-section">
                <div class="student-form-section-title">
                    <span><i class="bi bi-person-vcard"></i></span>
                    <div>
                        <h6>Personal details</h6>
                        <p>Basic information for the student profile.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="user_id" class="form-label">Login account <span class="form-label-hint">Optional</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Create profile without portal access</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <small class="student-field-hint">Link the student to an existing User account for the future Student Portal.</small>
                        @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Full name</label>
                        <div class="student-input-wrap">
                            <i class="bi bi-person"></i>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Ali Raza" required>
                        </div>
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email address</label>
                        <div class="student-input-wrap">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="student@email.com" required>
                        </div>
                        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone number</label>
                        <div class="student-input-wrap">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX" required>
                        </div>
                        @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="image" class="form-label">Profile image <span class="form-label-hint">Optional</span></label>
                        <div class="student-input-wrap">
                            <i class="bi bi-image"></i>
                            <input type="file" name="image" id="image" accept="image/png,image/jpeg"
                                class="form-control @error('image') is-invalid @enderror">
                        </div>
                        <small class="student-field-hint">JPG or PNG, max 2 MB</small>
                        @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <div class="student-drawer-note">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="create_portal_account" value="1" id="create_portal_account" @checked(old('create_portal_account', !old('user_id')))><label class="form-check-label fw-semibold" for="create_portal_account">Create Student Portal account</label></div>
                            <small>The student can log in immediately after enrollment.</small>
                            <div class="row g-2 mt-1" data-account-passwords><div class="col-md-6"><label class="form-label" for="account_password">Password</label><input class="form-control" type="password" name="account_password" id="account_password" autocomplete="new-password"></div><div class="col-md-6"><label class="form-label" for="account_password_confirmation">Confirm password</label><input class="form-control" type="password" name="account_password_confirmation" id="account_password_confirmation" autocomplete="new-password"></div></div>
                            @error('account_password') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <!-- Admission Type & Entry Mode Section -->
            <section class="student-form-section">
                <div class="student-form-section-title">
                    <span><i class="bi bi-mortarboard-fill"></i></span>
                    <div>
                        <h6>Admission & Entry Mode</h6>
                        <p>Regular entry (Semester 1) or Lateral entry (Post-ADP 5th Semester).</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input drawer-admission-radio" type="radio" name="admission_type" id="drawer_admission_regular" value="regular" @checked(old('admission_type', 'regular') === 'regular')>
                                <label class="form-check-label small fw-semibold" for="drawer_admission_regular">
                                    Regular Entry (Sem 1)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input drawer-admission-radio" type="radio" name="admission_type" id="drawer_admission_lateral_adp" value="lateral_adp" @checked(old('admission_type') === 'lateral_adp')>
                                <label class="form-check-label small fw-semibold text-primary" for="drawer_admission_lateral_adp">
                                    Lateral Entry (Post-ADP 5th Sem)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input drawer-admission-radio" type="radio" name="admission_type" id="drawer_admission_migration" value="migration" @checked(old('admission_type') === 'migration')>
                                <label class="form-check-label small fw-semibold" for="drawer_admission_migration">
                                    Credit Transfer
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Lateral Entry / Credit Transfer Fields -->
                    <div class="col-12 {{ old('admission_type', 'regular') === 'regular' ? 'd-none' : '' }}" id="drawer_lateral_entry_section">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Candidate is admitted directly into <strong>Semester 5</strong> with block credit exemption.
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="drawer_previous_institution" class="form-label small">Previous College / University <span class="text-danger">*</span></label>
                                <input type="text" name="previous_institution" id="drawer_previous_institution" value="{{ old('previous_institution') }}" class="form-control form-control-sm @error('previous_institution') is-invalid @enderror" placeholder="e.g. Govt Post Graduate College">
                                @error('previous_institution')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="drawer_previous_degree" class="form-label small">Previous Qualification / Degree <span class="text-danger">*</span></label>
                                <input type="text" name="previous_degree" id="drawer_previous_degree" value="{{ old('previous_degree') }}" class="form-control form-control-sm @error('previous_degree') is-invalid @enderror" placeholder="e.g. Associate Degree in Computer Science (ADCS)">
                                @error('previous_degree')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label for="drawer_transferred_credits" class="form-label small">Transferred Credits <span class="text-danger">*</span></label>
                                <input type="number" step="0.5" name="transferred_credits" id="drawer_transferred_credits" value="{{ old('transferred_credits', $defaultLateralCredits ?? 64.0) }}" class="form-control form-control-sm @error('transferred_credits') is-invalid @enderror">
                                <small class="text-muted" style="font-size: 10px;">Standard ADP: 60-66 credits</small>
                                @error('transferred_credits')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label for="drawer_previous_cgpa" class="form-label small">Prior CGPA / Marks</label>
                                <input type="text" name="previous_cgpa" id="drawer_previous_cgpa" value="{{ old('previous_cgpa') }}" class="form-control form-control-sm @error('previous_cgpa') is-invalid @enderror" placeholder="e.g. 3.45">
                                @error('previous_cgpa')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label for="drawer_admitted_to_semester" class="form-label small">Admitted to Semester</label>
                                <select name="admitted_to_semester" id="drawer_admitted_to_semester" class="form-select form-select-sm @error('admitted_to_semester') is-invalid @enderror">
                                    @for($s = 3; $s <= 6; $s++)
                                        <option value="{{ $s }}" @selected(old('admitted_to_semester', $defaultAdmittedSemester ?? 5) == $s)>Semester {{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="drawer_transcript_document" class="form-label small">Transcript Proof <small class="text-muted">(PDF/Img)</small></label>
                                <input type="file" name="transcript_document" id="drawer_transcript_document" class="form-control form-control-sm @error('transcript_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                @error('transcript_document')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="drawer_remarks" class="form-label small">Equivalence Order / Remarks</label>
                                <input type="text" name="remarks" id="drawer_remarks" value="{{ old('remarks') }}" class="form-control form-control-sm @error('remarks') is-invalid @enderror" placeholder="e.g. Ref # EC-2026/08">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="student-form-section">
                <div class="student-form-section-title">
                    <span><i class="bi bi-mortarboard"></i></span>
                    <div>
                        <h6>Current placement</h6>
                        <p>Choose the student's current department and section.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">Select department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="section_id" class="form-label">Section</label>
                        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select department first</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}"
                                    {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} ({{ $section->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="program_id" class="form-label">Program</label>
                        <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required><option value="">Select department first</option>@foreach ($programs as $program)<option value="{{ $program->id }}" data-department-id="{{ $program->department_id }}" @selected(old('program_id') == $program->id)>{{ $program->code }} — {{ $program->name }}</option>@endforeach</select>
                        @error('program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" class="btn-submit">
                <i class="bi bi-check2-circle"></i> Create Student
            </button>
        </div>
    </form>
</div>
