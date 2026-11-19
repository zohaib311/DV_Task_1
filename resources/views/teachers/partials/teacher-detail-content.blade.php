@php
    $teacherDepts = $teacher->offerings->pluck('department.name')->unique()->filter();
    $teacherPrograms = $teacher->offerings->pluck('program.name')->unique()->filter();
    $activeOfferingsCount = $teacher->offerings->where('status', 'active')->count();
    $totalStudentsTaught = $teacher->offerings->flatMap(fn($o) => $o->enrollmentCourses->pluck('student_semester_enrollment_id'))->unique()->count();
    $attendanceSessionsCount = $teacher->offerings->sum(fn($o) => $o->attendanceSessions->count());
@endphp

<div class="teacher-modal-layout">

    {{-- Left Profile Card --}}
    <div class="teacher-profile-card">
        <div class="teacher-profile-image">
            <img src="{{ asset('storage/images/' . ($teacher->image ?: 'default-user.png')) }}"
                alt="{{ $teacher->name }}" class="teacher-modal-avatar"
                onerror="this.onerror=null;this.src='{{ asset('storage/images/default-user.png') }}';">
        </div>

        <div class="teacher-profile-info">
            <h3>{{ $teacher->name }}</h3>

            <div class="badge px-3 py-1.5 rounded-pill mb-2" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; font-size: 0.82rem;">
                <i class="bi bi-mortarboard-fill me-1"></i> {{ $teacher->course ?? 'Faculty Member' }}
            </div>

            <div class="teacher-profile-pills">
                @if($teacher->user)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-shield-check me-1"></i> Portal Account Active
                    </span>
                    <small class="text-muted d-block text-truncate" style="font-size: 0.75rem; max-width: 250px;">
                        <i class="bi bi-person-circle me-1"></i> {{ $teacher->user->email }}
                    </small>
                @else
                    <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle">
                        <i class="bi bi-shield-slash me-1"></i> No Portal Login
                    </span>
                @endif

                @if($activeOfferingsCount > 0)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-broadcast-pin me-1"></i> Actively Teaching ({{ $activeOfferingsCount }} {{ Str::plural('Class', $activeOfferingsCount) }})
                    </span>
                @else
                    <span class="badge bg-light text-secondary border">
                        <i class="bi bi-pause-circle me-1"></i> Standby Faculty
                    </span>
                @endif
            </div>

            <div class="teacher-profile-contact-list">
                <div class="teacher-profile-item">
                    <i class="bi bi-envelope"></i>
                    <span>{{ $teacher->email }}</span>
                </div>
                <div class="teacher-profile-item">
                    <i class="bi bi-telephone"></i>
                    <span>{{ $teacher->phone }}</span>
                </div>
                <div class="teacher-profile-item">
                    <i class="bi bi-building"></i>
                    <span>{{ $teacherDepts->isNotEmpty() ? $teacherDepts->implode(', ') : 'Department N/A' }}</span>
                </div>
                <div class="teacher-profile-item">
                    <i class="bi bi-calendar-event"></i>
                    <div>
                        <strong class="d-block text-dark">Joined University:</strong>
                        <span>{{ $teacher->created_at ? $teacher->created_at->format('d M Y') : 'N/A' }}</span>
                        @if($teacher->created_at)
                            <small class="text-muted d-block" style="font-size: 0.74rem;">({{ $teacher->created_at->diffForHumans() }})</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 w-100 pt-3 border-top d-flex flex-column gap-2">
                <a href="{{ route('editTeacherForm', $teacher->id) }}" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1 rounded-3">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit Teacher Profile</span>
                </a>
                @if(!isset($isFullPage) || !$isFullPage)
                    <a href="{{ route('viewTeacher', $teacher->id) }}" target="_blank" class="btn btn-sm btn-light border text-secondary w-100 d-inline-flex align-items-center justify-content-center gap-1 rounded-3">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Open Full Page View</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Details Stack --}}
    <div class="teacher-details-stack">

        {{-- Top KPI Cards Strip --}}
        <div class="teacher-kpi-grid">
            <div class="teacher-kpi-card">
                <div class="teacher-kpi-icon icon-courses">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="teacher-kpi-content">
                    <span class="teacher-kpi-num">{{ $teacher->offerings->count() }}</span>
                    <span class="teacher-kpi-label">Total Subjects</span>
                </div>
            </div>

            <div class="teacher-kpi-card">
                <div class="teacher-kpi-icon icon-active">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
                <div class="teacher-kpi-content">
                    <span class="teacher-kpi-num">{{ $activeOfferingsCount }}</span>
                    <span class="teacher-kpi-label">Active Classes</span>
                </div>
            </div>

            <div class="teacher-kpi-card">
                <div class="teacher-kpi-icon icon-students">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="teacher-kpi-content">
                    <span class="teacher-kpi-num">{{ $totalStudentsTaught }}</span>
                    <span class="teacher-kpi-label">Students Taught</span>
                </div>
            </div>

            <div class="teacher-kpi-card">
                <div class="teacher-kpi-icon icon-depts">
                    <i class="bi bi-building"></i>
                </div>
                <div class="teacher-kpi-content">
                    <span class="teacher-kpi-num">{{ $teacherDepts->count() }}</span>
                    <span class="teacher-kpi-label">Departments</span>
                </div>
            </div>
        </div>

        {{-- 1. Identity & Official Appointment Details --}}
        <div class="teacher-details-box">
            <div class="teacher-details-heading heading-personal">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Faculty Member Identity &amp; Appointment</span>
                </div>
                <span class="badge bg-primary text-white">Teacher ID: #{{ $teacher->id }}</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-hash"></i> Faculty System ID</span>
                <span class="teacher-detail-value fw-bold text-dark">#{{ $teacher->id }}</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-person"></i> Full Name</span>
                <span class="teacher-detail-value fw-semibold text-dark">{{ $teacher->name }}</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-envelope"></i> Official Email</span>
                <span class="teacher-detail-value text-primary fw-medium">{{ $teacher->email }}</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-telephone"></i> Contact Phone</span>
                <span class="teacher-detail-value">{{ $teacher->phone }}</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-award"></i> Specialization / Course</span>
                <span class="teacher-detail-value">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        {{ $teacher->course ?? 'General Academic Faculty' }}
                    </span>
                </span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-calendar-check"></i> Joining / Appointment Date</span>
                <span class="teacher-detail-value">
                    @if($teacher->created_at)
                        <strong class="text-dark">{{ $teacher->created_at->format('l, d F Y') }}</strong>
                        <span class="text-muted ms-2">({{ $teacher->created_at->format('h:i A') }})</span>
                        <span class="badge bg-light text-primary border ms-2">
                            <i class="bi bi-clock-history me-1"></i> Joined {{ $teacher->created_at->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-muted fst-italic">Not Recorded</span>
                    @endif
                </span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-shield-lock"></i> Portal User Account</span>
                <span class="teacher-detail-value">
                    @if($teacher->user)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle-fill me-1"></i> Linked (User #{{ $teacher->user->id }})
                        </span>
                        <span class="ms-2 text-dark fw-medium">{{ $teacher->user->email }}</span>
                        <span class="badge bg-light text-secondary border ms-2">
                            Role: {{ $teacher->user->roles->pluck('name')->implode(', ') ?: 'Teacher' }}
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle">
                            <i class="bi bi-x-circle me-1"></i> No Linked Portal Account
                        </span>
                        <span class="text-muted ms-2 small">Can be linked in Edit Teacher screen</span>
                    @endif
                </span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-arrow-repeat"></i> Profile Last Updated</span>
                <span class="teacher-detail-value text-muted">
                    {{ $teacher->updated_at ? $teacher->updated_at->format('d M Y, h:i A') : 'N/A' }}
                </span>
            </div>
        </div>

        {{-- 2. Teaching Portfolio & Assigned Subjects ("kon kon say subjects ko prhaa raha hai") --}}
        <div class="teacher-details-box">
            <div class="teacher-details-heading heading-courses">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-book-half"></i>
                    <span>Assigned Subjects &amp; Teaching Portfolio</span>
                </div>
                <span class="badge bg-primary text-white">
                    {{ $teacher->offerings->count() }} {{ Str::plural('Subject', $teacher->offerings->count()) }}
                </span>
            </div>

            @if($teacher->offerings->isNotEmpty())
                <div class="table-responsive">
                    <table class="teacher-offerings-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject / Course</th>
                                <th>Department &amp; Program</th>
                                <th>Term &amp; Sem</th>
                                <th>Section</th>
                                <th>Credits</th>
                                <th>Students</th>
                                <th>Assessment Scheme</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacher->offerings as $offering)
                                <tr>
                                    <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="course-code-pill">{{ $offering->course_code }}</span>
                                        <div class="fw-bold text-dark mt-1">{{ $offering->course_name }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $offering->department->name ?? 'N/A' }}</div>
                                        <small class="text-muted d-block">{{ $offering->program->name ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $offering->term->name ?? 'Current' }}</span>
                                        <small class="text-muted d-block mt-0.5">Sem {{ $offering->semester }}</small>
                                    </td>
                                    <td>
                                        <span class="offering-section-pill">
                                            Sec {{ $offering->section->name ?? 'A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ number_format((float) $offering->credit_hours, 1) }}</strong>
                                        <span class="text-muted small">Cr</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-people me-1"></i> {{ $offering->enrollmentCourses->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block" style="font-size: 0.72rem; line-height: 1.3;">
                                            Total: <strong>{{ $offering->total_marks }}</strong><br>
                                            Att: {{ $offering->attendance_marks }} | Mid: {{ $offering->mid_marks }} | Fin: {{ $offering->final_marks }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($offering->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($offering->status === 'completed')
                                            <span class="badge bg-info">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($offering->status ?? 'Planned') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    <h6 class="fw-bold text-dark mb-1">No Assigned Subjects or Classes</h6>
                    <p class="small text-muted mb-0">This faculty member is not currently assigned to any course offerings. Use the Academic Setup module to allocate classes.</p>
                </div>
            @endif
        </div>

        {{-- 3. Classroom Timetable & Room Schedule --}}
        <div class="teacher-details-box">
            <div class="teacher-details-heading heading-schedule">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i>
                    <span>Classroom Timetable &amp; Schedule Allocations</span>
                </div>
                <span class="badge" style="background:#059669; color:#fff;">
                    {{ $teacher->timetableAllocations->count() }} {{ Str::plural('Slot', $teacher->timetableAllocations->count()) }}
                </span>
            </div>

            @if($teacher->timetableAllocations->isNotEmpty())
                <div class="p-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($teacher->timetableAllocations as $alloc)
                            <div class="schedule-slot-pill">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $alloc->timeSlot->day_of_week ?? 'Day' }}:</span>
                                <strong>{{ $alloc->timeSlot ? substr($alloc->timeSlot->start_time, 0, 5) . ' - ' . substr($alloc->timeSlot->end_time, 0, 5) : 'Slot' }}</strong>
                                <span class="badge bg-white text-success border ms-1">
                                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $alloc->room->name ?? 'Room N/A' }}
                                </span>
                                @if($alloc->courseOffering)
                                    <small class="text-muted">({{ $alloc->courseOffering->course_code }})</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-3 text-center text-muted">
                    <small><i class="bi bi-calendar-x me-1"></i> No classroom schedule slots currently allocated to this teacher in the Timetable module.</small>
                </div>
            @endif
        </div>

        {{-- 4. Academic Operations & Performance Tracking --}}
        <div class="teacher-details-box">
            <div class="teacher-details-heading heading-portal">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-activity"></i>
                    <span>Academic Operations &amp; Engagement Summary</span>
                </div>
                <span class="badge bg-secondary text-white">Live Status</span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-clipboard-check"></i> Attendance Sessions</span>
                <span class="teacher-detail-value">
                    <strong class="text-dark">{{ $attendanceSessionsCount }}</strong> sessions conducted across assigned classes
                </span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-diagram-3"></i> Departments Affiliated</span>
                <span class="teacher-detail-value">
                    @if($teacherDepts->isNotEmpty())
                        @foreach($teacherDepts as $deptName)
                            <span class="badge bg-light text-dark border me-1">{{ $deptName }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">None</span>
                    @endif
                </span>
            </div>

            <div class="teacher-detail-row">
                <span class="teacher-detail-label"><i class="bi bi-person-workspace"></i> Teacher Panel Access</span>
                <span class="teacher-detail-value">
                    @if($teacher->user)
                        <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Authorized to manage rosters, attendance, grading &amp; LMS</span>
                    @else
                        <span class="text-muted"><i class="bi bi-info-circle me-1"></i> Link a user account with role 'Teacher' to enable portal workspace access.</span>
                    @endif
                </span>
            </div>
        </div>

    </div>

</div>
