<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Examination Admit Card - {{ $student->registration_no }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2pt solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .uni-name {
            font-size: 15pt;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
        }

        .uni-sub {
            font-size: 8pt;
            color: #64748b;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h1 {
            font-size: 12pt;
            margin: 0;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .clearance-banner {
            background-color: #f0fdf4;
            border: 1pt solid #86efac;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8pt;
            color: #166534;
        }

        .profile-container {
            width: 100%;
            margin-bottom: 14px;
        }

        .profile-info {
            width: 78%;
            background-color: #f8fafc;
            border: 1pt solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 8.5pt;
        }

        .profile-info td {
            padding: 3px 6px;
        }

        .profile-label {
            color: #64748b;
            width: 25%;
        }

        .profile-val {
            font-weight: bold;
            color: #0f172a;
            width: 75%;
        }

        .photo-box {
            width: 20%;
            text-align: center;
            vertical-align: top;
        }

        .photo-placeholder {
            width: 90px;
            height: 110px;
            border: 1pt solid #cbd5e1;
            background-color: #f1f5f9;
            margin: 0 auto;
            text-align: center;
            line-height: 110px;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 14px;
        }

        .courses-table th {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            padding: 5px 8px;
            border: 0.5pt solid #bae6fd;
            text-align: left;
        }

        .courses-table td {
            padding: 6px 8px;
            border: 0.5pt solid #e2e8f0;
        }

        .text-center {
            text-align: center;
        }

        .instructions-box {
            background-color: #fffbeb;
            border: 1pt solid #fef3c7;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 7.8pt;
            color: #92400e;
        }

        .instructions-box h4 {
            margin: 0 0 4px 0;
            color: #b45309;
            text-transform: uppercase;
            font-size: 8.2pt;
        }

        .instructions-box ol {
            margin: 0;
            padding-left: 16px;
        }

        .instructions-box li {
            margin-bottom: 2px;
        }

        .footer-table {
            width: 100%;
            margin-top: 15px;
        }

        .sign-box {
            text-align: center;
            width: 40%;
            vertical-align: bottom;
            font-size: 8pt;
        }

        .sign-line {
            border-top: 1pt solid #475569;
            width: 80%;
            margin: 25px auto 4px auto;
        }

        .qr-cell {
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
    </style>
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
