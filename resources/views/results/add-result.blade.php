@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/addstudent.css') }}">
    <style>
        .filter-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 24px;
        }

        .students-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        .spinner-container {
            display: none;
            padding: 40px;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1">Add Student Result</h3>
                <p class="text-muted mb-0">Select department and section to view students and record results.</p>
            </div>
            <a href="{{ route('allResults') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Results
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Top Filter Section: Department (Left) and Section (Right) --}}
        <div class="filter-card mb-4">
            <h5 class="fw-bold mb-3 text-primary">
                <i class="bi bi-funnel-fill me-2"></i>Select Department & Section
            </h5>
            <div class="row">
                {{-- Department Dropdown (Left Side) --}}
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="department_id" class="form-label fw-semibold">1. Department</label>
                    <select id="department_id" class="form-select form-select-lg">
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section Dropdown (Right Side, Initially Disabled) --}}
                <div class="col-md-6">
                    <label for="section_id" class="form-label fw-semibold">2. Section</label>
                    <select id="section_id" class="form-select form-select-lg" disabled>
                        <option value="">-- Select Department First --</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Students List Card Section --}}
        <div class="students-card">
            <h5 class="fw-bold mb-3 text-primary">
                <i class="bi bi-people-fill me-2"></i>Students List
            </h5>

            {{-- Initial / Empty State Message --}}
            <div id="initial_state_msg" class="alert alert-light text-center border py-4 mb-0">
                <i class="bi bi-arrow-up-circle fs-3 text-primary d-block mb-2"></i>
                <span class="fw-semibold text-secondary">Select Department First, Then Select Section
                    For Students List</span>
            </div>

            {{-- Loading Spinner --}}
            <div id="loading_spinner" class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted mb-0">Students Loading...</p>
            </div>

            {{-- Table Container --}}
            <div id="students_table_wrapper" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th width="140">Student ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th width="160" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="students_table_body">
                            {{-- Rows populated via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- No Students Found Message --}}
            <div id="no_students_msg" class="alert alert-warning text-center py-4 mb-0" style="display: none;">
                <i class="bi bi-exclamation-triangle fs-4 me-2"></i> No Students Founds
            </div>
        </div>

    </div>

    {{-- Add Result Modal --}}
    <div class="modal fade" id="addResultModal" tabindex="-1" aria-labelledby="addResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-header-title mb-0 fw-bold" id="addResultModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add Student Result
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('addResult') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">

                        <input type="hidden" name="student_id" id="modal_student_id">
                        <input type="hidden" name="section_id" id="modal_section_id">

                        <div class="p-3 mb-4 rounded-3 bg-light border">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <small class="text-muted d-block">Student Name:</small>
                                    <strong id="modal_student_name" class="fs-6 text-dark">-</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Student Email:</small>
                                    <strong id="modal_student_email" class="fs-6 text-dark">-</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label fw-semibold">Course</label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="percentage" class="form-label fw-semibold">Percentage (%)</label>
                                <input type="number" step="0.01" name="percentage" id="percentage"
                                    class="form-control" placeholder="e.g. 85.50" required>
                            </div>

                            <div class="col-md-4">
                                <label for="gpa" class="form-label fw-semibold">GPA</label>
                                <input type="number" step="0.01" name="gpa" id="gpa" class="form-control"
                                    placeholder="e.g. 3.70" required>
                            </div>

                            <div class="col-md-4">
                                <label for="cgpa" class="form-label fw-semibold">CGPA</label>
                                <input type="number" step="0.01" name="cgpa" id="cgpa" class="form-control"
                                    placeholder="e.g. 3.50" required>
                            </div>

                            <div class="col-md-4">
                                <label for="grade" class="form-label fw-semibold">Grade</label>
                                <input type="text" name="grade" id="grade" class="form-control"
                                    placeholder="e.g. A, B+" required>
                            </div>

                            <div class="col-md-12">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Submit Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/result/add-result-filter.js') }}"></script>
@endsection
