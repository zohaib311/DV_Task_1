@extends('student-portal.layout')

@section('heading', $course->course_name)
@section('description', $course->course_code . ' · ' . number_format((float) $course->credit_hours, 1) . ' Credits · ' .
    ($course->enrollment?->term?->name ?? 'Current Term'))

@section('header-actions')
    <a href="{{ route('student.courses') }}" class="btn academic-header-btn">
        <i class="bi bi-arrow-left me-1"></i> My Courses
    </a>
@endsection

@section('student-content')
    <!-- Course Info Strip -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-light text-primary border mb-1">{{ $course->course_code }}</span>
                <h4 class="fw-bold mb-1 text-dark">{{ $course->course_name }}</h4>
                <p class="text-muted small mb-0">
                    <i class="bi bi-person-badge me-1"></i>Instructor(s):
                    <strong>{{ $offering->teachers->pluck('name')->join(', ') ?: 'Assigned Faculty' }}</strong>
                    · Section: <strong>{{ $offering->section?->name ?? 'A' }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('student.timetable.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calendar-week me-1"></i> View in Timetable
                </a>
            </div>
        </div>
    </div>

    <!-- Announcements Section (if any) -->
    @if ($announcements->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-megaphone-fill text-primary me-2"></i>Course Noticeboard</h5>
            <div class="d-flex flex-column gap-3">
                @foreach ($announcements as $anc)
                    <div
                        class="p-3 rounded-3 @if ($anc->isUrgent()) bg-danger-subtle border border-danger-subtle @else bg-light border @endif">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 @if ($anc->isUrgent()) text-danger @else text-dark @endif">
                                @if ($anc->isUrgent())
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> [URGENT]
                                @endif
                                {{ $anc->title }}
                            </h6>
                            <small class="text-muted">{{ $anc->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0 text-secondary small" style="white-space: pre-line;">{{ $anc->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Available Quizzes Section -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-patch-question-fill text-primary me-2"></i>Online
                    Objective Quizzes</h5>
                <small class="text-muted">Timed MCQs for this course. Answers are auto-graded upon completion.</small>
            </div>
            <span class="badge bg-primary rounded-pill">{{ $quizzes->count() }} Quiz(zes)</span>
        </div>

        @if ($quizzes->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-journal-x fs-2 d-block mb-1 text-secondary"></i>
                <p class="mb-0 small">No online quizzes have been scheduled for this course yet.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach ($quizzes as $quiz)
                    @php($attempt = $quiz->attempts->first())
                    <div class="col-md-6">
                        <div
                            class="card border-0 shadow-sm rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $quiz->title }}</h6>
                                    @if ($attempt && $attempt->isCompleted())
                                        <span class="badge bg-success"><i
                                                class="bi bi-check-circle me-1"></i>Completed</span>
                                    @elseif($attempt && $attempt->isInProgress())
                                        <span class="badge bg-warning text-dark"><i
                                                class="bi bi-hourglass-split me-1"></i>In Progress</span>
                                    @else
                                        <span class="badge bg-primary">Available</span>
                                    @endif
                                </div>
                                @if ($quiz->description)
                                    <p class="text-muted small mb-2">{{ $quiz->description }}</p>
                                @endif
                                <div class="d-flex gap-2 small text-muted mb-3">
                                    <span><i class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} Mins</span>
                                    <span>•</span>
                                    <span><i class="bi bi-list-check me-1"></i>{{ $quiz->questions->count() }}
                                        Questions</span>
                                    <span>•</span>
                                    <span><i class="bi bi-star me-1"></i>{{ $quiz->total_points }} Points</span>
                                </div>
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                @if ($attempt && $attempt->isCompleted())
                                    <div>
                                        <small class="text-muted d-block">Your Score:</small>
                                        <strong class="text-success fs-6">{{ $attempt->score_obtained }} /
                                            {{ $attempt->total_points }}</strong>
                                        <small class="text-muted">({{ $attempt->percentage() }}%)</small>
                                    </div>
                                    <a href="{{ route('student.courses.lms.quizzes.result', [$course, $quiz]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View Result
                                    </a>
                                @elseif($attempt && $attempt->isInProgress())
                                    <span class="text-warning small fw-bold">Attempt active!</span>
                                    <a href="{{ route('student.courses.lms.quizzes.take', [$course, $quiz]) }}"
                                        class="btn btn-sm btn-warning fw-bold">
                                        <i class="bi bi-play-circle me-1"></i> Resume Quiz
                                    </a>
                                @else
                                    <span class="text-muted small">Not attempted</span>
                                    <a href="{{ route('student.courses.lms.quizzes.take', [$course, $quiz]) }}"
                                        class="btn btn-sm btn-primary fw-bold"
                                        onclick="return confirm('Ready to start the quiz? The {{ $quiz->duration_minutes }}-minute countdown timer will begin immediately.');">
                                        <i class="bi bi-play-circle-fill me-1"></i> Start Quiz
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Course Materials Repository -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-folder2-open text-primary me-2"></i>Weekly Class
                    Materials &amp; Downloads</h5>
                <small class="text-muted">Lecture slides, study notes, handouts, and external reference links.</small>
            </div>
        </div>

        @if ($materials->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                <p class="mb-0">No course materials have been uploaded by your instructor yet.</p>
            </div>
        @else
            @foreach ($materials as $weekTitle => $items)
                <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden bg-white border">
                    <div class="card-header bg-light py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary"><i
                                class="bi bi-bookmark-fill me-1"></i>{{ $weekTitle }}</span>
                        <span class="badge bg-secondary rounded-pill">{{ $items->count() }} file(s)</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($items as $mat)
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2 bg-light rounded text-primary fs-4">
                                        @if ($mat->file_type === 'pdf')
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        @elseif(in_array($mat->file_type, ['ppt', 'pptx']))
                                            <i class="bi bi-file-earmark-slides-fill text-warning"></i>
                                        @elseif(in_array($mat->file_type, ['doc', 'docx']))
                                            <i class="bi bi-file-earmark-word-fill text-primary"></i>
                                        @elseif($mat->file_type === 'zip')
                                            <i class="bi bi-file-earmark-zip-fill text-dark"></i>
                                        @elseif($mat->external_url)
                                            <i class="bi bi-link-45deg text-success"></i>
                                        @else
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $mat->title }}</h6>
                                        @if ($mat->description)
                                            <p class="text-muted small mb-1">{{ $mat->description }}</p>
                                        @endif
                                        <div class="d-flex gap-2 align-items-center small text-muted">
                                            @if ($mat->hasFile())
                                                <span>{{ $mat->file_name }} ({{ $mat->formattedFileSize() }})</span>
                                            @endif
                                            <span>• Uploaded {{ $mat->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if ($mat->hasFile())
                                        <a href="{{ route('student.courses.lms.materials.download', [$course, $mat]) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>
                                    @endif
                                    @if ($mat->external_url)
                                        <a href="{{ $mat->external_url }}" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Open Link
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
