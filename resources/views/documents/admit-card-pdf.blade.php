<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Examination Admit Card - {{ $student->registration_no }}</title>
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/documents/admit-card-pdf.css') }}">
</head>

<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 65%;">
                <div class="uni-name">{{ $university['name'] }}</div>
                <div class="uni-sub">{{ $university['sub_title'] }} · Examination Directorate</div>
            </td>
            <td class="doc-title" style="width: 35%;">
                <h1>Admit Card</h1>
                <span style="font-size: 8pt;">Examination Roll Slip</span><br>
                <small style="font-size: 7.5pt; color: #64748b;">{{ $enrollment->term?->name ?? 'Term' }} ·
                    {{ $enrollment->academic_year }}</small>
            </td>
        </tr>
    </table>

    <div class="clearance-banner">
        <strong>✓ OFFICIAL EXAMINATION CLEARANCE:</strong>
        Fee status verified (Cleared/Paid) &nbsp;·&nbsp;
        Course attendance verified ({{ $eligibility['attendance_percentage'] }}% &ge; 75.0% Required) &nbsp;·&nbsp;
        Candidate is eligible to appear in all scheduled papers.
    </div>

    <table class="profile-container" cellpadding="0" cellspacing="0">
        <tr>
            <td class="profile-info">
                <table style="width: 100%; font-size: 8.5pt;">
                    <tr>
                        <td class="profile-label">Student Name:</td>
                        <td class="profile-val">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Registration No:</td>
                        <td class="profile-val" style="font-size: 10pt; color: #0369a1;">
                            {{ $student->registration_no ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Program:</td>
                        <td class="profile-val">
                            {{ $student->program?->name ?: $student->program?->code ?? 'Undergraduate' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Department / Sec:</td>
                        <td class="profile-val">{{ $student->department?->name ?? 'Academic Dept' }} ·
                            {{ $student->section?->name ?? 'A' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Semester:</td>
                        <td class="profile-val">{{ $enrollment->semester }} ({{ $enrollment->academic_year }})</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Exam Hall / Center:</td>
                        <td class="profile-val" style="color: #0369a1;">{{ $exam_hall }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;"></td>
            <td class="photo-box">
                <div class="photo-placeholder">
                    CANDIDATE PHOTO
                </div>
            </td>
        </tr>
    </table>

    <table class="courses-table">
        <thead>
            <tr>
                <th style="width: 15%;">Course Code</th>
                <th style="width: 45%;">Course Title</th>
                <th style="width: 10%;" class="text-center">Cr. Hrs</th>
                <th style="width: 15%;" class="text-center">Exam Center</th>
                <th style="width: 15%;" class="text-center">Invigilator Sign</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courses as $course)
                <tr>
                    <td style="font-weight: bold; font-family: monospace;">{{ $course->course_code }}</td>
                    <td>{{ $course->course_name }}</td>
                    <td class="text-center">{{ number_format((float) $course->credit_hours, 1) }}</td>
                    <td class="text-center" style="font-size: 7.5pt;">{{ $exam_hall }}</td>
                    <td style="border-bottom: 0.5pt solid #cbd5e1;"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No registered examination courses for this semester.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="instructions-box">
        <h4>Important Examination Instructions:</h4>
        <ol>
            <li>Candidates must present this original <strong>Admit Card</strong> along with their <strong>Official
                    Student ID Card</strong> at the entrance.</li>
            <li>Mobile phones, smartwatches, recording devices, programmable calculators, or any loose papers are
                strictly forbidden inside the hall.</li>
            <li>No candidate will be admitted to the examination center after 15 minutes of the scheduled examination
                commencement time.</li>
            <li>Use of unfair means (UFM) will result in immediate cancellation of paper and disciplinary proceedings
                under university regulations.</li>
        </ol>
    </div>

    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="qr-cell">
                @if (!empty($qr_code))
                    <img src="{{ $qr_code }}" width="75" height="75" alt="QR Code">
                    <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">Scan to Verify</div>
                @endif
            </td>
            <td class="sign-box">
                <div class="sign-line"></div>
                <strong>Candidate's Signature</strong><br>
                <span>(Must sign in presence of invigilator)</span>
            </td>
            <td class="sign-box">
                <div class="sign-line"></div>
                <strong>Controller of Examinations</strong><br>
                <span>{{ $university['name'] }}</span>
            </td>
        </tr>
    </table>

</body>

</html>
