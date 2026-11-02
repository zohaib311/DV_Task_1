@extends('teaching.layout')

@section('heading', 'My Weekly Teaching Schedule')
@section('description', 'View all your assigned lecture times, room locations, and launch class attendance.')

@section('header-actions')
    <a href="{{ route('teaching.attendance.index') }}" class="btn academic-header-btn">
        <i class="bi bi-calendar-check me-1"></i> Attendance Center
    </a>
@endsection

@section('teaching-content')
    <!-- Term Selection / Meta Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('teaching.timetable.index') }}"
            class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar-week fs-4 text-primary"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Active Semester Schedule</h6>
                    <small class="text-muted">Faculty Member: <strong>{{ $teacher->name }}</strong> ·
                        {{ $schedule->count() }} assigned weekly lectures</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 small fw-bold text-muted">Term:</label>
                <select name="term_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ $selectedTerm?->id === $term->id ? 'selected' : '' }}>
                            {{ $term->name }} ({{ $term->academicYear?->name }})
                            {{ $term->status === 'active' ? '★ Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if ($schedule->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
            <h5 class="fw-bold text-secondary">No Lectures Scheduled Yet</h5>
            <p class="text-muted small mb-0">You have no course timetable allocations for
                {{ $selectedTerm?->name ?? 'the current term' }}. Timetable allocations will appear once published by the
                Academic Administrator.</p>
        </div>
    @else
        <!-- Grouped by Day of the Week -->
        @php($grouped = $schedule->groupBy(fn($item) => $item->timeSlot?->day_of_week ?? 'Unassigned'))
        <div class="row g-4">
            @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                @if (isset($grouped[$day]) && $grouped[$day]->isNotEmpty())
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                            <div
                                class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <h6 class="mb-0 fw-bold text-primary"><i
                                        class="bi bi-calendar-event me-2"></i>{{ $day }}</h6>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $grouped[$day]->count() }}
                                    {{ $grouped[$day]->count() === 1 ? 'Class' : 'Classes' }}</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-column gap-3">
                                    @foreach ($grouped[$day] as $alloc)
                                        <div class="p-3 rounded-3 border bg-light-subtle position-relative"
                                            style="border-left: 4px solid #4f46e5 !important;">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <span
                                                        class="badge bg-primary text-white mb-1">{{ $alloc->timeSlot?->time_window }}</span>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $alloc->courseOffering?->course_code }} -
                                                        {{ $alloc->courseOffering?->course_name }}</h6>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 my-2" style="font-size: 0.8rem;">
                                                <span class="badge bg-white text-dark border"><i
                                                        class="bi bi-door-open text-primary me-1"></i>{{ $alloc->room?->code }}
                                                    ({{ $alloc->room?->name }})</span>
                                                <span class="badge bg-white text-dark border"><i
                                                        class="bi bi-people text-success me-1"></i>Section
                                                    {{ $alloc->courseOffering?->section?->name ?? 'A' }}</span>
                                                @if ($alloc->room?->building)
                                                    <span class="badge bg-white text-secondary border"><i
                                                            class="bi bi-building me-1"></i>{{ $alloc->room?->building }}</span>
                                                @endif
                                            </div>
                                            @if ($alloc->notes)
                                                <small class="text-muted d-block mb-2"><i
                                                        class="bi bi-info-circle me-1"></i>{{ $alloc->notes }}</small>
                                            @endif
                                            <div class="d-flex justify-content-end pt-2 border-top">
                                                <a href="{{ route('teaching.attendance.offering', $alloc->course_offering_id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-calendar2-check me-1"></i> Mark Attendance
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@endsection
