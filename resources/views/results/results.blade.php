@extends('welcome')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    <link rel="stylesheet" href="{{ asset('css/universal/action-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/result/result-model.css') }}">
    <link rel="stylesheet" href="{{ asset('css/result/add-result.css') }}">
    <link rel="stylesheet" href="{{ asset('css/result/results.css') }}">
@endsection

@section('content')
    <div class="results-page">
        <div class="results-main-card">
            <header class="results-page-header">
                <span class="results-page-kicker"><i class="bi bi-journal-check"></i> Academic Records</span>
                <h1>Results Management</h1>
                <p>Select a department and section to view, add, or update student results.</p>
            </header>

            <div class="results-main-card-body">

                @if ($errors->any())
                    <div class="alert alert-danger results-alert" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success results-alert" id="success-message" role="status">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    </div>
                    <script>
                        setTimeout(function() {
                            const message = document.getElementById('success-message');
                            if (message) {
                                message.style.transition = 'opacity 0.5s ease';
                                message.style.opacity = '0';
                                setTimeout(() => message.remove(), 500);
                            }
                        }, 3000);
                    </script>
                @endif

                <section class="results-filter-section" aria-labelledby="results-filter-heading">
                    <div class="results-section-heading">
                        <div>
                            <span class="results-section-eyebrow">Filter records</span>
                            <h2 id="results-filter-heading">Choose a class</h2>
                        </div>
                        <span class="results-step-hint"><i class="bi bi-sliders"></i> Required selection</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg">
                            <label for="department_id" class="form-label"><span>01</span> Department</label>
                            <div class="results-select-wrap">
                                <i class="bi bi-building"></i>
                                <select id="department_id" class="form-select">
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg">
                            <label for="section_id" class="form-label"><span>02</span> Section</label>
                            <div class="results-select-wrap">
                                <i class="bi bi-diagram-3"></i>
                                <select id="section_id" class="form-select" disabled>
                                    <option value="">-- Select Department First --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg">
                            <label for="academic_year_filter" class="form-label"><span>03</span> Academic Year</label>
                            <div class="results-select-wrap">
                                <i class="bi bi-calendar3"></i>
                                <select id="academic_year_filter" class="form-select" disabled>
                                    <option value="">All Academic Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg">
                            <label for="semester_filter" class="form-label"><span>04</span> Semester</label>
                            <div class="results-select-wrap">
                                <i class="bi bi-layers"></i>
                                <select id="semester_filter" class="form-select" disabled>
                                    <option value="">All Semesters</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg">
                            <label for="result_status_filter" class="form-label"><span>05</span> Result Status</label>
                            <div class="results-select-wrap">
                                <i class="bi bi-funnel"></i>
                                <select id="result_status_filter" class="form-select" disabled>
                                    <option value="all">All Result States</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="results-list-section" aria-labelledby="results-list-heading">
                    <div class="results-section-heading results-list-heading">
                        <div>
                            <span class="results-section-eyebrow">Student records</span>
                            <h2 id="results-list-heading">Students & results</h2>
                        </div>
                        <span class="results-list-status"><i class="bi bi-lightning-charge"></i> Live records</span>
                    </div>

                    <div id="initial_state_msg" class="results-empty-state">
                        <i class="bi bi-arrow-up-circle"></i>
                        <strong>Select a department and section</strong>
                        <span>Students and their results will appear here.</span>
                    </div>

                    <div id="loading_spinner" class="spinner-container">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted mb-0 fw-medium">Loading student records…</p>
                    </div>

                    <div id="students_table_wrapper" style="display: none;">
                        <div class="students-table-responsive results-table-scroll">
                            <table class="students-table table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="120">REG NO</th>
                                        <th>Student Name</th>
                                        <th>Email</th>
                                        <th>Semester</th>
                                        <th>Courses</th>
                                        <th>Percentage</th>
                                        <th>SGPA</th>
                                        <th>CGPA</th>
                                        <th>Status</th>
                                        <th width="150" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="students_table_body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="no_students_msg" class="results-empty-state results-empty-state-warning"
                        style="display: none;">
                        <i class="bi bi-people"></i>
                        <strong>No students found</strong>
                        <span>This section does not have any students yet.</span>
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- 1. VIEW RESULT MODAL --}}
    <div class="modal fade result-detail-modal" id="viewResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-person-fill fs-5"></i>
                        <h5 class="modal-title mb-0">Student Result Detail</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Student Profile Header --}}
                    <div class="student-profile-card">
                        <img id="view_student_image" src="" alt="Student Avatar" class="student-modal-avatar">
                        <div class="student-info-meta">
                            <h4 id="view_student_name">-</h4>
                            <p><i class="bi bi-envelope me-1"></i> <span id="view_student_email">-</span></p>
                            <div class="student-meta-pills">
                                <span class="student-meta-pill">
                                    <i class="bi bi-telephone me-1"></i> <span id="view_student_phone">-</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Metrics Grid --}}
                    <div class="result-metrics-grid">
                        <div class="metric-card percentage-card">
                            <label>Percentage</label>
                            <div class="metric-value" id="view_percentage">-</div>
                        </div>

                        <div class="metric-card gpa-card">
                            <label>GPA</label>
                            <div class="metric-value" id="view_gpa">-</div>
                        </div>

                        <div class="metric-card cgpa-card">
                            <label>CGPA</label>
                            <div class="metric-value" id="view_cgpa">-</div>
                        </div>

                        <div class="metric-card grade-card">
                            <label>Grade</label>
                            <div class="metric-value" id="view_grade">-</div>
                        </div>
                    </div>

                    {{-- Detailed Table --}}
                    <div class="result-details-box">
                        <table class="table result-details-table align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th>Result Record ID</th>
                                    <td class="fw-semibold" id="view_record_id">-</td>
                                </tr>
                                <tr>
                                    <th>Course Title</th>
                                    <td>
                                        <span class="fw-semibold" id="view_course_name">-</span>
                                        <small class="text-muted" id="view_course_code"></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Section / Department</th>
                                    <td id="view_section_name">-</td>
                                </tr>
                                <tr>
                                    <th>Academic Status</th>
                                    <td>
                                        <span id="view_status_badge" class="status-badge">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0 justify-content-center py-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-3 fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade semester-sheet-modal" id="semesterResultSheetModal" tabindex="-1"
        aria-labelledby="semesterResultSheetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="sheet-eyebrow"><i class="bi bi-mortarboard-fill"></i> Academic record</span>
                        <h5 class="modal-title" id="semesterResultSheetModalLabel">Semester Result Sheet</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="semester_sheet_loading" class="semester-sheet-loading">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span>Loading academic record…</span>
                    </div>
                    <div id="semester_sheet_content" hidden>
                        <section class="semester-sheet-student">
                            <div>
                                <span class="summary-kicker">Student academic profile</span>
                                <strong id="sheet_student_name">—</strong>
                            </div>
                            <dl>
                                <div><dt>Registration No.</dt><dd id="sheet_registration_no">—</dd></div>
                                <div><dt>Department</dt><dd id="sheet_department">—</dd></div>
                                <div><dt>Section</dt><dd id="sheet_section">—</dd></div>
                            </dl>
                        </section>

                        <section id="semester_sheet_record" class="semester-sheet-record" hidden>
                            <div class="semester-sheet-record-heading">
                                <div>
                                    <span class="summary-kicker">Semester result</span>
                                    <h6 id="sheet_semester">—</h6>
                                    <small id="sheet_academic_year">—</small>
                                </div>
                                <span id="sheet_status" class="semester-status is-draft">—</span>
                            </div>
                            <div class="semester-sheet-metrics">
                                <div><span>Percentage</span><strong id="sheet_percentage">—</strong></div>
                                <div><span>SGPA</span><strong id="sheet_sgpa">—</strong></div>
                                <div><span>CGPA</span><strong id="sheet_cgpa">—</strong></div>
                                <div><span>Record state</span><strong id="sheet_record_state">—</strong></div>
                            </div>
                            <div class="semester-sheet-table-wrap">
                                <table class="semester-sheet-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th><th>Cr.</th><th>Attendance</th><th>Midterm</th><th>Final</th>
                                            <th>Obtained</th><th>%</th><th>Grade</th><th>GP</th><th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sheet_course_rows"></tbody>
                                </table>
                            </div>
                        </section>

                        <section class="semester-history-section">
                            <div class="semester-history-heading">
                                <div>
                                    <span class="summary-kicker">Academic history</span>
                                    <h6 id="sheet_history_heading">All semester records</h6>
                                </div>
                                <span id="sheet_history_count" class="course-count">0 semesters</span>
                            </div>
                            <div class="semester-history-table-wrap">
                                <table class="semester-history-table">
                                    <thead><tr><th>Semester</th><th>Academic Year</th><th>SGPA</th><th>CGPA</th><th>Status</th><th></th></tr></thead>
                                    <tbody id="sheet_history_rows"></tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <x-result.add-semester-result-drawer />

@endsection

@section('scripts')
    <script src="{{ asset('js/result/results-filter.js') }}"></script>
@endsection
