@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/student-model.css') }}">
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

                    <script>
                        setTimeout(function() {
                            const message = document.getElementById('success-message');

                            if (message) {
                                message.style.transition = 'opacity 0.5s ease';
                                message.style.opacity = '0';

                                setTimeout(() => message.remove(), 500);
                            }
                        }, 2000);
                    </script>

                    <div class="add__user__btn">
                        <a href="{{ route('addStudentForm') }}" class="nav-link">
                            <span>Add Student</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr>
                                        <th scope="row">{{ $student->id }}</th>
                                        <td class="user__image">
                                            <img src="{{ asset('storage/images/' . $student->image) }}"
                                                alt="{{ $student->name }}" class="user-image">
                                        </td>
                                        <td class="fw-semibold">{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>{{ $student->phone }}</td>


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
@endsection
