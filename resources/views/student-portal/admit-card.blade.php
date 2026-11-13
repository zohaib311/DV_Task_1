@extends('student-portal.layout')

@section('heading', 'Examination admit card')
@section('description', 'Check your semester examination clearance, attendance threshold, and download your roll number
    slip.')

@section('header-actions')
    @if ($eligibility['eligible'])
        <a class="btn academic-header-btn" href="{{ route('student.admit-card.pdf') }}">
            <i class="bi bi-file-earmark-pdf-fill" aria-hidden="true"></i> Download Admit Card (PDF)
        </a>
    @endif
@endsection

@section('student-content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="portal-stat-card h-100">
                <span class="portal-stat-icon" style="color: {{ $eligibility['fee_cleared'] ? '#16a34a' : '#dc2626' }}; background: {{ $eligibility['fee_cleared'] ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.1)' }};">
                    <i class="bi {{ $eligibility['fee_cleared'] ? 'bi-wallet-fill' : 'bi-exclamation-triangle-fill' }}" aria-hidden="true"></i>
                </span>
                <div class="portal-stat-content">
                    <small>Fee clearance</small>
                    <strong style="color: {{ $eligibility['fee_cleared'] ? '#16a34a' : '#dc2626' }};">
                        {{ $eligibility['fee_cleared'] ? 'Cleared' : 'Pending' }}
                    </strong>
                    <span class="text-muted" style="font-size: 0.72rem;">{{ $eligibility['fee_cleared'] ? 'Semester invoice paid' : 'Unpaid invoice dues' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="portal-stat-card h-100">
                <span class="portal-stat-icon" style="color: {{ ($eligibility['attendance_percentage'] ?? 0) >= 75 ? '#16a34a' : '#dc2626' }}; background: {{ ($eligibility['attendance_percentage'] ?? 0) >= 75 ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.1)' }};">
                    <i class="bi bi-percent" aria-hidden="true"></i>
                </span>
                <div class="portal-stat-content">
                    <small>Overall attendance</small>
                    @if ($eligibility['attendance_percentage'] !== null)
                        <strong style="color: {{ $eligibility['attendance_percentage'] >= 75 ? '#16a34a' : '#dc2626' }};">
                            {{ $eligibility['attendance_percentage'] }}%
                        </strong>
                        <span class="text-muted" style="font-size: 0.72rem;">Minimum 75.0% required</span>
                    @else
                        <strong>N/A</strong>
                        <span class="text-muted" style="font-size: 0.72rem;">No sessions recorded</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="portal-stat-card h-100">
                <span class="portal-stat-icon" style="color: {{ $eligibility['eligible'] ? '#16a34a' : '#dc2626' }}; background: {{ $eligibility['eligible'] ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.1)' }};">
                    <i class="bi {{ $eligibility['eligible'] ? 'bi-patch-check-fill' : 'bi-shield-x' }}" aria-hidden="true"></i>
                </span>
                <div class="portal-stat-content">
                    <small>Examination status</small>
                    <strong style="color: {{ $eligibility['eligible'] ? '#16a34a' : '#dc2626' }};">
                        {{ $eligibility['eligible'] ? 'Eligible' : 'Blocked' }}
                    </strong>
                    <span class="text-muted" style="font-size: 0.72rem;">{{ $eligibility['eligible'] ? 'Cleared to sit for exams' : 'Requirements pending' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (!$eligibility['eligible'])
        <div class="academic-note portal-note-with-icon fee-note"
            style="border-left-color: #dc2626; background-color: #fef2f2;">
            <i class="bi bi-exclamation-octagon-fill" style="color: #dc2626;" aria-hidden="true"></i>
            <div>
                <strong style="color: #991b1b; display: block; margin-bottom: 2px;">Examination Clearance Blocked</strong>
                <span style="color: #7f1d1d;">{{ $eligibility['message'] }}</span>
            </div>
        </div>
    @else
        <div class="academic-note portal-note-with-icon fee-note"
            style="border-left-color: #059669; background-color: #ecfdf5;">
            <i class="bi bi-patch-check-fill" style="color: #059669;" aria-hidden="true"></i>
            <div>
                <strong style="color: #065f46; display: block; margin-bottom: 2px;">Clearance Granted</strong>
                <span style="color: #047857;">You have met the required fee and attendance criteria for
                    {{ $enrollment?->semester ?? 'this semester' }}. Download your official examination pass below.</span>
            </div>
        </div>
    @endif

    <section class="fee-structure-preview">
        <div class="portal-section-heading">
            <div>
                <h2>Registered examination courses</h2>
                <p>Courses appearing on your candidate admit card for {{ $enrollment?->semester ?? 'current semester' }}
                    ({{ $enrollment?->academic_year }}).</p>
            </div>
            <span class="portal-badge is-info">{{ $enrollment?->semester ?? 'Active Semester' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table academic-table">
                <thead>
                    <tr>
                        <th>Course code</th>
                        <th>Course title</th>
                        <th>Credit hours</th>
                        <th>Type</th>
                        <th>Examination center</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td><strong style="font-family: monospace;">{{ $course->course_code }}</strong></td>
                            <td>{{ $course->course_name }}</td>
                            <td>{{ number_format((float) $course->credit_hours, 1) }}</td>
                            <td><span class="portal-badge is-neutral">{{ ucfirst($course->registration_type) }}</span></td>
                            <td>Central Examination Hall - Block A</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="portal-empty-state">No registered courses found for your active
                                enrollment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($eligibility['eligible'])
        <div style="margin-top: 24px; text-align: right;">
            <a href="{{ route('student.admit-card.pdf') }}" class="btn btn-primary portal-btn"
                style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #ffffff !important; font-weight: 600;">
                <i class="bi bi-file-earmark-pdf-fill" aria-hidden="true" style="color: #ffffff !important;"></i> Download Official Admit Card (PDF)
            </a>
        </div>
    @endif
@endsection
