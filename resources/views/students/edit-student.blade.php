@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/add-course-dropdown.css') }}">
    <script src="{{ asset('js/add-courses.js') }}"></script>
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

            <form action="{{ route('updateStudent', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">

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

                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <select name="semester" id="semester"
                            class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">Select Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="Semester {{ $i }}"
                                    {{ old('semester', $student->semester) == "Semester $i" ? 'selected' : '' }}>
                                    Semester {{ $i }}
                                </option>
                            @endfor
                        </select>
                        @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Assigned Courses</label>

                        <div class="course-dropdown">
                            <button type="button" class="course-dropdown-btn" id="courseDropdownBtn">
                                <span id="courseDropdownText">Select Courses</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="course-dropdown-menu" id="courseDropdownMenu">

                                @foreach ($courses as $course)
                                    @php
                                        $selectedCourses = old('course_ids', $student->course_ids ?? []);
                                    @endphp

                                    <label class="course-option">
                                        <input type="checkbox" name="course_ids[]" value="{{ $course->id }}"
                                            {{ is_array($selectedCourses) && in_array($course->id, $selectedCourses) ? 'checked' : '' }}>

                                        <span class="course-checkbox"></span>

                                        <span class="course-option-content">
                                            <span class="course-name">{{ $course->name }}</span>
                                            <span class="course-code">{{ $course->code }}</span>
                                        </span>
                                    </label>
                                @endforeach

                            </div>
                        </div>

                        @error('course_ids')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const departmentSelect = document.getElementById('department_id');
            const sectionSelect = document.getElementById('section_id');

            if (departmentSelect && sectionSelect) {
                function filterSections() {
                    const selectedDeptId = departmentSelect.value;
                    const options = sectionSelect.querySelectorAll('option');

                    options.forEach(option => {
                        if (!option.value) return; // Skip placeholder option

                        const deptId = option.getAttribute('data-department-id');

                        if (!selectedDeptId || deptId === selectedDeptId) {
                            option.hidden = false;
                            option.disabled = false;
                        } else {
                            option.hidden = true;
                            option.disabled = true;
                            if (option.selected) {
                                option.selected = false;
                            }
                        }
                    });

                    // If currently selected option is now hidden/disabled, reset selection
                    const currentOption = sectionSelect.options[sectionSelect.selectedIndex];
                    if (currentOption && currentOption.disabled) {
                        sectionSelect.value = '';
                    }
                }

                departmentSelect.addEventListener('change', filterSections);
                filterSections(); // Run initially for pre-selected values
            }
        });
    </script>
@endsection
