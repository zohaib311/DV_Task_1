@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">

                    <h4 class="mb-0">Courses List</h4>

                    @if (session('success'))
                        <div class="alert alert-success" id="success-message">
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
                        <a href="{{ route('addCourseForm') }}" class="nav-link">
                            <span>Add Course</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($courses as $course)
                                    <tr>

                                        <th>
                                            {{ $course->id }}
                                        </th>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $course->code }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $course->name }}
                                        </td>

                                        <td>
                                            {{ $course->description }}
                                        </td>

                                        <td>
                                            <div class="action__buttons">

                                                <a href="{{ route('editCourseForm', $course->id) }}"
                                                    class="action__btn action__edit" title="Edit Course">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Course" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $course->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            No courses found.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @foreach ($courses as $course)
        <div class="modal fade" id="deleteModal{{ $course->id }}" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete__modal">

                    <div class="modal-body text-center">

                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>
                            Delete course?
                        </h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $course->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">

                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteCourse', $course->id) }}" method="POST">

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
@endsection
