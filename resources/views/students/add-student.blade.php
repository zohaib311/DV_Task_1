@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Student</h2>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data" data-student-form>
                @csrf

                <div class="row">

                    <div class="col-12 mb-3">
                        <label for="user_id" class="form-label">Login Account <small class="text-muted">(Optional)</small></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Create profile without portal access</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter student name"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter student email"
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="create_portal_account" value="1" id="create_portal_account" @checked(old('create_portal_account', !old('user_id')))><label class="form-check-label fw-semibold" for="create_portal_account">Create Student Portal account</label></div>
                            <small class="text-muted d-block mt-1">The student's name, email, phone, and selected image will create their login automatically.</small>
                            <div class="row g-3 mt-1" data-account-passwords><div class="col-md-6"><label class="form-label" for="account_password">Portal password</label><input class="form-control @error('account_password') is-invalid @enderror" type="password" name="account_password" id="account_password" autocomplete="new-password"><small class="text-muted">At least 8 characters</small></div><div class="col-md-6"><label class="form-label" for="account_password_confirmation">Confirm password</label><input class="form-control" type="password" name="account_password_confirmation" id="account_password_confirmation" autocomplete="new-password"></div></div>
                            @error('account_password')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Admission & Entry Type -->
                    <div class="col-12 mt-3">
                        <div class="border rounded-3 p-3 bg-light-subtle">
                            <label class="form-label fw-semibold d-block"><i class="bi bi-mortarboard me-1"></i> Admission Entry Type</label>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="admission_type" id="admission_regular" value="regular" @checked(old('admission_type', 'regular') === 'regular')>
                                    <label class="form-check-label fw-medium" for="admission_regular">
                                        Regular Admission (Semester 1 Entry)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="admission_type" id="admission_lateral_adp" value="lateral_adp" @checked(old('admission_type') === 'lateral_adp')>
                                    <label class="form-check-label fw-medium text-primary" for="admission_lateral_adp">
                                        Lateral Entry (Post-ADP 5th Semester)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="admission_type" id="admission_migration" value="migration" @checked(old('admission_type') === 'migration')>
                                    <label class="form-check-label fw-medium" for="admission_migration">
                                        Credit Transfer / Migration
                                    </label>
                                </div>
                            </div>

                            <!-- Lateral Entry & Credit Transfer Fields -->
                            <div class="border-top pt-3 mt-3 {{ old('admission_type', 'regular') === 'regular' ? 'd-none' : '' }}" id="lateral_entry_section">
                                <div class="alert alert-info py-2 small mb-3">
                                    <i class="bi bi-info-circle me-1"></i> Lateral entry candidates are admitted directly into <strong>Semester 5</strong> with block credit exemption.
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="previous_institution" class="form-label">Previous Institution / University <span class="text-danger">*</span></label>
                                        <input type="text" name="previous_institution" id="previous_institution" value="{{ old('previous_institution') }}" class="form-control @error('previous_institution') is-invalid @enderror" placeholder="e.g. University of the Punjab / Govt College">
                                        @error('previous_institution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="previous_degree" class="form-label">Previous Qualification / Degree <span class="text-danger">*</span></label>
                                        <input type="text" name="previous_degree" id="previous_degree" value="{{ old('previous_degree') }}" class="form-control @error('previous_degree') is-invalid @enderror" placeholder="e.g. Associate Degree in Computer Science (ADCS)">
                                        @error('previous_degree')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="transferred_credits" class="form-label">Transferred Credit Hours <span class="text-danger">*</span></label>
                                        <input type="number" step="0.5" name="transferred_credits" id="transferred_credits" value="{{ old('transferred_credits', $defaultLateralCredits ?? 64.0) }}" class="form-control @error('transferred_credits') is-invalid @enderror">
                                        <small class="text-muted">Standard ADP: 60-66 credits</small>
                                        @error('transferred_credits')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="previous_cgpa" class="form-label">Prior CGPA / Percentage <small class="text-muted">(Optional)</small></label>
                                        <input type="text" name="previous_cgpa" id="previous_cgpa" value="{{ old('previous_cgpa') }}" class="form-control @error('previous_cgpa') is-invalid @enderror" placeholder="e.g. 3.45 / 4.00 or 74%">
                                        @error('previous_cgpa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="admitted_to_semester" class="form-label">Admitted to Semester</label>
                                        <select name="admitted_to_semester" id="admitted_to_semester" class="form-select @error('admitted_to_semester') is-invalid @enderror">
                                            @for($s = 3; $s <= 6; $s++)
                                                <option value="{{ $s }}" @selected(old('admitted_to_semester', $defaultAdmittedSemester ?? 5) == $s)>Semester {{ $s }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transcript_document" class="form-label">Previous Transcript / Equivalence Proof <small class="text-muted">(PDF or Image)</small></label>
                                        <input type="file" name="transcript_document" id="transcript_document" class="form-control @error('transcript_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                        @error('transcript_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="remarks" class="form-label">Equivalence Remarks / Order No. <small class="text-muted">(Optional)</small></label>
                                        <input type="text" name="remarks" id="remarks" value="{{ old('remarks') }}" class="form-control @error('remarks') is-invalid @enderror" placeholder="e.g. Equivalence Committee Ref # EC-2026/08">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id"
                            class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="section_id" class="form-label">Section</label>
                        <select name="section_id" id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}"
                                    data-department-id="{{ $section->department_id }}"
                                    {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} ({{ $section->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="program_id" class="form-label">Program</label>
                        <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                            <option value="">Select department first</option>
                            @foreach ($programs as $program)<option value="{{ $program->id }}" data-department-id="{{ $program->department_id }}" @selected(old('program_id') == $program->id)>{{ $program->code }} — {{ $program->name }}</option>@endforeach
                        </select>
                        @error('program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label">Student Image</label>
                        <input type="file" name="image" id="image"
                            class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Student
                        </button>
                    </div>

                </div>
            </form>
        </div>
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
    </script>
@endsection
