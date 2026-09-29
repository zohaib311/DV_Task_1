@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">

            <h2 class="text-center mb-4">Add New Course</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('addCourse') }}" method="POST" data-assessment-form>
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">
                            Course Code
                        </label>

                        <input type="text" name="code" id="code" value="{{ old('code') }}"
                            class="form-control @error('code') is-invalid @enderror" placeholder="e.g. CS101">

                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12 mb-2">
                        <div class="assessment-scheme-heading">
                            <div>
                                <span>Assessment scheme</span>
                                <small>Attendance, Midterm aur Final ka total course marks ke barabar hona chahiye.</small>
                            </div>
                            <strong id="assessment_scheme_total">0 / {{ old('total_marks', config('academic.marks.default_total')) }}</strong>
                        </div>
                        @error('assessment_scheme')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="attendance_marks" class="form-label">Attendance Marks</label>
                        <input type="number" name="attendance_marks" id="attendance_marks"
                            value="{{ old('attendance_marks', config('academic.assessment.defaults.attendance_marks')) }}"
                            class="form-control @error('attendance_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('attendance_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="mid_marks" class="form-label">Midterm Marks</label>
                        <input type="number" name="mid_marks" id="mid_marks"
                            value="{{ old('mid_marks', config('academic.assessment.defaults.mid_marks')) }}"
                            class="form-control @error('mid_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('mid_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="final_marks" class="form-label">Final Marks</label>
                        <input type="number" name="final_marks" id="final_marks"
                            value="{{ old('final_marks', config('academic.assessment.defaults.final_marks')) }}"
                            class="form-control @error('final_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('final_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            Course Name
                        </label>

                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter course name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="credit_hours" class="form-label">
                            Credit Hours
                        </label>

                        <input type="number" name="credit_hours" id="credit_hours"
                            value="{{ old('credit_hours', config('academic.courses.default_credit_hours')) }}"
                            class="form-control @error('credit_hours') is-invalid @enderror" min="0.5" max="12"
                            step="0.5" required>

                        @error('credit_hours')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="total_marks" class="form-label">
                            Total Marks
                        </label>

                        <input type="number" name="total_marks" id="total_marks"
                            value="{{ old('total_marks', config('academic.marks.default_total')) }}"
                            class="form-control @error('total_marks') is-invalid @enderror" min="1" max="1000" step="1"
                            required>

                        @error('total_marks')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox"
                                role="switch" name="is_active" value="1" id="is_active"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Course is active and available for enrollment</label>
                        </div>
                        @error('is_active')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">
                            Course Description
                        </label>

                        <textarea name="description" id="description" rows="5"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Enter course description (maximum 100 words)">{{ old('description') }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Course
                        </button>
                    </div>

                </div>
            </form>

        </div>

    </div>
@endsection

@section('scripts')
    @include('course.partials.assessment-scheme-script')
@endsection
