@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enrollment/enrollment.css') }}">
@endsection

@section('content')
    <div class="container add__form_cont py-5">
        <div class="add__form enrollment-form mx-auto">
            <div class="enrollment-form-header">
                <div class="enrollment-form-icon"><i class="bi bi-journal-plus"></i></div>
                <div>
                    <h2>New Semester Enrollment</h2>
                    <p>Assign a student to a semester and preserve their course record.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success enrollment-errors border-success text-success" role="status">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger enrollment-errors" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('addEnrollment') }}" method="POST" novalidate>
                @csrf

                <section class="enrollment-form-section">
                    <div class="enrollment-section-title">
                        <i class="bi bi-person-vcard"></i>
                        <div>
                            <h5>Student placement</h5>
                            <p>Select the student. Their current department and section are saved as a history snapshot.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="student_id" class="form-label">Student</label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">Select student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}"
                                        data-registration="{{ $student->registration_no }}"
                                        data-department="{{ $student->department->name ?? 'Not assigned' }}"
                                        data-section="{{ $student->section->name ?? 'Not assigned' }}"
                                        {{ old('student_id', request('student_id')) == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->registration_no ?? 'No registration no.' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" id="academic_year"
                                value="{{ old('academic_year', $defaultAcademicYear) }}"
                                class="form-control @error('academic_year') is-invalid @enderror" placeholder="2026-2027"
                                pattern="\d{4}-\d{4}" required>
                            @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="student-placement-summary" id="studentPlacementSummary" aria-live="polite">
                                <i class="bi bi-person-badge"></i>
                                <span>Select a student to view their registration number, department and section.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="enrollment-form-section">
                    <div class="enrollment-section-title">
                        <i class="bi bi-mortarboard"></i>
                        <div>
                            <h5>Semester & courses</h5>
                            <p>Only active courses are available. Credit hours and marks are copied into the enrollment history.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                <option value="">Select semester</option>
                                @for ($semester = 1; $semester <= 8; $semester++)
                                    <option value="Semester {{ $semester }}" {{ old('semester') === "Semester $semester" ? 'selected' : '' }}>
                                        Semester {{ $semester }}
                                    </option>
                                @endfor
                            </select>
                            @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <label class="form-label d-block mt-4 mb-2">Assign active courses</label>
                    <div class="enrollment-course-grid">
                        @php($selectedCourses = old('course_ids', []))
                        @forelse ($courses as $course)
                            <label class="enrollment-course-option">
                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}"
                                    {{ in_array($course->id, $selectedCourses) ? 'checked' : '' }}>
                                <span class="enrollment-course-check"><i class="bi bi-check-lg"></i></span>
                                <span>
                                    <strong>{{ $course->name }}</strong>
                                    <small>{{ $course->code }} · {{ number_format((float) $course->credit_hours, 1) }} Cr. Hrs · {{ $course->total_marks }} marks</small>
                                </span>
                            </label>
                        @empty
                            <p class="text-muted mb-0">No active courses are available. Add or activate courses first.</p>
                        @endforelse
                    </div>
                    @error('course_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    @error('course_ids.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </section>

                <div class="form__actions mt-4">
                    <a href="{{ route('allEnrollments') }}" class="cancel__btn">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="update__btn">
                        <i class="bi bi-journal-check me-1"></i> Create Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentSelect = document.getElementById('student_id');
            const summary = document.getElementById('studentPlacementSummary');

            function updateStudentSummary() {
                const option = studentSelect.options[studentSelect.selectedIndex];
                if (!option || !option.value) {
                    summary.innerHTML = '<i class="bi bi-person-badge"></i><span>Select a student to view their registration number, department and section.</span>';
                    return;
                }

                const icon = document.createElement('i');
                icon.className = 'bi bi-person-badge';
                const details = document.createElement('span');
                const name = document.createElement('strong');
                const meta = document.createElement('small');

                name.textContent = option.textContent.trim();
                meta.textContent = `Reg. No: ${option.dataset.registration || 'N/A'} · ${option.dataset.department} · Section ${option.dataset.section}`;
                details.append(name, meta);
                summary.replaceChildren(icon, details);
            }

            studentSelect.addEventListener('change', updateStudentSummary);
            updateStudentSummary();
        });
    </script>
@endsection
