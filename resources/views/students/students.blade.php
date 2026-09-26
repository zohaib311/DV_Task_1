@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/student-model.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/add-student-drawer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/add-course-dropdown.css') }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="table__content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Students List</h4>
                    @if (session('success'))
                        <div class="alert alert-success mt-2 mb-0" id="success-message">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="add__user__btn">
                        <button type="button" class="btn text-white d-inline-flex align-items-center gap-2 border-0 px-3 py-2 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.2);" data-bs-toggle="offcanvas" data-bs-target="#addStudentDrawer" aria-controls="addStudentDrawer">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>Add Student</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Reg No</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Semester</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr>
                                        <th scope="row">{{ $student->id }}</th>
                                        <td>
                                            <span class="badge bg-secondary text-wrap reg-no-badge">
                                                {{ $student->registration_no ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="user__image">
                                            <img src="{{ asset('storage/images/' . $student->image) }}"
                                                alt="{{ $student->name }}" class="user-image">
                                        </td>
                                        <td class="fw-semibold">{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>{{ $student->phone }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ $student->semester ?? 'N/A' }}
                                            </span>
                                        </td>


                                        <td>
                                            <div class="action__buttons">
                                                <button type="button" class="action__btn action__info" title="View Student"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#infoStudentModal{{ $student->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="{{ route('editStudentForm', $student->id) }}"
                                                    class="action__btn action__edit" title="Edit Student">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Student" data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal{{ $student->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($students as $student)
        <div class="modal fade" id="deleteStudentModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content delete__modal">
                    <div class="modal-body text-center">
                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <h4>Delete Student?</h4>
                        <p>
                            Are you sure you want to delete
                            <strong>{{ $student->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>
                        <div class="delete__modal__actions">
                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('deleteStudent', $student->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete__confirm">
                                    <i class="bi bi-trash3 me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($students as $student)
        <div class="modal fade student-detail-modal" id="infoStudentModal{{ $student->id }}" tabindex="-1"
            aria-labelledby="infoStudentModalLabel{{ $student->id }}" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">

                    <div class="modal-header student-modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-vcard-fill"></i>
                            <h5 class="modal-title mb-0" id="infoStudentModalLabel{{ $student->id }}">
                                Student Details
                            </h5>
                        </div>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>

                    <div class="modal-body student-modal-body">

                        <div class="student-modal-layout">

                            <div class="student-profile-card">

                                <div class="student-profile-image">
                                    <img src="{{ asset('storage/images/' . ($student->image ?: 'default-user.png')) }}"
                                        alt="{{ $student->name }}" class="student-modal-avatar">
                                </div>

                                <div class="student-profile-info">
                                    <h3>{{ $student->name }}</h3>

                                    <div class="student-profile-item">
                                        <i class="bi bi-envelope"></i>
                                        <span>{{ $student->email }}</span>
                                    </div>

                                    <div class="student-profile-item">
                                        <i class="bi bi-telephone"></i>
                                        <span>{{ $student->phone }}</span>
                                    </div>
                                </div>

                            </div>

                            <div class="student-details-box">

                                <div class="student-details-heading">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Student Information</span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-hash"></i>
                                        Student ID
                                    </span>
                                    <span class="student-detail-value">
                                        #{{ $student->id }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-card-heading"></i>
                                        Registration No
                                    </span>
                                    <span class="student-detail-value fw-bold text-primary">
                                        {{ $student->registration_no ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-person"></i>
                                        Full Name
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->name }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-envelope"></i>
                                        Email Address
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->email }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-telephone"></i>
                                        Phone Number
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->phone }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-building"></i>
                                        Department
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->department->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-diagram-3"></i>
                                        Section
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->section->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar3"></i>
                                        Semester
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->semester ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row student-courses-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-book"></i>
                                        Assigned Courses
                                    </span>

                                    <span class="student-detail-value student-course-list">
                                        @forelse ($student->assigned_courses as $course)
                                            <span class="student-course-badge">
                                                {{ $course->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted">No courses assigned</span>
                                        @endforelse
                                    </span>
                                </div>

                                {{-- <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-calendar-plus"></i>
                                        Registered At
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->created_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="student-detail-row">
                                    <span class="student-detail-label">
                                        <i class="bi bi-clock-history"></i>
                                        Last Updated
                                    </span>
                                    <span class="student-detail-value">
                                        {{ $student->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}
                                    </span>
                                </div> --}}

                            </div>

                        </div>

                    </div>



                </div>
            </div>
        </div>
    @endforeach

    <!-- Offcanvas / Side Drawer for Add Student -->
    <div class="offcanvas offcanvas-end add-student-drawer" tabindex="-1" id="addStudentDrawer" aria-labelledby="addStudentDrawerLabel">
        <form action="{{ route('addStudent') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100 m-0">
            @csrf

            <!-- Fixed Header -->
            <div class="drawer-header">
                <h5 id="addStudentDrawerLabel">
                    <i class="bi bi-person-plus-fill"></i>
                    Add New Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <!-- Scrollable Body -->
            <div class="drawer-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter student name"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Enter student email"
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="03XXXXXXXXX" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="department_id" class="form-label fw-semibold">Department</label>
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

                    <div class="col-md-6">
                        <label for="section_id" class="form-label fw-semibold">Section</label>
                        <select name="section_id" id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Department First</option>
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

                    <div class="col-md-6">
                        <label for="semester" class="form-label fw-semibold">Semester</label>
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

                    <div class="col-12">
                        <label class="form-label fw-semibold">Assigned Courses</label>
                        <div class="course-dropdown">
                            <button type="button" class="course-dropdown-btn" id="courseDropdownBtn">
                                <span id="courseDropdownText">Select Courses</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="course-dropdown-menu" id="courseDropdownMenu">
                                @foreach ($courses as $course)
                                    @php
                                        $selectedCourses = old('course_ids', []);
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
                        <label for="image" class="form-label fw-semibold">Student Image</label>
                        <input type="file" name="image" id="image"
                            class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fixed Footer -->
            <div class="drawer-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check2-circle me-1"></i> Add Student
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/add-courses.js') }}"></script>
    <script src="{{ asset('js/student/department-section-filter.js') }}"></script>
    <script src="{{ asset('js/student/alert-dismiss.js') }}"></script>
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const addDrawerEl = document.getElementById('addStudentDrawer');
                if (addDrawerEl) {
                    const addDrawer = new bootstrap.Offcanvas(addDrawerEl);
                    addDrawer.show();
                }
            });
        </script>
    @endif
@endsection
