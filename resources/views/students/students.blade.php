@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
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
                                    <th scope="col">Department</th>
                                    <th scope="col">Section</th>
                                    <th scope="col">Assigned Courses</th>
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
                                        <td>{{ $student->department->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $student->section->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @forelse ($student->assigned_courses as $c)
                                                <span class="badge bg-primary me-1 mb-1">
                                                    {{ $c->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted small">No courses</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <div class="action__buttons">
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
@endsection
