@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/edit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form mx-auto">

            <div class="edit__header mb-4">
                <div>
                    <h2>Update Student Result</h2>
                    <p class="text-muted small mb-0">Modify student grade, GPA, and course details</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success" id="success-message">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Please fix the following errors:
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('updateResult', $result->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select name="student_id" id="student_id"
                            class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}"
                                    {{ old('student_id', $result->student_id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="course_id" class="form-label">Course</label>
                        <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror"
                            required>
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}"
                                    {{ old('course_id', $result->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="section_id" class="form-label">Section & Department</label>
                        <select name="section_id" id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}"
                                    {{ old('section_id', $result->section_id) == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} - {{ $section->department->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="percentage" class="form-label">Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" id="percentage"
                            value="{{ old('percentage', $result->percentage) }}"
                            class="form-control @error('percentage') is-invalid @enderror" placeholder="e.g. 85.50"
                            required>
                        @error('percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="gpa" class="form-label">GPA</label>
                        <input type="number" step="0.01" name="gpa" id="gpa"
                            value="{{ old('gpa', $result->gpa) }}" class="form-control @error('gpa') is-invalid @enderror"
                            placeholder="e.g. 3.70" required>
                        @error('gpa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="cgpa" class="form-label">CGPA</label>
                        <input type="number" step="0.01" name="cgpa" id="cgpa"
                            value="{{ old('cgpa', $result->cgpa) }}"
                            class="form-control @error('cgpa') is-invalid @enderror" placeholder="e.g. 3.50" required>
                        @error('cgpa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="grade" class="form-label">Grade</label>
                        <input type="text" name="grade" id="grade" value="{{ old('grade', $result->grade) }}"
                            class="form-control @error('grade') is-invalid @enderror" placeholder="e.g. A, B+" required>
                        @error('grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror"
                            required>
                            <option value="Pass" {{ old('status', $result->status) == 'Pass' ? 'selected' : '' }}>Pass
                            </option>
                            <option value="Fail" {{ old('status', $result->status) == 'Fail' ? 'selected' : '' }}>Fail
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form__actions">
                            <a href="{{ route('allResults') }}" class="cancel__btn">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back
                            </a>

                            <button type="submit" class="update__btn">
                                <i class="bi bi-check2-circle me-1"></i>
                                Update Result
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
