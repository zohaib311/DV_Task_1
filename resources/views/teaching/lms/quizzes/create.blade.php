@extends('teaching.layout')

@section('heading', 'Create Objective MCQ Quiz')
@section('description', $offering->course_name . ' (' . $offering->course_code . ') · Section ' .
    ($offering->section?->name ?? 'A'))

@section('header-actions')
    <a href="{{ route('teaching.offerings.lms.index', $offering) }}" class="btn academic-header-btn">
        <i class="bi bi-arrow-left me-1"></i> Back to LMS Hub
    </a>
@endsection

@section('teaching-content')
    <form action="{{ route('teaching.offerings.lms.quizzes.store', $offering) }}" method="POST" id="quizForm">
        @csrf
        <!-- Quiz Settings Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-sliders me-2"></i>Quiz Information &amp; Schedule</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Quiz Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                        placeholder="e.g. Quiz 1: Variables, Types & Logic" value="{{ old('title') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Duration (Minutes) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" max="180"
                        value="{{ old('duration_minutes', 15) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sync to Assessment Component</label>
                    <select name="assessment_id" class="form-select">
                        <option value="">None (Standalone Practice Quiz)</option>
                        @foreach ($assessments as $ast)
                            <option value="{{ $ast->id }}" {{ old('assessment_id') == $ast->id ? 'selected' : '' }}>
                                {{ $ast->title }} (Max: {{ $ast->maximum }}m / Wt: {{ $ast->weight }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Instructions / Description</label>
                    <input type="text" name="description" class="form-control"
                        placeholder="e.g. Choose the single best answer for each question. You cannot pause the timer once started."
                        value="{{ old('description') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Available From <small
                            class="text-muted">(Optional)</small></label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Deadline / Close Time <small
                            class="text-muted">(Optional)</small></label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                </div>
                <div class="col-md-4 d-flex align-items-center mt-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1"
                            id="publishQuizCheck" checked>
                        <label class="form-check-label fw-semibold" for="publishQuizCheck">Publish for enrolled
                            students</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Builder Container -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check text-primary me-2"></i>MCQ Questions</h5>
                <small class="text-muted">Add questions with 4 options and mark the correct choice for auto-grading.</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="addQuestionBtn">
                <i class="bi bi-plus-circle me-1"></i> Add Another Question
            </button>
        </div>

        <div id="questionsContainer" class="d-flex flex-column gap-3 mb-4">
            <!-- Question 1 Template -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white question-card" data-index="0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary question-title"><i class="bi bi-patch-question me-1"></i> Question
                        1</h6>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 small fw-semibold">Points:</label>
                        <input type="number" name="questions[0][points]" class="form-control form-control-sm"
                            style="width: 70px;" value="1" min="0.5" step="0.5" required>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question-btn"
                            style="display: none;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Question Text <span class="text-danger">*</span></label>
                    <textarea name="questions[0][question_text]" rows="2" class="form-control"
                        placeholder="Enter the MCQ question here..." required>{{ old('questions.0.question_text') }}</textarea>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">A</span>
                            <input type="text" name="questions[0][option_a]" class="form-control"
                                placeholder="Option A" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">B</span>
                            <input type="text" name="questions[0][option_b]" class="form-control"
                                placeholder="Option B" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">C</span>
                            <input type="text" name="questions[0][option_c]" class="form-control"
                                placeholder="Option C" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">D</span>
                            <input type="text" name="questions[0][option_d]" class="form-control"
                                placeholder="Option D" required>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center gap-3">
                    <label class="form-label mb-0 fw-bold text-success">Correct Option:</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[0][correct_option]"
                            value="a" id="q0_a" checked>
                        <label class="form-check-label" for="q0_a">A</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[0][correct_option]"
                            value="b" id="q0_b">
                        <label class="form-check-label" for="q0_b">B</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[0][correct_option]"
                            value="c" id="q0_c">
                        <label class="form-check-label" for="q0_c">C</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[0][correct_option]"
                            value="d" id="q0_d">
                        <label class="form-check-label" for="q0_d">D</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary" id="addQuestionBtnBottom">
                <i class="bi bi-plus-lg me-1"></i> Add Another Question
            </button>
            <button type="submit" class="btn btn-primary fw-bold px-5 py-2">
                <i class="bi bi-check-circle-fill me-1"></i> Save &amp; Publish Quiz
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let questionIndex = 1;
            const container = document.getElementById('questionsContainer');

            function addQuestion() {
                const idx = questionIndex++;
                const card = document.createElement('div');
                card.className = 'card border-0 shadow-sm rounded-4 p-4 bg-white question-card';
                card.setAttribute('data-index', idx);
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-primary question-title"><i class="bi bi-patch-question me-1"></i> Question ${container.children.length + 1}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 small fw-semibold">Points:</label>
                            <input type="number" name="questions[${idx}][points]" class="form-control form-control-sm" style="width: 70px;" value="1" min="0.5" step="0.5" required>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-question-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question Text <span class="text-danger">*</span></label>
                        <textarea name="questions[${idx}][question_text]" rows="2" class="form-control" placeholder="Enter the MCQ question here..." required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-light">A</span>
                                <input type="text" name="questions[${idx}][option_a]" class="form-control" placeholder="Option A" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-light">B</span>
                                <input type="text" name="questions[${idx}][option_b]" class="form-control" placeholder="Option B" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-light">C</span>
                                <input type="text" name="questions[${idx}][option_c]" class="form-control" placeholder="Option C" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-light">D</span>
                                <input type="text" name="questions[${idx}][option_d]" class="form-control" placeholder="Option D" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex align-items-center gap-3">
                        <label class="form-label mb-0 fw-bold text-success">Correct Option:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="questions[${idx}][correct_option]" value="a" id="q${idx}_a" checked>
                            <label class="form-check-label" for="q${idx}_a">A</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="questions[${idx}][correct_option]" value="b" id="q${idx}_b">
                            <label class="form-check-label" for="q${idx}_b">B</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="questions[${idx}][correct_option]" value="c" id="q${idx}_c">
                            <label class="form-check-label" for="q${idx}_c">C</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="questions[${idx}][correct_option]" value="d" id="q${idx}_d">
                            <label class="form-check-label" for="q${idx}_d">D</label>
                        </div>
                    </div>
                `;
                container.appendChild(card);
                updateRemoveButtons();
            }

            function updateRemoveButtons() {
                const cards = container.querySelectorAll('.question-card');
                cards.forEach((card, index) => {
                    const title = card.querySelector('.question-title');
                    if (title) title.innerHTML =
                        `<i class="bi bi-patch-question me-1"></i> Question ${index + 1}`;
                    const removeBtn = card.querySelector('.remove-question-btn');
                    if (removeBtn) {
                        removeBtn.style.display = cards.length > 1 ? 'inline-block' : 'none';
                        removeBtn.onclick = function() {
                            card.remove();
                            updateRemoveButtons();
                        };
                    }
                });
            }

            document.getElementById('addQuestionBtn').addEventListener('click', addQuestion);
            document.getElementById('addQuestionBtnBottom').addEventListener('click', addQuestion);
            updateRemoveButtons();
        });
    </script>
@endsection
