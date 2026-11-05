@extends('student-portal.layout')

@section('heading', $quiz->title)
@section('description', 'Timed Objective Quiz · ' . $course->course_name . ' (' . $course->course_code . ')')

@section('student-content')
    <!-- Floating / Sticky Top Timer Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white sticky-top"
        style="top: 80px; z-index: 1020; border: 2px solid #e0e7ff !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark">{{ $quiz->title }}</h5>
                <small class="text-muted">{{ $quiz->questions->count() }} Questions · {{ $quiz->total_points }} Points
                    Total</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="small text-muted d-block fw-semibold">TIME REMAINING</span>
                    <span id="timerDisplay" class="badge bg-primary fs-5 px-3 py-2 rounded-pill font-monospace shadow-sm">
                        --:--
                    </span>
                </div>
                <button type="button" class="btn btn-success fw-bold px-4" onclick="confirmSubmit();">
                    <i class="bi bi-send-check-fill me-1"></i> Submit Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Quiz Taking Form -->
    <form id="quizForm" action="{{ route('student.courses.lms.quizzes.submit', [$course, $quiz]) }}" method="POST">
        @csrf
        <div class="d-flex flex-column gap-4 mb-4">
            @foreach ($quiz->questions as $index => $q)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white question-block">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-primary fs-6">Question {{ $index + 1 }} of
                            {{ $quiz->questions->count() }}</span>
                        <span class="badge bg-light text-dark border">{{ $q->points }} Point(s)</span>
                    </div>
                    <h5 class="fw-semibold text-dark mb-4" style="line-height: 1.5;">{{ $q->question_text }}</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label
                                class="d-flex align-items-center p-3 rounded-3 border bg-light w-100 cursor-pointer option-label"
                                for="q_{{ $q->id }}_a" style="cursor: pointer;">
                                <input class="form-check-input me-3 mt-0 fs-5" type="radio"
                                    name="answers[{{ $q->id }}]" value="a" id="q_{{ $q->id }}_a">
                                <div>
                                    <strong class="me-1">A)</strong> {{ $q->option_a }}
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="d-flex align-items-center p-3 rounded-3 border bg-light w-100 cursor-pointer option-label"
                                for="q_{{ $q->id }}_b" style="cursor: pointer;">
                                <input class="form-check-input me-3 mt-0 fs-5" type="radio"
                                    name="answers[{{ $q->id }}]" value="b" id="q_{{ $q->id }}_b">
                                <div>
                                    <strong class="me-1">B)</strong> {{ $q->option_b }}
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="d-flex align-items-center p-3 rounded-3 border bg-light w-100 cursor-pointer option-label"
                                for="q_{{ $q->id }}_c" style="cursor: pointer;">
                                <input class="form-check-input me-3 mt-0 fs-5" type="radio"
                                    name="answers[{{ $q->id }}]" value="c" id="q_{{ $q->id }}_c">
                                <div>
                                    <strong class="me-1">C)</strong> {{ $q->option_c }}
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="d-flex align-items-center p-3 rounded-3 border bg-light w-100 cursor-pointer option-label"
                                for="q_{{ $q->id }}_d" style="cursor: pointer;">
                                <input class="form-check-input me-3 mt-0 fs-5" type="radio"
                                    name="answers[{{ $q->id }}]" value="d" id="q_{{ $q->id }}_d">
                                <div>
                                    <strong class="me-1">D)</strong> {{ $q->option_d }}
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-5">
            <h6 class="fw-bold mb-2">Review All Your Answers Before Submitting</h6>
            <p class="text-muted small mb-3">Once submitted, your answers will be evaluated immediately and your score
                recorded.</p>
            <div>
                <button type="button" class="btn btn-success fw-bold px-5 py-2 fs-6 shadow-sm" onclick="confirmSubmit();">
                    <i class="bi bi-send-check-fill me-2"></i> Submit Answers Now
                </button>
            </div>
        </div>
    </form>

    <script>
        let remainingSeconds = {{ $remainingSeconds }};
        const timerDisplay = document.getElementById('timerDisplay');
        const quizForm = document.getElementById('quizForm');
        let isSubmitted = false;

        function updateTimer() {
            if (remainingSeconds <= 0) {
                timerDisplay.innerText = "00:00";
                timerDisplay.className = "badge bg-danger fs-5 px-3 py-2 rounded-pill font-monospace";
                if (!isSubmitted) {
                    isSubmitted = true;
                    alert("Time is up! Your answers are being submitted automatically.");
                    quizForm.submit();
                }
                return;
            }

            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            timerDisplay.innerText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (remainingSeconds < 60) {
                timerDisplay.className = "badge bg-danger fs-5 px-3 py-2 rounded-pill font-monospace";
            } else if (remainingSeconds < 180) {
                timerDisplay.className = "badge bg-warning text-dark fs-5 px-3 py-2 rounded-pill font-monospace";
            }

            remainingSeconds--;
        }

        function confirmSubmit() {
            if (confirm("Are you sure you want to finish and submit your quiz now?")) {
                isSubmitted = true;
                quizForm.submit();
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
@endsection
