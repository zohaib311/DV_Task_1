@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/result/add-result.css') }}">
@endsection

@section('content')
    <div class="add-result-container">

        <div class="page-header-card">
            <div>
                <h3 class="page-header-title">Add Student Result</h3>
                <p class="page-header-sub">Select department and section to view students and record results.</p>
            </div>
            <a href="{{ route('allResults') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Results
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="filter-card">
            <h5 class="filter-card-title">
                <i class="bi bi-funnel-fill"></i>Select Department & Section
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="department_id" class="form-label">1. Department</label>
                    <select id="department_id" class="form-select">
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="section_id" class="form-label">2. Section</label>
                    <select id="section_id" class="form-select" disabled>
                        <option value="">-- Select Department First --</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="students-card">
            <h5 class="students-card-title">
                <i class="bi bi-people-fill"></i>Students List
            </h5>

            <div id="initial_state_msg" class="alert alert-light text-center border py-4 mb-0 rounded-3">
                <i class="bi bi-arrow-up-circle fs-3 text-primary d-block mb-2"></i>
                <span class="fw-semibold text-secondary">Pehly Department select krain, phr Section select krain
                    students ki list dekhny kay liye.</span>
            </div>

            <div id="loading_spinner" class="spinner-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted mb-0 fw-medium">Students load ho rahy hain...</p>
            </div>

            <div id="students_table_wrapper" style="display: none;">
                <div class="students-table-responsive">
                    <table class="students-table table align-middle">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th width="140">Student ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th width="160" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="students_table_body">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="no_students_msg" class="alert alert-warning border-0 text-center py-4 mb-0 rounded-3"
                style="display: none;">
                <i class="bi bi-exclamation-triangle fs-4 me-2"></i> Is Section main koi student maujood nahi hai.
            </div>
        </div>

    </div>

    <div class="modal fade result-modal" id="addResultModal" tabindex="-1" aria-labelledby="addResultModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addResultModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add Student Result
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('addResult') }}" method="POST">
                    @csrf
                    <div class="modal-body">

                        <input type="hidden" name="student_id" id="modal_student_id">
                        <input type="hidden" name="section_id" id="modal_section_id">

                        <div class="student-info-box">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <small class="text-muted d-block fw-semibold">Student Name:</small>
                                    <strong id="modal_student_name" class="fs-6 text-dark">-</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block fw-semibold">Student Email:</small>
                                    <strong id="modal_student_email" class="fs-6 text-dark">-</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">Course</label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="percentage" class="form-label">Percentage (%)</label>
                                <input type="number" step="0.01" name="percentage" id="percentage"
                                    class="form-control" placeholder="e.g. 85.50" required>
                            </div>

                            <div class="col-md-4">
                                <label for="gpa" class="form-label">GPA</label>
                                <input type="number" step="0.01" name="gpa" id="gpa" class="form-control"
                                    placeholder="e.g. 3.70" required>
                            </div>

                            <div class="col-md-4">
                                <label for="cgpa" class="form-label">CGPA</label>
                                <input type="number" step="0.01" name="cgpa" id="cgpa" class="form-control"
                                    placeholder="e.g. 3.50" required>
                            </div>

                            <div class="col-md-4">
                                <label for="grade" class="form-label">Grade</label>
                                <input type="text" name="grade" id="grade" class="form-control"
                                    placeholder="e.g. A, B+" required>
                            </div>

                            <div class="col-md-12">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-3"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit-result">Submit Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/result/add-result-filter.js') }}"></script>
@endsection
