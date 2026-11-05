@extends('teaching.layout')

@section('heading', $offering->course_name . ' — LMS & Class Materials')
@section('description', $offering->course_code . ' · Section ' . ($offering->section?->name ?? 'A') . ' · ' .
    $offering->term->name)

@section('header-actions')
    <a href="{{ route('teaching.offerings.show', $offering) }}" class="btn academic-header-btn">
        <i class="bi bi-arrow-left me-1"></i> Class Overview
    </a>
@endsection

@section('teaching-content')
    <!-- Quick Nav Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="lmsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-4 py-2 rounded-pill shadow-sm" id="materials-tab" data-bs-toggle="pill"
                data-bs-target="#materials-pane" type="button" role="tab">
                <i class="bi bi-folder2-open me-2"></i>Course Materials ({{ $materials->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4 py-2 rounded-pill shadow-sm" id="announcements-tab" data-bs-toggle="pill"
                data-bs-target="#announcements-pane" type="button" role="tab">
                <i class="bi bi-megaphone me-2"></i>Announcements ({{ $announcements->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4 py-2 rounded-pill shadow-sm" id="quizzes-tab" data-bs-toggle="pill"
                data-bs-target="#quizzes-pane" type="button" role="tab">
                <i class="bi bi-patch-question me-2"></i>Online Quizzes ({{ $quizzes->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="lmsTabsContent">
        <!-- TAB 1: COURSE MATERIALS -->
        <div class="tab-pane fade show active" id="materials-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload Course
                        Material</h5>
                </div>
                <form action="{{ route('teaching.offerings.lms.materials.store', $offering) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Title / Topic <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                placeholder="e.g. Week 1 - Intro to Programming Slides" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Week Number</label>
                            <select name="week_number" class="form-select">
                                <option value="">General Resource (No Week)</option>
                                @for ($w = 1; $w <= 16; $w++)
                                    <option value="{{ $w }}">Week {{ $w }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Document File (PDF, PPT, DOC, ZIP)</label>
                            <input type="file" name="file" class="form-control"
                                accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.zip,.txt,.jpg,.png">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Description / Notes <small
                                    class="text-muted">(Optional)</small></label>
                            <input type="text" name="description" class="form-control"
                                placeholder="e.g. Read chapters 1-3 before Wednesday lecture">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">External Resource Link <small class="text-muted">(e.g.
                                    Video / GitHub)</small></label>
                            <input type="url" name="external_url" class="form-control"
                                placeholder="https://youtube.com/... or https://github.com/...">
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                    id="publishCheck" checked>
                                <label class="form-check-label fw-semibold" for="publishCheck">Publish immediately for
                                    enrolled students</label>
                            </div>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-upload me-1"></i> Upload Material
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Materials Repository List -->
            @if ($materials->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-folder-x fs-1 text-muted mb-2"></i>
                    <h5 class="fw-bold text-secondary">No Course Materials Uploaded Yet</h5>
                    <p class="text-muted small mb-0">Upload lecture slides, reading PDFs, and external links above so
                        enrolled students can access them.</p>
                </div>
            @else
                @php($groupedMaterials = $materials->groupBy(fn($m) => $m->week_number ? "Week {$m->week_number}" : 'General Resources'))
                @foreach ($groupedMaterials as $weekTitle => $items)
                    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden bg-white">
                        <div
                            class="card-header bg-light py-2 px-4 border-0 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary"><i
                                    class="bi bi-calendar2-range me-2"></i>{{ $weekTitle }}</span>
                            <span class="badge bg-secondary rounded-pill">{{ $items->count() }} item(s)</span>
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
                                                    <span><i class="bi bi-file-earmark me-1"></i>{{ $mat->file_name }}
                                                        ({{ $mat->formattedFileSize() }})</span>
                                                @endif
                                                @if ($mat->external_url)
                                                    <a href="{{ $mat->external_url }}" target="_blank"
                                                        rel="noopener noreferrer" class="text-decoration-none">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Open External Link
                                                    </a>
                                                @endif
                                                <span>• Uploaded {{ $mat->created_at->diffForHumans() }}</span>
                                                @if (!$mat->is_published)
                                                    <span class="badge bg-warning text-dark">Draft / Unpublished</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($mat->hasFile())
                                            <a href="{{ route('teaching.offerings.lms.materials.download', [$offering, $mat]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endif
                                        <form
                                            action="{{ route('teaching.offerings.lms.materials.destroy', [$offering, $mat]) }}"
                                            method="POST" onsubmit="return confirm('Delete this course material?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- TAB 2: ANNOUNCEMENTS & NOTICEBOARD -->
        <div class="tab-pane fade" id="announcements-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-megaphone-fill me-2"></i>Post Course Announcement
                </h5>
                <form action="{{ route('teaching.offerings.lms.announcements.store', $offering) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Announcement Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                placeholder="e.g. Class Rescheduled to Lab 2 / Quiz 1 Next Monday" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="normal">Normal Notice</option>
                                <option value="urgent">🚨 Urgent (Sends Immediate Alert)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Message / Content <span
                                    class="text-danger">*</span></label>
                            <textarea name="content" rows="3" class="form-control"
                                placeholder="Write announcement details for your students..." required></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-send-fill me-1"></i> Post Announcement
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Announcements Feed -->
            @if ($announcements->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-chat-square-dots fs-1 text-muted mb-2"></i>
                    <h5 class="fw-bold text-secondary">No Announcements Posted</h5>
                    <p class="text-muted small mb-0">Use announcements to broadcast urgent updates, quiz reminders, and
                        class guidelines to your enrolled students.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach ($announcements as $anc)
                        <div
                            class="card border-0 shadow-sm rounded-4 p-4 bg-white @if ($anc->isUrgent()) border-start border-danger border-4 @endif">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold mb-0 text-dark">{{ $anc->title }}</h5>
                                        @if ($anc->isUrgent())
                                            <span class="badge bg-danger text-white"><i
                                                    class="bi bi-exclamation-triangle-fill me-1"></i>Urgent</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">Notice</span>
                                        @endif
                                    </div>
                                    <small class="text-muted"><i
                                            class="bi bi-clock me-1"></i>{{ $anc->created_at->format('d M Y, h:i A') }}
                                        ({{ $anc->created_at->diffForHumans() }})</small>
                                </div>
                                <form
                                    action="{{ route('teaching.offerings.lms.announcements.destroy', [$offering, $anc]) }}"
                                    method="POST" onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 mb-0 text-secondary" style="white-space: pre-line;">{{ $anc->content }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 3: ONLINE QUIZZES -->
        <div class="tab-pane fade" id="quizzes-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-patch-question-fill me-2"></i>Objective Quizzes
                        & Auto-Grading</h5>
                    <small class="text-muted">Create timed MCQs that automatically evaluate and populate student assessment
                        marks.</small>
                </div>
                <a href="{{ route('teaching.offerings.lms.quizzes.create', $offering) }}"
                    class="btn btn-primary fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Create New MCQ Quiz
                </a>
            </div>

            @if ($quizzes->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-journal-check fs-1 text-muted mb-2"></i>
                    <h5 class="fw-bold text-secondary">No Quizzes Configured</h5>
                    <p class="text-muted small mb-3">Create timed online quizzes with multiple-choice questions for
                        automated grading.</p>
                    <div>
                        <a href="{{ route('teaching.offerings.lms.quizzes.create', $offering) }}"
                            class="btn btn-outline-primary fw-bold">
                            <i class="bi bi-plus-lg me-1"></i> Create First Quiz
                        </a>
                    </div>
                </div>
            @else
                <div class="row g-3">
                    @foreach ($quizzes as $quiz)
                        <div class="col-md-6">
                            <div
                                class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold mb-0 text-dark">{{ $quiz->title }}</h5>
                                        <span
                                            class="badge {{ $quiz->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $quiz->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    @if ($quiz->description)
                                        <p class="text-muted small mb-3">{{ $quiz->description }}</p>
                                    @endif
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-light text-primary border"><i
                                                class="bi bi-clock me-1"></i>{{ $quiz->duration_minutes }} Mins</span>
                                        <span class="badge bg-light text-dark border"><i
                                                class="bi bi-list-check me-1"></i>{{ $quiz->questions_count }}
                                            Questions</span>
                                        <span class="badge bg-light text-success border"><i
                                                class="bi bi-star me-1"></i>{{ $quiz->total_points }} Points</span>
                                        @if ($quiz->assessment)
                                            <span class="badge bg-info text-white"><i class="bi bi-link me-1"></i>Synced
                                                to {{ $quiz->assessment->title }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mb-3">
                                        <i class="bi bi-people-fill me-1"></i><strong>{{ $quiz->attempts_count }}</strong>
                                        student submission(s)
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <a href="{{ route('teaching.offerings.lms.quizzes.show', [$offering, $quiz]) }}"
                                        class="btn btn-sm btn-primary fw-bold">
                                        <i class="bi bi-bar-chart-line me-1"></i> View Results & Questions
                                    </a>
                                    <form
                                        action="{{ route('teaching.offerings.lms.quizzes.destroy', [$offering, $quiz]) }}"
                                        method="POST" onsubmit="return confirm('Delete this quiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
