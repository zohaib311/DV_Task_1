@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container py-5">
        <div class="table__content">
            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white student-list-header">
                    <div class="student-list-heading">
                        <h4 class="mb-0">Departments List</h4>
                        <span data-live-list-count class="student-search-count" aria-live="polite">{{ $departments->count() }} departments</span>
                    </div>

                    <div class="student-search-field student-header-search" data-live-list-search
                        data-rows=".department-search-row" data-empty="#departments-empty-state"
                        data-no-results="#departments-no-search-results" data-label="departments">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input data-live-list-input type="search" autocomplete="off"
                            placeholder="Search by department name"
                            aria-label="Search departments by name">
                        <button data-live-list-clear type="button" aria-label="Clear department search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="add__user__btn">
                        <a href="{{ route('addDepartmentForm') }}" class="nav-link">
                            <span>Add Department</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success m-3 mb-0" data-auto-dismiss>
                            {{ session('success') }}
                        </div>
                    @endif
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
                                    <tr class="department-search-row" data-search="{{ strtolower($department->name) }}">
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
                                    <tr id="departments-empty-state">
                                        <td colspan="3" class="text-center py-4">
                                            No departments found.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="departments-no-search-results" hidden>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No department matches your search.
                                    </td>
                                </tr>
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

@section('scripts')
    <script src="{{ asset('js/universal/live-list-search.js') }}"></script>
@endsection
