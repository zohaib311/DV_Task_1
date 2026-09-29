<div class="offcanvas offcanvas-end result-drawer semester-result-drawer" tabindex="-1" id="semesterResultDrawer"
    aria-labelledby="semesterResultDrawerLabel" data-student-enrollments-url="{{ url('/result/student') }}"
    data-enrollment-url="{{ url('/result/semester-enrollment') }}" data-store-url="{{ route('result.semester.store') }}"
    data-result-url="{{ url('/result/semester-result') }}"
    data-grade-scale='@json(config('academic.grade_scale'))'
    data-allow-drafts="{{ config('academic.results.allow_drafts') ? 'true' : 'false' }}"
    data-allow-published-edits="{{ config('academic.results.allow_published_result_edits') ? 'true' : 'false' }}"
    data-final-minimum-enabled="{{ config('academic.assessment.final_minimum.enabled') ? 'true' : 'false' }}"
    data-final-minimum-percentage="{{ config('academic.assessment.final_minimum.minimum_percentage') }}">
    <div class="drawer-header">
        <div>
            <span class="drawer-eyebrow"><i class="bi bi-mortarboard-fill"></i> Academic record</span>
            <h5 id="semesterResultDrawerLabel">Add Semester Result</h5>
        </div>
        <div class="drawer-header-actions">
            <span class="drawer-action-state" id="semester_result_action_state">Draft</span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>

    <form id="semesterResultForm" class="d-flex flex-column flex-grow-1 m-0" novalidate>
        @csrf
        <input type="hidden" id="semester_result_student_id" name="student_id">
        <input type="hidden" id="semester_result_enrollment_id" name="enrollment_id">

        <div class="drawer-body">
            <div id="semester_result_errors" class="semester-result-errors" role="alert" hidden></div>

            <div id="semester_result_loading" class="semester-result-loading">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span>Academic record load ho raha hai…</span>
            </div>

            <div id="semester_result_content" hidden>
                <section class="semester-student-summary" aria-label="Student academic summary">
                    <div class="semester-student-heading">
                        <span class="summary-kicker">Student academic summary</span>
                        <strong id="semester_result_student_name">—</strong>
                    </div>
                    <dl class="semester-summary-grid">
                        <div><dt>Registration No.</dt><dd id="semester_result_registration_no">—</dd></div>
                        <div><dt>Department</dt><dd id="semester_result_department">—</dd></div>
                        <div><dt>Section</dt><dd id="semester_result_section">—</dd></div>
                        <div><dt>Academic Year</dt><dd id="semester_result_academic_year">—</dd></div>
                    </dl>
                </section>

                <div class="semester-picker-row">
                    <label for="semester_result_enrollment_select" class="form-label">Semester enrollment</label>
                    <select id="semester_result_enrollment_select" class="form-select"></select>
                    <small id="semester_result_enrollment_help">Choose an enrollment to load its assigned courses.</small>
                </div>

                <section id="semester_result_saved_summary" class="semester-saved-summary" aria-label="Saved result summary" hidden>
                    <div>
                        <span class="summary-kicker">Saved result</span>
                        <strong id="semester_result_saved_status">—</strong>
                    </div>
                    <span id="semester_result_saved_note">Existing calculated result is loaded below.</span>
                </section>

                <div id="semester_result_locked" class="semester-result-locked" hidden></div>

                <section class="semester-marks-section" aria-labelledby="semester_marks_heading">
                    <div class="semester-marks-heading">
                        <div>
                            <span class="summary-kicker">Course assessment</span>
                            <h6 id="semester_marks_heading">Enter obtained marks</h6>
                        </div>
                        <span id="semester_result_course_count" class="course-count">0 courses</span>
                    </div>
                    <div class="semester-marks-table-wrap">
                        <table class="semester-marks-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course</th>
                                    <th>Code</th>
                                    <th>Cr.</th>
                                    <th class="marks-input-heading">Attendance</th>
                                    <th class="marks-input-heading">Midterm</th>
                                    <th class="marks-input-heading">Final</th>
                                    <th>Obtained</th>
                                    <th>%</th>
                                    <th>Grade</th>
                                    <th>GP</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="semester_result_courses_body"></tbody>
                        </table>
                    </div>
                </section>

                <section class="semester-live-summary" aria-label="Live result summary">
                    <div class="semester-live-summary-heading">
                        <span class="summary-kicker">Live calculation</span>
                        <span id="semester_result_summary_status" class="semester-status is-draft">Draft</span>
                    </div>
                    <div class="semester-summary-metrics">
                        <div><span>Total marks</span><strong id="semester_result_total_marks">— / —</strong></div>
                        <div><span>Percentage</span><strong id="semester_result_percentage">—</strong></div>
                        <div><span>SGPA</span><strong id="semester_result_sgpa">—</strong></div>
                        <div><span>CGPA</span><strong id="semester_result_cgpa">—</strong></div>
                    </div>
                </section>
            </div>
        </div>

        <div class="drawer-footer semester-result-footer">
            <button type="button" class="btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
            @if (config('academic.results.allow_drafts'))
                <button type="submit" id="semester_result_draft_button" class="btn-save-draft" data-result-action="draft">
                    <i class="bi bi-save2"></i> Save Draft
                </button>
            @endif
            <button type="submit" id="semester_result_publish_button" class="btn-submit-result" data-result-action="publish">
                <i class="bi bi-check2-circle"></i> Save &amp; Publish
            </button>
        </div>
    </form>
</div>
