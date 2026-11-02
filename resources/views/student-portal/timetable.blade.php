@extends('student-portal.layout')

@section('heading', 'My Weekly Timetable')
@section('description', 'Weekly class schedules, classroom locations, and lecture timings for your registered semester
    courses.')

@section('student-content')
    <!-- Active Semester Indicator -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                    <i class="bi bi-calendar3-week fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $enrollment?->semester ?? 'Current Semester' }}</h5>
                    <small class="text-muted">Academic Session:
                        <strong>{{ $enrollment?->academic_year ?? 'Current' }}</strong> · Enrolled:
                        <strong>{{ $student->name }}</strong> ({{ $student->registration_no }})</small>
                </div>
            </div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i> {{ $schedule->count() }} Weekly Class
                    {{ $schedule->count() === 1 ? 'Period' : 'Periods' }}
                </span>
            </div>
        </div>
    </div>

    @if ($schedule->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
            <h5 class="fw-bold text-secondary">No Class Schedule Available</h5>
            <p class="text-muted small mb-0">There are no timetable allocations published yet for your registered courses in
                {{ $enrollment?->semester ?? 'this semester' }}. Please check back after the departmental timetable release.
            </p>
        </div>
    @else
        <!-- Grouped by Day of Week -->
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
                                    {{ $grouped[$day]->count() === 1 ? 'Lecture' : 'Lectures' }}</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-column gap-3">
                                    @foreach ($grouped[$day] as $alloc)
                                        <div class="p-3 rounded-3 border bg-light-subtle position-relative"
                                            style="border-left: 4px solid #6366f1 !important;">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <span
                                                        class="badge bg-primary text-white mb-1">{{ $alloc->timeSlot?->time_window }}</span>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $alloc->courseOffering?->course_code }} -
                                                        {{ $alloc->courseOffering?->course_name }}</h6>
                                                </div>
                                                <span
                                                    class="badge bg-light text-secondary border">{{ number_format((float) $alloc->courseOffering?->credit_hours, 1) }}
                                                    Cr. Hrs</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 my-2" style="font-size: 0.8rem;">
                                                <span class="badge bg-white text-dark border"><i
                                                        class="bi bi-geo-alt text-danger me-1"></i>Room:
                                                    {{ $alloc->room?->code }} ({{ $alloc->room?->name }})</span>
                                                @if ($alloc->room?->building)
                                                    <span class="badge bg-white text-secondary border"><i
                                                            class="bi bi-building me-1"></i>{{ $alloc->room?->building }}</span>
                                                @endif
                                                <span class="badge bg-white text-dark border"><i
                                                        class="bi bi-person text-primary me-1"></i>Instructor:
                                                    {{ $alloc->teacher?->name }}</span>
                                            </div>
                                            @if ($alloc->notes)
                                                <small class="text-muted d-block mt-2 pt-2 border-top"><i
                                                        class="bi bi-info-circle me-1"></i>{{ $alloc->notes }}</small>
                                            @endif
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
