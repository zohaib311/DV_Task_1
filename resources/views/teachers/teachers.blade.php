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
                        <h4 class="mb-0">Teachers List</h4>
                        <span id="teacher-search-count" class="student-search-count" aria-live="polite">{{ $teachers->count() }} teachers</span>
                    </div>

                    <div class="student-search-field student-header-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="teacher-live-search" type="search" autocomplete="off"
                            placeholder="Search by name, email, phone or specialization"
                            aria-label="Search teachers by name, email, phone or specialization">
                        <button id="teacher-search-clear" type="button" aria-label="Clear teacher search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="add__user__btn">
                        <a href="{{ route('addTeacherForm') }}" class="nav-link">
                            <span>Add Teacher</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3 mb-0" id="success-message">
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
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Login Account</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="teachers-table-body">

                                @forelse ($teachers as $teacher)
                                    <tr class="teacher-search-row"
                                        data-search="{{ strtolower(implode(' ', [$teacher->name, $teacher->email, $teacher->phone, $teacher->course ?? ''])) }}">

                                        <th>
                                            {{ $teacher->id }}
                                        </th>

                                        <td class="user__image">
                                            <img src="{{ asset('storage/images/' . $teacher->image) }}"
                                                alt="{{ $teacher->name }}" class="user-image">
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $teacher->name }}
                                        </td>

                                        <td>
                                            {{ $teacher->email }}
                                        </td>

                                        <td>
                                            {{ $teacher->phone }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $teacher->course }}
                                            </span>

                                        </td>

                                        <td>
                                            @if ($teacher->user)
                                                <span class="badge bg-success">Linked</span>
                                            @else
                                                <span class="text-muted small">Not linked</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="action__buttons">

                                                <a href="{{ route('editTeacherForm', $teacher->id) }}"
                                                    class="action__btn action__edit" title="Edit Teacher">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Teacher" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $teacher->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>
                                        </td>

                                    </tr>

                                @empty

                                    <tr id="teachers-empty-state">
                                        <td colspan="8" class="text-center py-4">
                                            No teachers found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr id="teachers-no-search-results" hidden>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No teacher matches your search.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @foreach ($teachers as $teacher)
        <div class="modal fade" id="deleteModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete__modal">

                    <div class="modal-body text-center">

                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>
                            Delete Teacher?
                        </h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $teacher->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">

                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteTeacher', $teacher->id) }}" method="POST">

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('teacher-live-search');
            const clear = document.getElementById('teacher-search-clear');
            const count = document.getElementById('teacher-search-count');
            const rows = Array.from(document.querySelectorAll('.teacher-search-row'));
            const emptyState = document.getElementById('teachers-empty-state');
            const noResults = document.getElementById('teachers-no-search-results');

            if (!search || !count || !noResults) return;

            const filterTeachers = function () {
                const query = search.value.trim().toLowerCase();
                let matches = 0;

                rows.forEach(function (row) {
                    const matchesSearch = !query || (row.dataset.search || '').includes(query);
                    row.hidden = !matchesSearch;
                    if (matchesSearch) matches++;
                });

                noResults.hidden = rows.length === 0 || matches > 0;
                if (emptyState) emptyState.hidden = Boolean(query);
                if (clear) clear.hidden = !query;
                count.textContent = query ? `${matches} of ${rows.length} teachers` : `${rows.length} teachers`;
            };

            search.addEventListener('input', filterTeachers);
            clear?.addEventListener('click', function () {
                search.value = '';
                filterTeachers();
                search.focus();
            });

            setTimeout(function () {
                const message = document.getElementById('success-message');
                if (!message) return;

                message.style.transition = 'opacity .35s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 350);
            }, 2800);
        });
    </script>
@endsection
