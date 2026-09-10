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

                    <h4 class="mb-0">
                        Students List
                    </h4>

                    @if (session('success'))
                        <div class="alert alert-success mt-3 mb-0" id="success-message">
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
                                    <th scope="col">Class</th>
                                    <th scope="col">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($students as $student)
                                    <tr>

                                        <th scope="row">
                                            {{ $student->id }}
                                        </th>

                                        <td class="user__image">
                                            <img src="{{ asset('storage/images/' . $student->image) }}"
                                                alt="{{ $student->name }}" class="user-image">
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $student->name }}
                                        </td>

                                        <td>
                                            {{ $student->email }}
                                        </td>

                                        <td>
                                            {{ $student->phone }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $student->class }}
                                            </span>
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
                                @endforeach

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

                        <h4>
                            Delete Student?
                        </h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $student->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">

                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteStudent', $student->id) }}" method="POST">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="delete__confirm">
                                    <i class="bi bi-trash3 me-1"></i>
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    @endforeach

    <script>
        setTimeout(function() {

            const message = document.getElementById('success-message');

            if (message) {

                message.style.transition = 'opacity 0.5s ease';
                message.style.opacity = '0';

                setTimeout(function() {
                    message.remove();
                }, 500);

            }

        }, 2000);
    </script>
@endsection
