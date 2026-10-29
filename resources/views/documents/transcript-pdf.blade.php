<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Official Academic Transcript - {{ $student->registration_no }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2.5pt solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .uni-name {
            font-size: 17pt;
            font-weight: bold;
            color: #312e81;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .uni-sub {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h1 {
            font-size: 13pt;
            margin: 0;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .doc-title span {
            font-size: 8pt;
            color: #64748b;
        }

        .profile-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1pt solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 14px;
            padding: 6px 10px;
            font-size: 8.5pt;
        }

        .profile-table td {
            padding: 3px 6px;
        }

        .profile-label {
            color: #64748b;
            font-weight: normal;
            width: 15%;
        }

        .profile-val {
            color: #0f172a;
            font-weight: bold;
            width: 35%;
        }

        .semester-block {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .semester-title {
            background-color: #e0e7ff;
            color: #312e81;
            font-weight: bold;
            padding: 4px 8px;
            font-size: 9pt;
            border-left: 3pt solid #4f46e5;
            margin-bottom: 4px;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 6px;
        }

        .records-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border-bottom: 1.5pt solid #cbd5e1;
        }

        .records-table td {
            padding: 4px 6px;
            border-bottom: 0.5pt solid #e2e8f0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .sem-summary {
            text-align: right;
            font-size: 8pt;
            color: #334155;
            padding: 2px 6px;
            font-weight: bold;
        }

        .cgpa-box {
            width: 100%;
            border: 1.5pt solid #4f46e5;
            background-color: #eef2ff;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 10px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .cgpa-table {
            width: 100%;
            font-size: 9.5pt;
        }

        .footer-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            font-size: 8pt;
        }

        .sign-line {
            border-top: 1pt solid #475569;
            width: 80%;
            margin: 30px auto 4px auto;
        }

        .qr-cell {
            width: 100px;
            text-align: center;
            vertical-align: middle;
        }

        .qr-text {
            font-size: 7pt;
            color: #64748b;
            margin-top: 2px;
        }

        .grading-legend {
            font-size: 7pt;
            color: #64748b;
            border-top: 0.5pt solid #cbd5e1;
            padding-top: 4px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 65%;">
                <div class="uni-name">{{ $university['name'] }}</div>
                <div class="uni-sub">{{ $university['sub_title'] }} · {{ $university['address'] }}</div>
            </td>
            <td class="doc-title" style="width: 35%;">
                <h1>Official Transcript</h1>
                <span>Verified Academic Record</span><br>
                <small style="font-size: 7.5pt; color: #475569;">Issue Ref:
                    TR-{{ $student->registration_no ?: $student->id }}</small>
            </td>
        </tr>
    </table>

    <table class="profile-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="profile-label">Student Name:</td>
            <td class="profile-val">{{ $student->name }}</td>
            <td class="profile-label">Registration No:</td>
            <td class="profile-val">{{ $student->registration_no ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="profile-label">Program:</td>
            <td class="profile-val">{{ $student->program?->name ?: $student->program?->code ?? 'Undergraduate' }}</td>
            <td class="profile-label">Department:</td>
            <td class="profile-val">{{ $student->department?->name ?? 'Academic Dept' }}</td>
        </tr>
        <tr>
            <td class="profile-label">Section:</td>
            <td class="profile-val">{{ $student->section?->name ?? 'A' }}</td>
            <td class="profile-label">Date Issued:</td>
            <td class="profile-val">{{ $transcript['issued_at']->format('d M Y') }}</td>
        </tr>
    </table>

    @forelse($transcript['semesters'] as $sem)
        <div class="semester-block">
            <div class="semester-title">
                {{ $sem['semester'] }} ({{ $sem['academic_year'] }})
            </div>
            <table class="records-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Code</th>
                        <th style="width: 44%;">Course Title</th>
                        <th style="width: 10%;" class="text-center">Cr. Hrs</th>
                        <th style="width: 10%;" class="text-center">Marks</th>
                        <th style="width: 8%;" class="text-center">Grade</th>
                        <th style="width: 8%;" class="text-center">G.P.</th>
                        <th style="width: 8%;" class="text-center">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sem['courses'] as $course)
                        <tr>
                            <td style="font-family: monospace; font-weight: bold;">{{ $course['code'] }}</td>
                            <td>{{ $course['name'] }}</td>
                            <td class="text-center">{{ number_format($course['credits'], 1) }}</td>
                            <td class="text-center">{{ number_format($course['percentage'], 1) }}%</td>
                            <td class="text-center"><strong>{{ $course['grade'] }}</strong></td>
                            <td class="text-center">{{ number_format($course['grade_point'], 2) }}</td>
                            <td class="text-center" style="font-size: 7.5pt; color: #64748b;">
                                {{ $course['repeated'] ? 'Repeat' : 'Pass' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="color: #94a3b8;">No cleared course outcomes
                                for this semester.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="sem-summary">
                Semester GPA (SGPA): <span
                    style="color: #4f46e5; font-size: 9pt;">{{ number_format((float) $sem['sgpa'], 2) }}</span>
                &nbsp;·&nbsp;
                Cumulative GPA (CGPA): <span
                    style="color: #1e1b4b; font-size: 9pt;">{{ number_format((float) $sem['cgpa'], 2) }}</span>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 30px; color: #94a3b8;">
            No published semester records are available for this student.
        </div>
    @endforelse

    <div class="cgpa-box">
        <table class="cgpa-table">
            <tr>
                <td style="width: 35%;">
                    <span style="color: #64748b; font-size: 8pt;">TOTAL CREDIT HOURS EARNED</span><br>
                    <strong
                        style="font-size: 13pt; color: #312e81;">{{ number_format((float) $transcript['earned_credits'], 1) }}</strong>
                </td>
                <td style="width: 35%;">
                    <span style="color: #64748b; font-size: 8pt;">CUMULATIVE GRADE POINT AVERAGE</span><br>
                    <strong
                        style="font-size: 13pt; color: #4f46e5;">{{ number_format((float) $transcript['cgpa'], 2) }} /
                        4.00</strong>
                </td>
                <td style="width: 30%; text-align: right;">
                    <span style="color: #64748b; font-size: 8pt;">ACADEMIC STATUS</span><br>
                    <strong style="font-size: 12pt; color: #047857;">ACTIVE / GOOD STANDING</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="qr-cell">
                @if (!empty($transcript['qr_code']))
                    <img src="{{ $transcript['qr_code'] }}" width="80" height="80" alt="Verification QR Code">
                    <div class="qr-text">Scan to Verify</div>
                @endif
            </td>
            <td class="sign-box">
                <div class="sign-line"></div>
                <strong>Prepared &amp; Verified By</strong><br>
                <span>Examination Branch</span>
            </td>
            <td class="sign-box">
                <div class="sign-line"></div>
                <strong>Controller of Examinations</strong><br>
                <span>{{ $university['name'] }}</span>
            </td>
        </tr>
    </table>

    <div class="grading-legend">
        <strong>Grading Scale (4.00 Max):</strong>
        A (85-100% / 4.00) · A- (80-84% / 3.70) · B+ (75-79% / 3.30) · B (70-74% / 3.00) · B- (65-69% / 2.70) · C+
        (61-64% / 2.30) · C (58-60% / 2.00) · C- (55-57% / 1.70) · D (50-54% / 1.00) · F (&lt;50% / 0.00).
        <br>
        <em>Note: This official transcript is electronically authenticated and protected with an encrypted verification
            hash: {{ substr($transcript['verification_hash'], 0, 16) }}...</em>
    </div>

</body>

</html>
