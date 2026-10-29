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
    <section class="portal-summary-grid fee-summary-grid">
        <div class="portal-summary-card">
            <span>Fee clearance</span>
            @if ($eligibility['fee_cleared'])
                <strong style="color: #059669;">Cleared</strong>
                <small>Semester invoice paid</small>
            @else
                <strong style="color: #dc2626;">Pending</strong>
                <small>Unpaid invoice dues</small>
            @endif
        </div>
        <div class="portal-summary-card">
            <span>Overall attendance</span>
            @if ($eligibility['attendance_percentage'] !== null)
                <strong style="color: {{ $eligibility['attendance_percentage'] >= 75 ? '#059669' : '#dc2626' }};">
                    {{ $eligibility['attendance_percentage'] }}%
                </strong>
                <small>Minimum 75.0% required</small>
            @else
                <strong>N/A</strong>
                <small>No sessions recorded</small>
            @endif
        </div>
        <div class="portal-summary-card">
            <span>Examination status</span>
            @if ($eligibility['eligible'])
                <strong style="color: #059669;">Eligible</strong>
                <small>Cleared to sit for exams</small>
            @else
                <strong style="color: #dc2626;">Blocked</strong>
                <small>Clearance requirements pending</small>
            @endif
        </div>
    </section>

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
            <a href="{{ route('student.admit-card.pdf') }}" class="portal-btn"
                style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="bi bi-file-earmark-pdf-fill" aria-hidden="true"></i> Download Official Admit Card (PDF)
            </a>
        </div>
    @endif
@endsection
