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
                    <h4 class="mb-0">Departments List</h4>

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
                        <a href="{{ route('addDepartmentForm') }}" class="nav-link">
                            <span>Add Department</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Department Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr>
                                        <th>{{ $department->id }}</th>
                                        <td class="fw-semibold">{{ $department->name }}</td>
                                        <td>
                                            <div class="action__buttons">
                                                <a href="{{ route('editDepartmentForm', $department->id) }}"
                                                    class="action__btn action__edit" title="Edit Department">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Department" data-bs-toggle="modal"
                                                    data-bs-target="#deleteDepartmentModal{{ $department->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            No departments found.
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

    @foreach ($departments as $department)
        <div class="modal fade" id="deleteDepartmentModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content delete__modal">
                    <div class="modal-body text-center">
                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>Delete Department?</h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $department->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">
                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteDepartment', $department->id) }}" method="POST">
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
