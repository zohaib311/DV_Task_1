@extends('teaching.layout')

@section('heading', $quiz->title . ' — Quiz Results & Gradebook')
@section('description', $offering->course_name . ' (' . $offering->course_code . ') · ' . $quiz->duration_minutes . '
    Mins · ' . $quiz->total_points . ' Points')

@section('header-actions')
    <a href="{{ route('teaching.offerings.lms.index', $offering) }}" class="btn academic-header-btn">
        <i class="bi bi-arrow-left me-1"></i> Back to LMS Hub
    </a>
@endsection

@section('teaching-content')
    <!-- Quiz Meta Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <span class="text-muted small fw-bold">TOTAL QUESTIONS</span>
                <h3 class="fw-bold text-primary mb-0">{{ $quiz->questions->count() }}</h3>
                <small class="text-muted">{{ $quiz->total_points }} Total Marks</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <span class="text-muted small fw-bold">DURATION</span>
                <h3 class="fw-bold text-dark mb-0">{{ $quiz->duration_minutes }}</h3>
                <small class="text-muted">Minutes allowed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <span class="text-muted small fw-bold">STUDENT ATTEMPTS</span>
                <h3 class="fw-bold text-success mb-0">{{ $attempts->count() }}</h3>
                <small class="text-muted">Submissions received</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <span class="text-muted small fw-bold">LINKED ASSESSMENT</span>
                <h6 class="fw-bold text-info mb-0 mt-1">
                    {{ $quiz->assessment?->title ?? 'None (Standalone)' }}
                </h6>
                <small class="text-muted">{{ $quiz->assessment ? 'Auto-sync active' : 'Practice mode' }}</small>
            </div>
        </div>
    </div>

    <!-- Student Attempts Gradebook Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Student Submissions &amp;
                Auto-Grades</h5>
            <span class="badge bg-primary rounded-pill">{{ $attempts->count() }} Submissions</span>
        </div>

        @if ($attempts->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                <p class="mb-0">No students have attempted this quiz yet. Submissions will appear here in real-time as
                    students submit their answers.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table academic-table align-middle">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Registration No.</th>
                            <th>Started At</th>
                            <th>Completed At</th>
                            <th>Score Obtained</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attempts as $attempt)
                            <tr>
                                <td>
                                    <strong>{{ $attempt->student?->name }}</strong>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-light text-dark border">{{ $attempt->student?->registration_no ?? '—' }}</span>
                                </td>
                                <td>{{ $attempt->started_at->format('d M Y, h:i A') }}</td>
                                <td>{{ $attempt->completed_at ? $attempt->completed_at->format('d M Y, h:i A') : 'In progress' }}
                                </td>
                                <td>
                                    <strong class="text-primary">{{ $attempt->score_obtained }}</strong> /
                                    {{ $attempt->total_points }}
                                </td>
                                <td>
                                    @php($pct = $attempt->percentage())
                                    <span class="badge {{ $pct >= 50 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                                <td>
                                    @if ($attempt->status === 'submitted')
                                        <span class="badge bg-success"><i
                                                class="bi bi-check-circle me-1"></i>Submitted</span>
                                    @elseif($attempt->status === 'timed_out')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-stopwatch me-1"></i>Timed
                                            Out</span>
                                    @else
                                        <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>In
                                            Progress</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Questions Review Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-list-ol text-primary me-2"></i>Quiz Questions &amp; Answer Keys
        </h5>
        <div class="d-flex flex-column gap-3">
            @foreach ($quiz->questions as $index => $q)
                <div class="p-3 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-primary">Question {{ $index + 1 }}</span>
                        <span class="badge bg-secondary">{{ $q->points }} Points</span>
                    </div>
                    <p class="fw-semibold mb-2">{{ $q->question_text }}</p>
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <div
                                class="p-2 rounded {{ $q->correct_option === 'a' ? 'bg-success text-white fw-bold' : 'bg-white border' }}">
                                <strong>A)</strong> {{ $q->option_a }}
                                @if ($q->correct_option === 'a')
                                    <i class="bi bi-check-circle-fill ms-1"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div
                                class="p-2 rounded {{ $q->correct_option === 'b' ? 'bg-success text-white fw-bold' : 'bg-white border' }}">
                                <strong>B)</strong> {{ $q->option_b }}
                                @if ($q->correct_option === 'b')
                                    <i class="bi bi-check-circle-fill ms-1"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div
                                class="p-2 rounded {{ $q->correct_option === 'c' ? 'bg-success text-white fw-bold' : 'bg-white border' }}">
                                <strong>C)</strong> {{ $q->option_c }}
                                @if ($q->correct_option === 'c')
                                    <i class="bi bi-check-circle-fill ms-1"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div
                                class="p-2 rounded {{ $q->correct_option === 'd' ? 'bg-success text-white fw-bold' : 'bg-white border' }}">
                                <strong>D)</strong> {{ $q->option_d }}
                                @if ($q->correct_option === 'd')
                                    <i class="bi bi-check-circle-fill ms-1"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
