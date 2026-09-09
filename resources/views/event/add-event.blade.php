@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Event</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('addEvent') }}" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="event_name" class="form-label">
                            Event Name
                        </label>

                        <input type="text" name="event_name" id="event_name" value="{{ old('event_name') }}"
                            class="form-control @error('event_name') is-invalid @enderror" placeholder="Enter event name"
                            required>

                        @error('event_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="time" class="form-label">
                            Time
                        </label>

                        <input type="time" name="time" id="time" value="{{ old('time') }}"
                            class="form-control @error('time') is-invalid @enderror" required>

                        @error('time')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="day" class="form-label">
                            Day
                        </label>

                        <select name="day" id="day" class="form-select @error('day') is-invalid @enderror"
                            required>
                            <option value="">Select Day</option>
                            <option value="Monday" {{ old('day') == 'Monday' ? 'selected' : '' }}>
                                Monday
                            </option>
                            <option value="Tuesday" {{ old('day') == 'Tuesday' ? 'selected' : '' }}>
                                Tuesday
                            </option>
                            <option value="Wednesday" {{ old('day') == 'Wednesday' ? 'selected' : '' }}>
                                Wednesday
                            </option>
                            <option value="Thursday" {{ old('day') == 'Thursday' ? 'selected' : '' }}>
                                Thursday
                            </option>
                            <option value="Friday" {{ old('day') == 'Friday' ? 'selected' : '' }}>
                                Friday
                            </option>
                            <option value="Saturday" {{ old('day') == 'Saturday' ? 'selected' : '' }}>
                                Saturday
                            </option>
                            <option value="Sunday" {{ old('day') == 'Sunday' ? 'selected' : '' }}>
                                Sunday
                            </option>
                        </select>

                        @error('day')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">
                            Date
                        </label>

                        <input type="date" name="date" id="date" value="{{ old('date') }}"
                            class="form-control @error('date') is-invalid @enderror" required>

                        @error('date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">
                            Description
                        </label>

                        <textarea name="description" id="description" rows="5"
                            class="form-control @error('description') is-invalid @enderror" placeholder="Enter event description" required>{{ old('description') }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Event
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>
@endsection
