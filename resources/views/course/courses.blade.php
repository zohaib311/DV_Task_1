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
                        <h4 class="mb-0">Courses List</h4>
                        <span data-live-list-count class="student-search-count" aria-live="polite">{{ $courses->count() }} courses</span>
                    </div>

                    <div class="student-search-field student-header-search" data-live-list-search
                        data-rows=".course-search-row" data-empty="#courses-empty-state"
                        data-no-results="#courses-no-search-results" data-label="courses">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input data-live-list-input type="search" autocomplete="off"
                            placeholder="Search by course code, name or description"
                            aria-label="Search courses by code, name or description">
                        <button data-live-list-clear type="button" aria-label="Clear course search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="add__user__btn">
                        <a href="{{ route('addCourseForm') }}" class="nav-link">
                            <span>Add Course</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3 mb-0" data-auto-dismiss>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger m-3 mb-0" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Credit Hours</th>
                                    <th>Assessment</th>
                                    <th>Total Marks</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($courses as $course)
                                    <tr class="course-search-row"
                                        data-search="{{ strtolower(implode(' ', [$course->code, $course->name, $course->description ?? '', $course->credit_hours, $course->is_active ? 'active' : 'inactive'])) }}">

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
                                            <span class="badge bg-info text-dark">
                                                {{ number_format((float) $course->credit_hours, 1) }} Cr. Hrs
                                            </span>
                                        </td>

                                        <td>
                                            <small class="d-block text-muted">Att. {{ $course->attendance_marks }}</small>
                                            <small class="d-block text-muted">Mid {{ $course->mid_marks }} · Final {{ $course->final_marks }}</small>
                                        </td>

                                        <td>
                                            {{ $course->total_marks }}
                                        </td>

                                        <td>
                                            <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $course->is_active ? 'Active' : 'Inactive' }}
                                            </span>
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

                                    <tr id="courses-empty-state">
                                        <td colspan="9" class="text-center py-4">
                                            No courses found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr id="courses-no-search-results" hidden>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No course matches your search.
                                    </td>
                                </tr>

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

@section('scripts')
    <script src="{{ asset('js/universal/live-list-search.js') }}"></script>
@endsection
