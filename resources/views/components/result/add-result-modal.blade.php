<div class="modal fade result-modal" id="addResultModal" tabindex="-1" aria-labelledby="addResultModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="addResultModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Student Result
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>

            <form action="{{ route('addResult') }}" method="POST">

                @csrf

                <div class="modal-body">

                    <input type="hidden" name="student_id" id="modal_student_id">
                    <input type="hidden" name="section_id" id="modal_section_id">

                    <div class="student-info-box">

                        <div class="row">

                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted d-block fw-semibold">
                                    Student Name:
                                </small>

                                <strong id="modal_student_name" class="fs-6 text-dark">
                                    -
                                </strong>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block fw-semibold">
                                    Student Email:
                                </small>

                                <strong id="modal_student_email" class="fs-6 text-dark">
                                    -
                                </strong>
                            </div>

                        </div>

                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label for="course_id" class="form-label">
                                Course
                            </label>

                            <select name="course_id" id="course_id" class="form-select" required>

                                <option value="">
                                    -- Select Course --
                                </option>

                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">
                                        {{ $course->name }} ({{ $course->code }})
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label for="percentage" class="form-label">
                                Percentage (%)
                            </label>

                            <input type="number" step="0.01" name="percentage" id="percentage" class="form-control"
                                placeholder="e.g. 85.50" required>

                        </div>

                        <div class="col-md-4">

                            <label for="gpa" class="form-label">
                                GPA
                            </label>

                            <input type="number" step="0.01" name="gpa" id="gpa" class="form-control"
                                placeholder="e.g. 3.70" required>

                        </div>

                        <div class="col-md-4">

                            <label for="cgpa" class="form-label">
                                CGPA
                            </label>

                            <input type="number" step="0.01" name="cgpa" id="cgpa" class="form-control"
                                placeholder="e.g. 3.50" required>

                        </div>

                        <div class="col-md-4">

                            <label for="grade" class="form-label">
                                Grade
                            </label>

                            <input type="text" name="grade" id="grade" class="form-control"
                                placeholder="e.g. A, B+" required>

                        </div>

                        <div class="col-md-12">

                            <label for="status" class="form-label">
                                Status
                            </label>

                            <select name="status" id="status" class="form-select" required>

                                <option value="Pass">
                                    Pass
                                </option>

                                <option value="Fail">
                                    Fail
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-submit-result">
                        Submit Result
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
