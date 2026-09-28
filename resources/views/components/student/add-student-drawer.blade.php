<div class="offcanvas offcanvas-end add-student-drawer" tabindex="-1" id="addStudentDrawer"
    aria-labelledby="addStudentDrawerLabel">
    <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data"
        class="d-flex flex-column h-100 m-0" novalidate>
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
                <span>A registration number will be created automatically. Semester and courses are assigned from Enrollments.</span>
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
