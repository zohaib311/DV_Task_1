@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        <div class="table__content">

            <div class="card shadow-sm border-0">

                <div class="card-header flex-auto bg-primary text-white">

                    <h4 class="mb-0">Events List</h4>

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
                        <a href="{{ route('addEventForm') }}" class="nav-link">
                            <span>Add Event</span>
                        </a>
                    </div>

                </div>

                <div class="card-body">

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

                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Time</th>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($events as $event)
                                    <tr>

                                        <th>
                                            {{ $event->id }}
                                        </th>


                                        <td class="fw-semibold">
                                            {{ $event->event_name }}
                                        </td>


                                        <td>
                                            {{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}
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

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            No events found.
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
@endsection
