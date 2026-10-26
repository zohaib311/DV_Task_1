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
                        <h4 class="mb-0">Sections List</h4>
                        <span data-live-list-count class="student-search-count" aria-live="polite">{{ $sections->count() }} sections</span>
                    </div>

                    <div class="student-search-field student-header-search" data-live-list-search
                        data-rows=".section-search-row" data-empty="#sections-empty-state"
                        data-no-results="#sections-no-search-results" data-label="sections">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input data-live-list-input type="search" autocomplete="off"
                            placeholder="Search by section or department"
                            aria-label="Search sections by section or department name">
                        <button data-live-list-clear type="button" aria-label="Clear section search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="add__user__btn">
                        <a href="{{ route('addSectionForm') }}" class="nav-link">
                            <span>Add Section</span>
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
                                    <th>Section Name</th>
                                    <th>Department</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($sections as $section)
                                    <tr class="section-search-row"
                                        data-search="{{ strtolower(implode(' ', [$section->name, $section->department?->name ?? ''])) }}">

                                        <th>
                                            {{ $section->id }}
                                        </th>

                                        <td class="fw-semibold">
                                            {{ $section->name }}
                                        </td>

                                        <td>
                                            {{ $section->department->name ?? 'N/A' }}
                                        </td>


                                        <td>

                                            <div class="action__buttons">

                                                <a href="{{ route('editSectionForm', $section->id) }}"
                                                    class="action__btn action__edit" title="Edit Section">

                                                    <i class="bi bi-pencil-square"></i>

                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Section" data-bs-toggle="modal"
                                                    data-bs-target="#deleteSectionModal{{ $section->id }}">

                                                    <i class="bi bi-trash3"></i>

                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr id="sections-empty-state">

                                        <td colspan="4" class="text-center py-4">
                                            No sections found.
                                        </td>

                                    </tr>
                                @endforelse

                                <tr id="sections-no-search-results" hidden>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No section matches your search.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    @foreach ($sections as $section)
        <div class="modal fade" id="deleteSectionModal{{ $section->id }}" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content delete__modal">

                    <div class="modal-body text-center">

                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>Delete Section?</h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $section->name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">

                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">

                                Cancel

                            </button>

                            <form action="{{ route('deleteSection', $section->id) }}" method="POST">

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
