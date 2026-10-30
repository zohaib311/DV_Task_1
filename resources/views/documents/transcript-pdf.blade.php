<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Official Academic Transcript - {{ $student->registration_no ?: $student->name }}</title>
    <style>
        @page {
            margin: 7mm 9mm 7mm 9mm;
            size: a4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Outer Certificate Security Frame */
        .cert-outer-frame {
            border: 2pt solid #0c2340;
            padding: 2.5pt;
            background: #ffffff;
        }

        .cert-inner-frame {
            border: 0.75pt solid #c5a059;
            padding: 8px 10px;
            background: #ffffff;
        }

        /* Formal Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .crest-cell {
            width: 65px;
            text-align: left;
            vertical-align: middle;
        }

        .header-title-cell {
            text-align: center;
            vertical-align: middle;
            padding: 0 5px;
        }

        .uni-name {
            font-family: 'DejaVu Serif', Georgia, 'Times New Roman', serif;
            font-size: 15pt;
            font-weight: bold;
            color: #0c2340;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }

        .uni-office {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7.5pt;
            font-weight: bold;
            color: #c5a059;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }

        .uni-meta {
            font-size: 6.5pt;
            color: #475569;
            margin: 0;
        }

        .header-badge-cell {
            width: 110px;
            text-align: right;
            vertical-align: middle;
        }

        .doc-badge-pill {
            display: inline-block;
            background-color: #0c2340;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 3px 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 2px;
            border-bottom: 1.5pt solid #c5a059;
        }

        .doc-meta-line {
            font-size: 6.5pt;
            color: #475569;
            margin-top: 2px;
        }

        /* Gold and Navy Dual Divider Line */
        .dual-divider {
            border-top: 1.5pt solid #0c2340;
            border-bottom: 0.75pt solid #c5a059;
            height: 2px;
            margin: 4px 0 7px 0;
        }

        /* Student Dossier Matrix */
        .bio-box {
            width: 100%;
            border: 0.75pt solid #d1d5db;
            background-color: #fbfbfd;
            margin-bottom: 7px;
            padding: 4px 6px;
        }

        .bio-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .bio-table td {
            padding: 2px 4px;
            vertical-align: middle;
        }

        .bio-lbl {
            color: #475569;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            width: 15%;
        }

        .bio-val {
            color: #0c2340;
            font-weight: bold;
            width: 35%;
        }

        /* Semester Record Blocks */
        .semester-wrap {
            margin-bottom: 6px;
            page-break-inside: avoid;
        }

        .semester-bar {
            background-color: #0c2340;
            color: #ffffff;
            padding: 2.5px 6px;
            font-weight: bold;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-left: 3pt solid #c5a059;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .records-table th {
            background-color: #f1f5f9;
            color: #0c2340;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            padding: 3px 5px;
            border-top: 0.5pt solid #94a3b8;
            border-bottom: 1pt solid #0c2340;
            letter-spacing: 0.3px;
        }

        .records-table td {
            padding: 2.5px 5px;
            border-bottom: 0.5pt solid #e2e8f0;
            color: #1e293b;
        }

        .records-table tr:nth-child(even) td {
            background-color: #fcfcfd;
        }

        .code-col {
            font-family: monospace;
            font-weight: bold;
            color: #0c2340;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .sem-stats-bar {
            background-color: #f8fafc;
            border-bottom: 0.75pt solid #cbd5e1;
            padding: 2.5px 6px;
            font-size: 7.5pt;
            color: #334155;
            text-align: right;
            font-weight: 600;
        }

        .sem-stats-bar strong {
            color: #0c2340;
        }

        /* Executive Cumulative Box */
        .summary-matrix {
            width: 100%;
            border: 1pt solid #0c2340;
            background-color: #fcfbf9;
            margin-top: 5px;
            margin-bottom: 7px;
            page-break-inside: avoid;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            text-align: center;
            vertical-align: middle;
            padding: 4px 5px;
        }

        .sum-title {
            font-size: 6.5pt;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }

        .sum-val {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0c2340;
        }

        .sum-val.highlight {
            color: #0c2340;
            font-size: 11pt;
        }

        .sum-val.status-good {
            color: #059669;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }

        /* Footer & Signatures */
        .footer-table {
            width: 100%;
            margin-top: 8px;
            page-break-inside: avoid;
            border-collapse: collapse;
        }

        .qr-box {
            width: 95px;
            text-align: center;
            vertical-align: bottom;
            padding-right: 10px;
        }

        .qr-subtext {
            font-size: 5.5pt;
            color: #475569;
            margin-top: 1px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .seal-cell {
            width: 85px;
            text-align: center;
            vertical-align: middle;
        }

        .sign-cell {
            vertical-align: bottom;
            text-align: center;
            width: 32%;
            padding: 0 8px;
        }

        .sign-line {
            border-top: 0.75pt solid #334155;
            width: 85%;
            margin: 20px auto 2px auto;
        }

        .sign-title {
            font-size: 7pt;
            font-weight: bold;
            color: #0c2340;
            text-transform: uppercase;
        }

        .sign-sub {
            font-size: 6pt;
            color: #64748b;
        }

        /* Official Grading Scale & Security Disclaimer */
        .legend-strip {
            border-top: 0.5pt solid #cbd5e1;
            padding-top: 3px;
            margin-top: 6px;
            font-size: 6pt;
            color: #64748b;
            line-height: 1.25;
            page-break-inside: avoid;
        }

        .legend-strip strong {
            color: #334155;
        }
    </style>
</head>

<body>

    <div class="cert-outer-frame">
        <div class="cert-inner-frame">

            <!-- Formal University Header -->
            <table class="header-table">
                <tr>
                    <td class="crest-cell">
                        <!-- Elegant Vector University Emblem -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="58" height="58">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="#0c2340" stroke-width="2"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#c5a059" stroke-width="0.8"/>
                            <path d="M50 18 C65 18 78 14 78 14 C78 48 70 70 50 82 C30 70 22 48 22 14 C22 14 35 18 50 18 Z" fill="#0c2340" stroke="#c5a059" stroke-width="1.2"/>
                            <!-- Open Book -->
                            <path d="M50 36 C45 32 38 32 34 34 L34 52 C38 50 45 50 50 54 C55 50 62 50 66 52 L66 34 C62 32 55 32 50 36 Z" fill="#ffffff" stroke="#c5a059" stroke-width="0.8"/>
                            <line x1="50" y1="36" x2="50" y2="54" stroke="#c5a059" stroke-width="1"/>
                            <!-- Star -->
                            <polygon points="50,22 51.5,26 56,26 52.5,28.5 54,33 50,30.5 46,33 47.5,28.5 44,26 48.5,26" fill="#c5a059"/>
                            <!-- Ribbon -->
                            <path d="M26 80 L36 75 L50 78 L64 75 L74 80 L68 85 L50 82 L32 85 Z" fill="#c5a059"/>
                            <text x="50" y="80.5" font-family="'DejaVu Sans', sans-serif" font-size="4" font-weight="bold" fill="#0c2340" text-anchor="middle" letter-spacing="0.5">VERITAS</text>
                        </svg>
                    </td>
                    <td class="header-title-cell">
                        <div class="uni-name">{{ $university['name'] }}</div>
                        <div class="uni-office">Office of the Controller of Examinations</div>
                        <div class="uni-meta">
                            {{ $university['sub_title'] }} &bull; {{ $university['address'] }} &bull; Phone: {{ $university['phone'] }}
                        </div>
                    </td>
                    <td class="header-badge-cell">
                        <div class="doc-badge-pill">Official Transcript</div>
                        <div class="doc-meta-line">Ref: <strong>TR-{{ $student->registration_no ?: $student->id }}</strong></div>
                        <div class="doc-meta-line">Date: {{ $transcript['issued_at']->format('d M Y') }}</div>
                    </td>
                </tr>
            </table>

            <div class="dual-divider"></div>

            <!-- Student Bio Data Matrix -->
            <div class="bio-box">
                <table class="bio-table">
                    <tr>
                        <td class="bio-lbl">Candidate Name:</td>
                        <td class="bio-val">{{ strtoupper($student->name) }}</td>
                        <td class="bio-lbl">Registration No:</td>
                        <td class="bio-val" style="font-family: monospace;">{{ $student->registration_no ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="bio-lbl">Degree Program:</td>
                        <td class="bio-val">{{ $student->program?->name ?: ($student->program?->code ?? 'Undergraduate Degree Program') }}</td>
                        <td class="bio-lbl">Department:</td>
                        <td class="bio-val">{{ $student->department?->name ?? 'Department of Information Technology' }}</td>
                    </tr>
                    <tr>
                        <td class="bio-lbl">Section &amp; Shift:</td>
                        <td class="bio-val">Section {{ $student->section?->name ?? 'A' }} &bull; Regular (Morning)</td>
                        <td class="bio-lbl">Grading Scale:</td>
                        <td class="bio-val">4.00 Maximum CGPA Scale (HEC Standard)</td>
                    </tr>
                </table>
            </div>

            <!-- Semester Records Flow -->
            @forelse($transcript['semesters'] as $sem)
                <div class="semester-wrap">
                    <div class="semester-bar">
                        {{ $sem['semester'] }} &mdash; Academic Session {{ $sem['academic_year'] }}
                    </div>
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th style="width: 14%; text-align: left;">Course Code</th>
                                <th style="width: 44%; text-align: left;">Course Title</th>
                                <th style="width: 8%;" class="text-center">Cr. Hrs</th>
                                <th style="width: 10%;" class="text-center">Marks %</th>
                                <th style="width: 8%;" class="text-center">Grade</th>
                                <th style="width: 8%;" class="text-center">Grade Pt.</th>
                                <th style="width: 8%;" class="text-center">Outcome</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sem['courses'] as $course)
                                <tr>
                                    <td class="code-col">{{ $course['code'] }}</td>
                                    <td>{{ $course['name'] }}</td>
                                    <td class="text-center">{{ number_format($course['credits'], 1) }}</td>
                                    <td class="text-center">{{ number_format($course['percentage'], 1) }}%</td>
                                    <td class="text-center"><strong>{{ $course['grade'] }}</strong></td>
                                    <td class="text-center">{{ number_format($course['grade_point'], 2) }}</td>
                                    <td class="text-center" style="font-size: 6.5pt; color: {{ $course['grade'] === 'F' ? '#dc2626' : '#166534' }}; font-weight: bold;">
                                        {{ $course['repeated'] ? 'Repeat' : ($course['grade'] === 'F' ? 'Fail' : 'Pass') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center" style="color: #94a3b8; padding: 5px;">
                                        No cleared course outcomes recorded for this semester.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="sem-stats-bar">
                        Semester Credits: <strong>{{ number_format($sem['courses']->sum('credits'), 1) }}</strong>
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                        SGPA: <strong>{{ number_format((float) $sem['sgpa'], 2) }}</strong>
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                        Cumulative CGPA: <strong>{{ number_format((float) $sem['cgpa'], 2) }}</strong>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">
                    No officially published semester records found for this student.
                </div>
            @endforelse

            <!-- Cumulative Degree Summary Matrix -->
            <div class="summary-matrix">
                <table class="summary-table">
                    <tr>
                        <td style="width: 25%; border-right: 0.5pt solid #d1d5db;">
                            <div class="sum-title">Published Semesters</div>
                            <div class="sum-val">{{ $transcript['published_semesters'] }}</div>
                        </td>
                        <td style="width: 25%; border-right: 0.5pt solid #d1d5db;">
                            <div class="sum-title">Total Earned Credits</div>
                            <div class="sum-val">{{ number_format((float) $transcript['earned_credits'], 1) }}</div>
                        </td>
                        <td style="width: 25%; border-right: 0.5pt solid #d1d5db;">
                            <div class="sum-title">Cumulative GPA (CGPA)</div>
                            <div class="sum-val highlight">{{ number_format((float) $transcript['cgpa'], 2) }} <span style="font-size: 7pt; color: #64748b;">/ 4.00</span></div>
                        </td>
                        <td style="width: 25%;">
                            <div class="sum-title">Academic Standing</div>
                            <div class="sum-val status-good">&#10004; GOOD STANDING</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer: QR Verification, Seal, and Signatures -->
            <table class="footer-table">
                <tr>
                    <td class="qr-box">
                        @if (!empty($transcript['qr_code']))
                            <img src="{{ $transcript['qr_code'] }}" width="62" height="62" alt="Verification QR Code">
                            <div class="qr-subtext">Scan to Authenticate</div>
                        @endif
                    </td>
                    <td class="seal-cell">
                        <!-- Official Embossed University Seal Graphic -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="60" height="60">
                            <circle cx="50" cy="50" r="47" fill="none" stroke="#0c2340" stroke-width="1.5" stroke-dasharray="2.5,1.5"/>
                            <circle cx="50" cy="50" r="43" fill="none" stroke="#c5a059" stroke-width="1"/>
                            <circle cx="50" cy="50" r="33" fill="none" stroke="#0c2340" stroke-width="0.8"/>
                            <text x="50" y="24" font-family="'DejaVu Sans', sans-serif" font-size="4.2" font-weight="bold" fill="#0c2340" text-anchor="middle" letter-spacing="1">EXAMINATIONS</text>
                            <text x="50" y="44" font-family="'DejaVu Serif', serif" font-size="5.5" font-weight="bold" fill="#0c2340" text-anchor="middle" letter-spacing="0.5">OFFICIAL</text>
                            <text x="50" y="52" font-family="'DejaVu Sans', sans-serif" font-size="5" font-weight="bold" fill="#c5a059" text-anchor="middle" letter-spacing="1">&#9733; SEAL &#9733;</text>
                            <text x="50" y="60" font-family="'DejaVu Serif', serif" font-size="4.5" fill="#0c2340" text-anchor="middle" letter-spacing="0.5">VERIFIED</text>
                            <text x="50" y="79" font-family="'DejaVu Sans', sans-serif" font-size="4.2" font-weight="bold" fill="#0c2340" text-anchor="middle" letter-spacing="1">CONTROLLER</text>
                        </svg>
                    </td>
                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-title">Prepared &amp; Verified By</div>
                        <div class="sign-sub">Examinations &amp; Records Division</div>
                    </td>
                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-title">Controller of Examinations</div>
                        <div class="sign-sub">{{ $university['name'] }}</div>
                    </td>
                </tr>
            </table>

            <!-- Standard Grading Legend & Security Checksum -->
            <div class="legend-strip">
                <strong>HEC Standard 4.00 Grading Scale:</strong>
                A (85-100% / 4.00) &bull; A- (80-84% / 3.70) &bull; B+ (75-79% / 3.30) &bull; B (70-74% / 3.00) &bull; B- (65-69% / 2.70) &bull; C+ (61-64% / 2.30) &bull; C (58-60% / 2.00) &bull; C- (55-57% / 1.70) &bull; D (50-54% / 1.00) &bull; F (&lt;50% / 0.00).<br>
                <strong>Security Authentication Notice:</strong> This transcript is digitally signed and tamper-protected. Verify online by scanning the QR code or visiting: <em>{{ route('verify.document', ['type' => 'transcript', 'hash' => $transcript['verification_hash']]) }}</em> &bull; <strong>Checksum:</strong> <span style="font-family: monospace;">{{ substr($transcript['verification_hash'], 0, 24) }}...</span>
            </div>

        </div>
    </div>

</body>

</html>
