@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/add-course-dropdown.css') }}">
    <script src="{{ asset('js/add-courses.js') }}"></script>
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

            <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

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
                        <label for="semester" class="form-label">Semester</label>
                        <select name="semester" id="semester"
                            class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">Select Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="Semester {{ $i }}"
                                    {{ old('semester') == "Semester $i" ? 'selected' : '' }}>
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
                            <div class="invalid-feedback">{{ $message }}</div>
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
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
