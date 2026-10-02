@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enrollment/enrollment.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form mx-auto">

            <div class="edit__header">
                <div>
                    <h2>Update Student</h2>
                    <p>Edit student information and update the profile.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('updateStudent', $student->id) }}" method="POST" enctype="multipart/form-data" data-student-form>
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-12">
                        <label for="user_id" class="form-label">Login Account <small class="text-muted">(Optional)</small></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">No portal account linked</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" @selected(old('user_id', $student->user_id) == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">A linked account will receive Student Portal access in Phase 9G.</small>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter student name"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $student->email) }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter student email"
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id"
                            class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $student->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="registration_no" class="form-label">Registration No</label>
                        <input type="text" id="registration_no" value="{{ $student->registration_no ?? 'N/A' }}"
                            class="form-control bg-light" readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="program_id" class="form-label">Program</label>
                        <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                            <option value="">Select department first</option>
                            @foreach ($programs as $program)<option value="{{ $program->id }}" data-department-id="{{ $program->department_id }}" @selected(old('program_id', $student->program_id) == $program->id)>{{ $program->code }} — {{ $program->name }}</option>@endforeach
                        </select>
                        @error('program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="section_id" class="form-label">Section</label>
                        <select name="section_id" id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}"
                                    data-department-id="{{ $section->department_id }}"
                                    {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} ({{ $section->department->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="alert alert-light border academic-enrollment-note mb-0">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <div>
                                <strong>Academic enrollment</strong>
                                @if ($activeEnrollment)
                                    <span>{{ $activeEnrollment->semester }} · {{ $activeEnrollment->academic_year }} · {{ $activeEnrollment->courses->count() }} assigned courses</span>
                                @else
                                    <span>No semester enrollment has been created for this student yet.</span>
                                @endif
                                <a href="{{ route('addEnrollmentForm', ['student_id' => $student->id]) }}">Manage enrollment</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="image__section">
                            <div class="current__image__box">
                                <div class="image__section__title">
                                    <div class="image__section__icon"><i class="bi bi-image"></i></div>
                                    <div>
                                        <h6>Current Image</h6>
                                        <small>Student profile image</small>
                                    </div>
                                </div>
                                <div class="current__image__preview">
                                    <img src="{{ asset('storage/images/' . $student->image) }}" alt="{{ $student->name }}"
                                        class="user-image">
                                </div>
                            </div>

                            <div class="change__image__box">
                                <div class="image__section__title">
                                    <div class="image__section__icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <div>
                                        <h6>Change Image</h6>
                                        <small>Upload a new image</small>
                                    </div>
                                </div>
                                <label for="image" class="upload__box">
                                    <div class="upload__icon"><i class="bi bi-upload"></i></div>
                                    <div class="upload__text">
                                        <span>Choose a new image</span>
                                        <small>JPG, JPEG or PNG • Max 2MB</small>
                                    </div>
                                </label>
                                <input type="file" name="image" id="image" class="d-none">
                                @error('image')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form__actions">
                            <a href="{{ route('allStudents') }}" class="cancel__btn">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="update__btn">
                                <i class="bi bi-check2-circle me-1"></i> Update Student
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/student/department-section-filter.js') }}"></script>
    <script src="{{ asset('js/student/student-account-flow.js') }}"></script>
@endsection
