@extends('academic.layout')

@section('heading', 'Timetable & Academic Scheduling')
@section('description', 'Configure rooms, time slots, prevent schedule clashes, and organize the weekly lecture
    matrix.')

@section('header-actions')
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="collapse" data-bs-target="#newRoomCard"
            aria-expanded="false">
            <i class="bi bi-door-open-fill me-1"></i> Add Room
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="collapse" data-bs-target="#newSlotCard"
            aria-expanded="false">
            <i class="bi bi-clock-fill me-1"></i> Add Time Slot
        </button>
        <button type="button" class="btn btn-light btn-sm text-primary fw-bold" data-bs-toggle="collapse"
            data-bs-target="#newAllocationCard" aria-expanded="false">
            <i class="bi bi-calendar-plus-fill me-1"></i> Schedule Class
        </button>
    </div>
@endsection

@section('academic-content')
    <!-- Collapsible Quick Action Cards -->
    <div class="mb-4">
        <!-- 1. Schedule Class Form -->
        <div class="collapse mb-3 @if (
            $errors->has('course_offering_id') ||
                $errors->has('room_id') ||
                $errors->has('time_slot_id') ||
                $errors->has('teacher_id')) show @endif" id="newAllocationCard">
            <div class="card border-0 shadow-sm rounded-4 p-4"
                style="background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%); border: 1px solid #dbeafe !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar-plus-fill me-2"></i>Schedule Lecture /
                        Class Allocation</h5>
                    <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newAllocationCard"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('academic.timetable.allocations.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Course Offering <span class="text-danger">*</span></label>
                            <select name="course_offering_id" class="form-select" required>
                                <option value="">-- Select Course Offering --</option>
                                @foreach ($offerings as $offering)
                                    <option value="{{ $offering->id }}"
                                        {{ old('course_offering_id') == $offering->id ? 'selected' : '' }}>
                                        {{ $offering->course_code }} - {{ $offering->course_name }} (Sec:
                                        {{ $offering->section?->name ?? 'A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Instructor / Teacher <span
                                    class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">-- Select Teacher --</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Room / Lab Facility <span
                                    class="text-danger">*</span></label>
                            <select name="room_id" class="form-select" required>
                                <option value="">-- Select Room --</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}
                                        {{ !$room->is_active ? 'disabled' : '' }}>
                                        {{ $room->code }} - {{ $room->name }} (Cap: {{ $room->capacity }})
                                        {{ !$room->is_active ? '[Inactive]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Time Slot <span class="text-danger">*</span></label>
                            <select name="time_slot_id" class="form-select" required>
                                <option value="">-- Select Slot --</option>
                                @foreach ($timeSlots as $slot)
                                    <option value="{{ $slot->id }}"
                                        {{ old('time_slot_id') == $slot->id ? 'selected' : '' }}
                                        {{ $slot->slot_type === 'break' ? 'disabled' : '' }}>
                                        {{ $slot->day_of_week }}: {{ $slot->time_window }}
                                        {{ $slot->slot_type === 'break' ? '[Break]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-10">
                            <label class="form-label fw-semibold">Notes / Special Instructions</label>
                            <input type="text" name="notes" class="form-control"
                                placeholder="e.g. Bring scientific calculator, Quiz scheduled" value="{{ old('notes') }}"
                                maxlength="255">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Allocate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Add Room Form -->
        <div class="collapse mb-3 @if ($errors->has('code') || $errors->has('name') || $errors->has('capacity')) show @endif" id="newRoomCard">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-door-open-fill text-primary me-2"></i>Register New Room / Lab
                        Facility</h5>
                    <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newRoomCard"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('academic.timetable.rooms.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Room Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Advanced Computing Lab 1" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Room Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. CS-LAB-01"
                                value="{{ old('code') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Building / Block</label>
                            <input type="text" name="building" class="form-control" placeholder="e.g. Block C"
                                value="{{ old('building') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Facility Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="lecture_hall">Lecture Hall</option>
                                <option value="computer_lab">Computer Lab</option>
                                <option value="science_lab">Science Lab</option>
                                <option value="auditorium">Auditorium</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Seating Capacity <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" min="1" max="500"
                                value="{{ old('capacity', 45) }}" required>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="bi bi-plus-lg me-1"></i> Save Room
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Add Time Slot Form -->
        <div class="collapse mb-3 @if ($errors->has('time_slot')) show @endif" id="newSlotCard">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-fill text-primary me-2"></i>Create New Time Slot</h5>
                    <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newSlotCard"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('academic.timetable.time-slots.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="academic_term_id" value="{{ $selectedTerm?->id }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Academic Term</label>
                            <input type="text" class="form-control"
                                value="{{ $selectedTerm?->name ?? 'Active Term' }} ({{ $selectedTerm?->academicYear?->name }})"
                                disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control"
                                value="{{ old('start_time', '08:30') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control"
                                value="{{ old('end_time', '10:00') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Slot Type <span class="text-danger">*</span></label>
                            <select name="slot_type" class="form-select" required>
                                <option value="regular">Regular Lecture (1.5h)</option>
                                <option value="lab_double">Lab Double Slot (3h)</option>
                                <option value="break">Prayer / Lunch Break</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="bi bi-plus-lg me-1"></i> Add Time Slot
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('academic.timetable.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 fw-semibold">Term</span>
                    <select name="term_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" {{ $selectedTerm?->id === $term->id ? 'selected' : '' }}>
                                {{ $term->name }} ({{ $term->academicYear?->name }})
                                {{ $term->status === 'active' ? '★ Active' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <select name="room_id" class="form-select form-select-sm">
                    <option value="">All Rooms &amp; Labs</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}"
                            {{ ($filters['room_id'] ?? '') == $room->id ? 'selected' : '' }}>
                            {{ $room->code }} ({{ $room->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="teacher_id" class="form-select form-select-sm">
                    <option value="">All Teachers</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}"
                            {{ ($filters['teacher_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="section_id" class="form-select form-select-sm">
                    <option value="">All Sections</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->id }}"
                            {{ ($filters['section_id'] ?? '') == $sec->id ? 'selected' : '' }}>
                            Section {{ $sec->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i>
                    Filter</button>
                <a href="{{ route('academic.timetable.index', ['term_id' => $selectedTerm?->id]) }}"
                    class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <!-- Weekly Interactive Timetable Grid -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3-week text-primary me-2"></i>Weekly Lecture
                    Scheduling Matrix</h5>
                <small class="text-muted">Academic Term: <strong>{{ $selectedTerm?->name }}</strong> · Total Scheduled
                    Classes: <strong>{{ $grid['total_allocations'] ?? 0 }}</strong></small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i
                        class="bi bi-door-closed me-1"></i>{{ $rooms->where('is_active', true)->count() }} Active
                    Rooms</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i
                        class="bi bi-clock me-1"></i>{{ $timeSlots->count() }} Slots</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle" style="min-width: 900px; font-size: 0.85rem;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 140px;" class="py-3 bg-light">Time Window</th>
                        @foreach ($grid['days'] as $day)
                            <th class="py-3" style="width: calc((100% - 140px) / 6);">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($grid['time_windows'] as $window)
                        <tr>
                            <td class="text-center fw-bold bg-light text-secondary py-3">
                                <div>{{ $window['label'] }}</div>
                            </td>
                            @foreach ($grid['days'] as $day)
                                @php($allocationsInCell = $grid['matrix'][$day][$window['label']] ?? collect())
                                <td
                                    class="p-2 align-top {{ $allocationsInCell->isNotEmpty() ? 'bg-white' : 'bg-light-subtle' }}">
                                    @forelse($allocationsInCell as $alloc)
                                        <div class="card border rounded-3 p-2 mb-1 shadow-sm position-relative"
                                            style="background-color: #f8fafc; border-left: 3px solid #2563eb !important;">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong class="text-primary"
                                                    style="font-size: 0.8rem;">{{ $alloc->courseOffering?->course_code }}</strong>
                                                <form
                                                    action="{{ route('academic.timetable.allocations.destroy', $alloc) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Remove allocation for {{ $alloc->courseOffering?->course_code }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0"
                                                        style="font-size: 0.75rem;" title="Remove Allocation">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="fw-semibold text-truncate text-dark" style="font-size: 0.78rem;"
                                                title="{{ $alloc->courseOffering?->course_name }}">
                                                {{ $alloc->courseOffering?->course_name }}
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-1" style="font-size: 0.7rem;">
                                                <span class="badge bg-secondary-subtle text-secondary border"><i
                                                        class="bi bi-door-open me-0.5"></i>{{ $alloc->room?->code }}</span>
                                                <span class="badge bg-success-subtle text-success border"><i
                                                        class="bi bi-people me-0.5"></i>Sec:
                                                    {{ $alloc->courseOffering?->section?->name ?? 'A' }}</span>
                                            </div>
                                            <div class="text-muted mt-1 text-truncate" style="font-size: 0.72rem;">
                                                <i class="bi bi-person"></i> {{ $alloc->teacher?->name }}
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted d-block text-center py-2"
                                            style="font-size: 0.75rem; opacity: 0.4;">&mdash;</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-50"></i>
                                    <strong>No time slots have been configured for
                                        {{ $selectedTerm?->name ?? 'this term' }}.</strong>
                                    <p class="mb-2 text-muted small">Click "Add Time Slot" above to define regular class
                                        periods or breaks.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Facilities, Time Slots, & Allocations Tabs -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <ul class="nav nav-pills mb-3" id="timetableTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="rooms-tab" data-bs-toggle="pill" data-bs-target="#rooms-pane"
                        type="button" role="tab">
                        <i class="bi bi-door-open me-1"></i> Rooms &amp; Labs ({{ $rooms->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="slots-tab" data-bs-toggle="pill" data-bs-target="#slots-pane"
                        type="button" role="tab">
                        <i class="bi bi-clock me-1"></i> Term Slots ({{ $timeSlots->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="allocations-tab" data-bs-toggle="pill" data-bs-target="#allocations-pane"
                        type="button" role="tab">
                        <i class="bi bi-list-check me-1"></i> Master Allocation List ({{ $grid['total_allocations'] ?? 0 }})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="timetableTabsContent">
                <!-- Rooms Tab -->
                <div class="tab-pane fade show active" id="rooms-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table academic-table align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Facility Name</th>
                                    <th>Building</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rooms as $room)
                                    <tr>
                                        <td><strong style="font-family: monospace;">{{ $room->code }}</strong></td>
                                        <td>{{ $room->name }}</td>
                                        <td>{{ $room->building ?: 'Main Campus' }}</td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $room->type)) }}</span>
                                        </td>
                                        <td><strong>{{ $room->capacity }}</strong> seats</td>
                                        <td>
                                            <span class="badge {{ $room->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $room->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No rooms configured yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Slots Tab -->
                <div class="tab-pane fade" id="slots-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table academic-table align-middle">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time Window</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeSlots as $slot)
                                    <tr>
                                        <td><strong>{{ $slot->day_of_week }}</strong></td>
                                        <td><span class="badge bg-light text-primary border">{{ $slot->time_window }}</span>
                                        </td>
                                        <td>{{ $slot->formatted_start_time }}</td>
                                        <td>{{ $slot->formatted_end_time }}</td>
                                        <td>
                                            @if ($slot->slot_type === 'break')
                                                <span class="badge bg-warning text-dark"><i
                                                        class="bi bi-cup-hot me-1"></i>Break / Recess</span>
                                            @elseif($slot->slot_type === 'lab_double')
                                                <span class="badge bg-info text-dark">Lab Double Slot</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border">Regular
                                                    Lecture</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No time slots created for this
                                            term yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Allocations Tab -->
                <div class="tab-pane fade" id="allocations-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table academic-table align-middle">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Section</th>
                                    <th>Teacher</th>
                                    <th>Room</th>
                                    <th>Day &amp; Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grid['all_allocations'] ?? [] as $alloc)
                                    <tr>
                                        <td>
                                            <strong>{{ $alloc->courseOffering?->course_code }}</strong> -
                                            {{ $alloc->courseOffering?->course_name }}
                                        </td>
                                        <td><span class="badge bg-light text-dark border">Section
                                                {{ $alloc->courseOffering?->section?->name ?? 'A' }}</span></td>
                                        <td>{{ $alloc->teacher?->name }}</td>
                                        <td><strong style="font-family: monospace;">{{ $alloc->room?->code }}</strong>
                                            ({{ $alloc->room?->name }})</td>
                                        <td>
                                            <strong>{{ $alloc->timeSlot?->day_of_week }}</strong> ·
                                            {{ $alloc->timeSlot?->time_window }}
                                        </td>
                                        <td>
                                            <form action="{{ route('academic.timetable.allocations.destroy', $alloc) }}"
                                                method="POST" onsubmit="return confirm('Remove allocation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No course offering allocations
                                            have been made yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endsection
