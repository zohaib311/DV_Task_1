@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form mx-auto">
            <h2 class="text-center mb-4">Add Student Result</h2>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('addResult') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select name="student_id" id="student_id"
                            class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}"
                                    {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="course_id" class="form-label">Course</label>
                        <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror"
                            required>
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="section_id" class="form-label">Section & Department</label>
                        <select name="section_id" id="section_id"
                            class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}"
                                    {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }} - {{ $section->department->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="percentage" class="form-label">Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" id="percentage"
                            value="{{ old('percentage') }}" class="form-control" placeholder="e.g. 85.50" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="gpa" class="form-label">GPA</label>
                        <input type="number" step="0.01" name="gpa" id="gpa" value="{{ old('gpa') }}"
                            class="form-control" placeholder="e.g. 3.70" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="cgpa" class="form-label">CGPA</label>
                        <input type="number" step="0.01" name="cgpa" id="cgpa" value="{{ old('cgpa') }}"
                            class="form-control" placeholder="e.g. 3.50" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="grade" class="form-label">Grade</label>
                        <input type="text" name="grade" id="grade" value="{{ old('grade') }}"
                            class="form-control" placeholder="e.g. A, B+" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Pass" {{ old('status') == 'Pass' ? 'selected' : '' }}>Pass</option>
                            <option value="Fail" {{ old('status') == 'Fail' ? 'selected' : '' }}>Fail</option>
                        </select>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary w-100">Add Result</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
