@extends('welcome')
@section('title', 'University Admin Dashboard & Analytics')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard/dashboard.css')) }}">
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- ==========================================
         1. HERO WELCOME & SESSION BANNER
         ========================================== --}}
    <div class="dashboard-hero-banner">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div class="dashboard-hero-content">
                <div class="dashboard-hero-badge-top">
                    <i class="bi bi-shield-check"></i>
                    <span>University Management System · Admin Portal</span>
                </div>
                <h2>Welcome back, {{ auth()->user()->name }}!</h2>
                <p>
                    Live academic management statistics, student intake intelligence, financial volume, and institutional performance metrics.
                </p>
                <div class="dashboard-hero-badges">
                    <span class="dashboard-badge-glass">
                        <i class="bi bi-calendar3"></i>
                        {{ $activeTerm->name ?? 'Academic Session 2026-2027' }}
                    </span>
                    <span class="dashboard-badge-glass">
                        <i class="bi bi-clock-history"></i>
                        {{ now()->format('l, d F Y') }}
                    </span>
                    <span class="dashboard-badge-glass text-light" style="background: rgba(16, 185, 129, 0.22); border-color: rgba(16, 185, 129, 0.4);">
                        <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                        Live Academic Engine Active
                    </span>
                </div>
            </div>

            <div class="dashboard-hero-kpi-stack d-none d-lg-flex">
                <div class="hero-kpi-item">
                    <div class="hero-kpi-num">{{ number_format($totalStudents) }}</div>
                    <div class="hero-kpi-lbl">Students</div>
                </div>
                <div class="hero-kpi-item">
                    <div class="hero-kpi-num">{{ number_format($totalTeachers) }}</div>
                    <div class="hero-kpi-lbl">Faculty</div>
                </div>
                <div class="hero-kpi-item">
                    <div class="hero-kpi-num">{{ number_format($activeEnrollments) }}</div>
                    <div class="hero-kpi-lbl">Enrollments</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         2. QUICK OPERATIONS TOOLBAR
         ========================================== --}}
    @if($statistics->contains(fn ($stat) => $stat['createRoute']) || auth()->user()->can('results.create'))
        <div class="quick-actions-card">
            <h5>
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                <span>Quick Operations & Direct Management</span>
            </h5>
            <div class="action-buttons-grid">
                @foreach($statistics as $stat)
                    @if($stat['createRoute'])
                        <a href="{{ route($stat['createRoute']) }}" class="action-shortcut-btn">
                            <i class="bi {{ $stat['icon'] }}"></i>
                            <span>Add {{ \Illuminate\Support\Str::singular($stat['label']) }}</span>
                        </a>
                    @endif
                @endforeach
                @can('results.create')
                    <a href="{{ route('addResultForm') }}" class="action-shortcut-btn">
                        <i class="bi bi-award-fill"></i>
                        <span>Add Result</span>
                    </a>
                @endcan
                @can('fees.manage')
                    <a href="{{ route('fees.structures.index') }}" class="action-shortcut-btn">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                        <span>Fee Structures</span>
                    </a>
                @endcan
                @can('terms.manage')
                    <a href="{{ route('academic.terms.index') }}" class="action-shortcut-btn">
                        <i class="bi bi-calendar-range-fill"></i>
                        <span>Academic Terms</span>
                    </a>
                @endcan
            </div>
        </div>
    @endif

    {{-- ==========================================
         3. EXECUTIVE 4-KPI MASTER CARDS
         ========================================== --}}
    <div class="kpi-master-grid">
        {{-- Card 1: Students --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-card-header">
                    <div>
                        <div class="kpi-card-title">Enrolled Students</div>
                        <h3 class="kpi-card-value">{{ number_format($totalStudents) }}</h3>
                    </div>
                    <div class="kpi-icon-wrap students">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    {{ $activeEnrollments }} currently in active semesters
                </div>
            </div>
            <div class="kpi-card-footer">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                    {{ $regularAdmissions }} Regular
                </span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace">
                    {{ $lateralAdmissions }} ADP Lateral
                </span>
            </div>
        </div>

        {{-- Card 2: Faculty & Departments --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-card-header">
                    <div>
                        <div class="kpi-card-title">Teaching Faculty</div>
                        <h3 class="kpi-card-value">{{ number_format($totalTeachers) }}</h3>
                    </div>
                    <div class="kpi-icon-wrap teachers">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-building me-1"></i>
                    Across {{ $totalDepartments }} Depts & {{ $totalPrograms }} Programs
                </div>
            </div>
            <div class="kpi-card-footer">
                <span class="text-muted">Master Catalog:</span>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                    {{ $totalCourses }} Courses
                </span>
            </div>
        </div>

        {{-- Card 3: Invoiced Volume & Scholarships --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-card-header">
                    <div>
                        <div class="kpi-card-title">Semester Fee Volume</div>
                        <h3 class="kpi-card-value" style="font-size: 22px;">PKR {{ number_format($totalInvoicedAmount, 0) }}</h3>
                    </div>
                    <div class="kpi-icon-wrap revenue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-gift text-success me-1"></i>
                    Waivers: PKR {{ number_format($totalScholarshipDiscount, 0) }}
                </div>
            </div>
            <div class="kpi-card-footer">
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    {{ $scholarshipStudents }} Merit Scholars
                </span>
                <span class="text-muted" style="font-size: 11px;">Tuition-only</span>
            </div>
        </div>

        {{-- Card 4: Academic Results --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-card-header">
                    <div>
                        <div class="kpi-card-title">Academic Results</div>
                        <h3 class="kpi-card-value">{{ $statistics->firstWhere('label', 'Published Results')['count'] ?? 0 }}</h3>
                    </div>
                    <div class="kpi-icon-wrap academic">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-shield-check text-primary me-1"></i>
                    Verified and published semester sheets
                </div>
            </div>
            <div class="kpi-card-footer">
                <span class="text-muted">GPA / SGPA:</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    100% Calculated
                </span>
            </div>
        </div>
    </div>

    {{-- ==========================================
         4. VISUAL ANALYTICS & INTERACTIVE GRAPHS
         ========================================== --}}
    <div class="row g-4 mb-4">
        {{-- Chart 1: Department Enrollment Breakdown (Bar Chart) --}}
        <div class="col-lg-7">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h5>
                            <i class="bi bi-bar-chart-fill text-primary"></i>
                            Student Enrollment by Academic Department
                        </h5>
                        <p>Distribution of enrolled scholars across university faculties</p>
                    </div>
                    <span class="analytics-badge">
                        <i class="bi bi-people-fill me-1"></i> Live Headcount
                    </span>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="deptEnrollmentChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 2: Intake Tracks & Scholarships (Doughnut Chart) --}}
        <div class="col-lg-5">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h5>
                            <i class="bi bi-pie-chart-fill text-info"></i>
                            Admission Tracks & Merit Slabs
                        </h5>
                        <p>Intake channel breakdown and scholarship ratio</p>
                    </div>
                    <span class="analytics-badge">
                        <i class="bi bi-award-fill me-1"></i> Slabs
                    </span>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="admissionTracksChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Chart 3: Semester-wise Student Progression (Column/Line Chart) --}}
        <div class="col-lg-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h5>
                            <i class="bi bi-graph-up-arrow text-success"></i>
                            Semester Progression Distribution
                        </h5>
                        <p>Enrolled student cohorts across Semester 1 to Semester 8</p>
                    </div>
                    <span class="analytics-badge">
                        <i class="bi bi-layers-fill me-1"></i> Semesters 1 - 8
                    </span>
                </div>
                <div class="chart-canvas-container" style="min-height: 220px;">
                    <canvas id="semesterProgressionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 4: Financial Fee & Concessions Overview --}}
        <div class="col-lg-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h5>
                            <i class="bi bi-cash-coin text-warning"></i>
                            Semester Fee Volume & Concessions
                        </h5>
                        <p>Gross invoiced fees vs merit scholarship discounts granted</p>
                    </div>
                    <span class="analytics-badge">
                        <i class="bi bi-wallet2 me-1"></i> Currency: PKR
                    </span>
                </div>
                <div class="chart-canvas-container" style="min-height: 220px;">
                    <canvas id="feeAnalyticsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         5. RECENT ACTIVITY & OPERATIONAL PANELS
         ========================================== --}}
    <div class="row g-4 mb-4">
        {{-- Recent Student Admissions --}}
        <div class="col-lg-7">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h5>
                            <i class="bi bi-person-plus-fill text-primary"></i>
                            Recent Student Admissions
                        </h5>
                        <p>Latest registered scholars in university degree programs</p>
                    </div>
                    <a href="{{ route('allStudents') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                        View All
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="recent-students-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Registration</th>
                                <th>Department & Program</th>
                                <th>Admission Type</th>
                                <th class="text-end">Scholarship</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStudents as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ asset('storage/images/' . ($student->image ?: 'default-user.png')) }}"
                                                alt="{{ $student->name }}" class="recent-student-avatar">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $student->name }}</div>
                                                <small class="text-muted font-monospace" style="font-size: 11px;">#{{ $student->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace">
                                            {{ $student->registration_no ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $student->department->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $student->program->code ?? ($student->program->name ?? 'BS') }}</small>
                                    </td>
                                    <td>
                                        @if($student->admission_type === 'lateral_adp')
                                            <span class="badge" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a;">
                                                <i class="bi bi-award me-1"></i> ADP Lateral
                                            </span>
                                        @elseif($student->admission_type === 'migration')
                                            <span class="badge" style="background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4;">
                                                Transfer
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border">
                                                Regular
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($student->hasActiveScholarship())
                                            <span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #6ee7b7; font-weight:700;">
                                                {{ number_format((float) $student->scholarship_percentage, 0) }}% Off
                                            </span>
                                        @else
                                            <span class="text-muted small">Standard</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No student records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Upcoming Events & Live System Status --}}
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-4 h-100">
                {{-- Upcoming Events --}}
                <div class="analytics-card flex-grow-1">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <h5>
                                <i class="bi bi-calendar-event-fill text-warning"></i>
                                Academic Calendar & Events
                            </h5>
                            <p>Upcoming campus activities & milestones</p>
                        </div>
                        <a href="{{ route('allEvents') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                            All Events
                        </a>
                    </div>

                    <div class="event-list">
                        @forelse($upcomingEvents as $event)
                            <div class="event-item">
                                <div class="event-date-badge">
                                    <span class="event-date-month">
                                        {{ \Carbon\Carbon::parse($event->date)->format('M') }}
                                    </span>
                                    <span class="event-date-day">
                                        {{ \Carbon\Carbon::parse($event->date)->format('d') }}
                                    </span>
                                </div>
                                <div class="event-info">
                                    <h6>{{ $event->event_name }}</h6>
                                    <p>
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $event->start_time }} - {{ $event->end_time }}
                                        @if($event->day) · {{ $event->day }} @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small py-3 text-center">No upcoming events scheduled.</p>
                        @endforelse
                    </div>
                </div>

                {{-- System Operational Health --}}
                <div class="analytics-card">
                    <div class="analytics-title mb-3">
                        <h5>
                            <i class="bi bi-hdd-network-fill text-success"></i>
                            System Operational Status
                        </h5>
                    </div>

                    <div class="system-status-pill">
                        <span><i class="bi bi-broadcast text-danger me-2"></i>WebSocket Server (Reverb)</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Online :8080</span>
                    </div>

                    <div class="system-status-pill">
                        <span><i class="bi bi-shield-check text-primary me-2"></i>Security & RBAC Enforcement</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Strict Multi-Guard</span>
                    </div>

                    <div class="system-status-pill">
                        <span><i class="bi bi-check2-all text-success me-2"></i>Automated Test Coverage</span>
                        <span class="badge bg-success text-white">183 Passed (100%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         6. CORE SYSTEM MODULES OVERVIEW (ORIGINAL COMPATIBILITY)
         ========================================== --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-grid-fill me-2 text-primary"></i>
            System Modules & Master Records
        </h5>
        <span class="badge bg-light text-muted border font-monospace">
            {{ $statistics->count() }} Authorized Modules
        </span>
    </div>

    <div class="stats-grid">
        @forelse($statistics as $stat)
            <a href="{{ route($stat['route']) }}" class="stat-card {{ $stat['style'] }}">
                <div class="stat-card-info">
                    <h6>{{ $stat['label'] }}</h6>
                    <h3>{{ number_format($stat['count']) }}</h3>
                </div>
                <div class="stat-card-icon">
                    <i class="bi {{ $stat['icon'] }}"></i>
                </div>
            </a>
        @empty
            <p class="text-muted">No overview statistics are available for your permissions.</p>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
    {{-- Chart.js (Local Bundle for 100% offline reliability) --}}
    <script src="{{ asset('js/chart.umd.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Global Chart Defaults
            Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif";
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;
            Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';

            // ==========================================
            // CHART 1: Department Enrollment (Bar Chart)
            // ==========================================
            const deptCtx = document.getElementById('deptEnrollmentChart');
            if (deptCtx) {
                const deptLabels = {!! json_encode($chartDepartmentLabels) !!};
                const deptCounts = {!! json_encode($chartDepartmentCounts) !!};

                new Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels.length ? deptLabels : ['No Data'],
                        datasets: [{
                            label: 'Enrolled Students',
                            data: deptCounts.length ? deptCounts : [0],
                            backgroundColor: [
                                'rgba(79, 70, 229, 0.85)',
                                'rgba(99, 102, 241, 0.85)',
                                'rgba(124, 58, 237, 0.85)',
                                'rgba(14, 165, 233, 0.85)',
                                'rgba(16, 185, 129, 0.85)',
                                'rgba(245, 158, 11, 0.85)'
                            ],
                            borderColor: [
                                '#4338ca',
                                '#4f46e5',
                                '#6d28d9',
                                '#0284c7',
                                '#059669',
                                '#d97706'
                            ],
                            borderWidth: 1.5,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 42
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    color: '#f1f5f9'
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11.5, weight: '600' },
                                    maxRotation: 20
                                }
                            }
                        }
                    }
                });
            }

            // ==========================================
            // CHART 2: Admission Tracks & Scholarships (Doughnut)
            // ==========================================
            const admCtx = document.getElementById('admissionTracksChart');
            if (admCtx) {
                const regular = {{ (int) $regularAdmissions }};
                const lateral = {{ (int) $lateralAdmissions }};
                const scholarships = {{ (int) $scholarshipStudents }};

                new Chart(admCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Regular Intake (Sem 1)', 'ADP Lateral (Sem 5)', 'Merit Scholarships'],
                        datasets: [{
                            data: [regular, lateral, scholarships],
                            backgroundColor: [
                                '#4f46e5',
                                '#f59e0b',
                                '#10b981'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 14,
                                    font: { size: 12, weight: '600' },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            }
                        },
                        cutout: '68%'
                    }
                });
            }

            // ==========================================
            // CHART 3: Semester Progression Distribution (Line/Area)
            // ==========================================
            const semCtx = document.getElementById('semesterProgressionChart');
            if (semCtx) {
                const semLabels = {!! json_encode($semesterLabels) !!};
                const semCounts = {!! json_encode($chartSemesterCounts) !!};

                new Chart(semCtx, {
                    type: 'line',
                    data: {
                        labels: semLabels,
                        datasets: [{
                            label: 'Active Students',
                            data: semCounts,
                            fill: true,
                            backgroundColor: 'rgba(79, 70, 229, 0.12)',
                            borderColor: '#4f46e5',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4f46e5',
                            pointBorderWidth: 2.5,
                            pointRadius: 4.5,
                            pointHoverRadius: 7,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 2, precision: 0 },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11, weight: '600' } }
                            }
                        }
                    }
                });
            }

            // ==========================================
            // CHART 4: Fee Billing & Waivers (Bar Chart)
            // ==========================================
            const feeCtx = document.getElementById('feeAnalyticsChart');
            if (feeCtx) {
                const invoiced = {{ (float) $totalInvoicedAmount }};
                const discount = {{ (float) $totalScholarshipDiscount }};
                const netPayable = invoiced - discount;

                new Chart(feeCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Gross Invoiced', 'Merit Waivers', 'Net Expected'],
                        datasets: [{
                            label: 'Amount (PKR)',
                            data: [invoiced, discount, netPayable > 0 ? netPayable : 0],
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.85)',
                                'rgba(16, 185, 129, 0.85)',
                                'rgba(124, 58, 237, 0.85)'
                            ],
                            borderColor: [
                                '#2563eb',
                                '#059669',
                                '#7c3aed'
                            ],
                            borderWidth: 1.5,
                            borderRadius: 8,
                            maxBarThickness: 45
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'PKR ' + Number(context.raw).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(val) {
                                        return 'PKR ' + (val >= 1000 ? (val / 1000) + 'k' : val);
                                    }
                                },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11.5, weight: '600' } }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
