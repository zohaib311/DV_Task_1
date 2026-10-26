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
                        <h4 class="mb-0">Events List</h4>
                        <span data-live-list-count class="student-search-count" aria-live="polite">{{ $events->count() }} events</span>
                    </div>

                    <div class="student-search-field student-header-search" data-live-list-search
                        data-rows=".event-search-row" data-empty="#events-empty-state"
                        data-no-results="#events-no-search-results" data-label="events">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input data-live-list-input type="search" autocomplete="off"
                            placeholder="Search by event name, day, date or description"
                            aria-label="Search events by name, day, date or description">
                        <button data-live-list-clear type="button" aria-label="Clear event search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="add__user__btn">
                        <a href="{{ route('addEventForm') }}" class="nav-link">
                            <span>Add Event</span>
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
                                    <th>Event Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($events as $event)
                                    <tr class="event-search-row"
                                        data-search="{{ strtolower(implode(' ', [$event->event_name, $event->day, $event->date, $event->description ?? ''])) }}">

                                        <th>
                                            {{ $event->id }}
                                        </th>

                                        <td class="fw-semibold">
                                            {{ $event->event_name }}
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $event->day }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}
                                        </td>

                                        <td class="text-wrap">
                                            {{ $event->description }}
                                        </td>

                                        <td>
                                            <div class="action__buttons">

                                                <a href="{{ route('editEventForm', $event->id) }}"
                                                    class="action__btn action__edit" title="Edit Event">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="action__btn action__delete"
                                                    title="Delete Event" data-bs-toggle="modal"
                                                    data-bs-target="#deleteEventModal{{ $event->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </div>
                                        </td>

                                    </tr>

                                @empty

                                    <tr id="events-empty-state">
                                        <td colspan="8" class="text-center py-4">
                                            No events found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr id="events-no-search-results" hidden>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> No event matches your search.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @foreach ($events as $event)
        <div class="modal fade" id="deleteEventModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content delete__modal">
                    <div class="modal-body text-center">
                        <div class="delete__modal__icon">
                            <i class="bi bi-trash3"></i>
                        </div>

                        <h4>Delete Event?</h4>

                        <p>
                            Are you sure you want to delete
                            <strong>{{ $event->event_name }}</strong>?
                            <br>
                            This action cannot be undone.
                        </p>

                        <div class="delete__modal__actions">
                            <button type="button" class="delete__cancel" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <form action="{{ route('deleteEvent', $event->id) }}" method="POST">
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
