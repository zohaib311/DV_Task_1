@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">

        <div class="add__form mx-auto">


            <div class="edit__header">
                <div>
                    <h2>Update Course</h2>
                </div>

            </div>


            @if (session('success'))
                <div class="alert alert-success" id="success-message">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif


            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Please fix the following errors:
                    </div>

                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form action="{{ route('updateCourse', $course->id) }}" method="POST" enctype="multipart/form-data" data-assessment-form>

                @csrf
                @method('PUT')

                <div class="row g-4">


                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">
                            Course Code
                        </label>

                        <input type="text" name="code" id="code" value="{{ old('code', $course->code) }}"
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
                            <strong id="assessment_scheme_total">0 / {{ old('total_marks', $course->total_marks) }}</strong>
                        </div>
                        @error('assessment_scheme')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="attendance_marks" class="form-label">Attendance Marks</label>
                        <input type="number" name="attendance_marks" id="attendance_marks"
                            value="{{ old('attendance_marks', $course->attendance_marks) }}"
                            class="form-control @error('attendance_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('attendance_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="mid_marks" class="form-label">Midterm Marks</label>
                        <input type="number" name="mid_marks" id="mid_marks"
                            value="{{ old('mid_marks', $course->mid_marks) }}"
                            class="form-control @error('mid_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('mid_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="final_marks" class="form-label">Final Marks</label>
                        <input type="number" name="final_marks" id="final_marks"
                            value="{{ old('final_marks', $course->final_marks) }}"
                            class="form-control @error('final_marks') is-invalid @enderror" min="0" max="1000" step="1" required>
                        @error('final_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            Course Name
                        </label>

                        <input type="text" name="name" id="name" value="{{ old('name', $course->name) }}"
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
                            value="{{ old('credit_hours', $course->credit_hours) }}"
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
                            value="{{ old('total_marks', $course->total_marks) }}"
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
                                {{ old('is_active', $course->is_active) ? 'checked' : '' }}>
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
                            placeholder="Enter course description (maximum 100 words)">{{ old('description', $course->description) }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>



                    <div class="col-12">

                        <div class="form__actions">

                            <a href="{{ route('allCourses') }}" class="cancel__btn">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back
                            </a>

                            <button type="submit" class="update__btn">
                                <i class="bi bi-check2-circle me-1"></i>
                                Update Course
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


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
@endsection

@section('scripts')
    @include('course.partials.assessment-scheme-script')
@endsection
