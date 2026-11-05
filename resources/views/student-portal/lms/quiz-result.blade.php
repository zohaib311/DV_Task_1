@extends('student-portal.layout')

@section('heading', $quiz->title . ' — Result & Feedback')
@section('description', $course->course_name . ' (' . $course->course_code . ')')

@section('header-actions')
    <a href="{{ route('student.courses.lms', $course) }}" class="btn academic-header-btn">
        <i class="bi bi-arrow-left me-1"></i> Back to Course LMS
    </a>
@endsection

@section('student-content')
    @php
        $percentage = $attempt->percentage();
        $isPassed = $percentage >= 50;
        $userAnswers = $attempt->answers ?? [];
    @endphp

    <!-- Result Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 text-center bg-white"
        style="background: linear-gradient(135deg, {{ $isPassed ? '#f0fdf4 0%, #dcfce7 100%' : '#fef2f2 0%, #fee2e2 100%' }}); border: 1px solid {{ $isPassed ? '#86efac' : '#fca5a5' }} !important;">
        <div class="mb-3">
            @if ($isPassed)
                <i class="bi bi-patch-check-fill text-success" style="font-size: 3.5rem;"></i>
            @else
                <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 3.5rem;"></i>
            @endif
        </div>
        <h3 class="fw-bold mb-1 {{ $isPassed ? 'text-success' : 'text-danger' }}">
            {{ $isPassed ? 'Congratulations! Quiz Passed' : 'Quiz Completed' }}
        </h3>
        <p class="text-muted mb-3">Your objective quiz has been automatically evaluated.</p>
        <div class="display-5 fw-bold mb-2 text-dark">
            {{ $attempt->score_obtained }} <span class="fs-4 text-muted">/ {{ $attempt->total_points }} Points</span>
        </div>
        <div class="mb-3">
            <span class="badge {{ $isPassed ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2 rounded-pill">
                {{ $percentage }}% Score
            </span>
            <span class="badge bg-light text-dark border fs-6 px-3 py-2 rounded-pill ms-2">
                Status: {{ ucfirst(str_replace('_', ' ', $attempt->status)) }}
            </span>
        </div>
        <small class="text-muted">
            Started: {{ $attempt->started_at->format('d M Y, h:i A') }} ·
            Finished: {{ $attempt->completed_at ? $attempt->completed_at->format('d M Y, h:i A') : '—' }}
        </small>
    </div>

    <!-- Question by Question Breakdown -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-card-checklist text-primary me-2"></i>Questions &amp; Detailed
            Feedback</h5>
        <div class="d-flex flex-column gap-4">
            @foreach ($quiz->questions as $index => $q)
                @php
                    $userAnswer = $userAnswers[$q->id] ?? null;
                    $isCorrect = $userAnswer && $q->isCorrect($userAnswer);
                @endphp
                <div
                    class="p-3 rounded-3 border @if ($isCorrect) bg-success-subtle border-success-subtle @else bg-danger-subtle border-danger-subtle @endif">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="fw-bold @if ($isCorrect) text-success @else text-danger @endif">
                            Question {{ $index + 1 }}
                            @if ($isCorrect)
                                <i class="bi bi-check-circle-fill ms-1"></i> Correct (+{{ $q->points }} pts)
                            @else
                                <i class="bi bi-x-circle-fill ms-1"></i> Incorrect (0 / {{ $q->points }} pts)
                            @endif
                        </span>
                        <span class="badge bg-white text-dark border">{{ $q->points }} Points</span>
                    </div>

                    <h6 class="fw-semibold text-dark mb-3">{{ $q->question_text }}</h6>

                    <div class="row g-2 small">
                        @foreach (['a', 'b', 'c', 'd'] as $opt)
                            @php
                                $optText = $q->{'option_' . $opt};
                                $isSelected = $userAnswer === $opt;
                                $isThisCorrect = $q->correct_option === $opt;
                            @endphp
                            <div class="col-md-6">
                                <div
                                    class="p-2 rounded border
                                    @if ($isThisCorrect) bg-success text-white fw-bold
                                    @elseif($isSelected && !$isThisCorrect) bg-danger text-white fw-bold
                                    @else bg-white text-dark @endif">
                                    <strong>{{ strtoupper($opt) }})</strong> {{ $optText }}
                                    @if ($isThisCorrect)
                                        <i class="bi bi-check-circle-fill ms-1" title="Correct Answer"></i>
                                    @endif
                                    @if ($isSelected && !$isThisCorrect)
                                        <i class="bi bi-x-circle-fill ms-1" title="Your choice (Incorrect)"></i>
                                    @endif
                                    @if ($isSelected && $isThisCorrect)
                                        <span class="badge bg-white text-success ms-2">Your Answer</span>
                                    @elseif($isSelected)
                                        <span class="badge bg-white text-danger ms-2">Your Answer</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="text-center mb-4">
        <a href="{{ route('student.courses.lms', $course) }}" class="btn btn-primary fw-bold px-4 py-2">
            <i class="bi bi-arrow-left me-1"></i> Return to Course LMS Hub
        </a>
    </div>
@endsection
