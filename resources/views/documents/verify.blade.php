<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Official Verification Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --success: #059669;
            --danger: #dc2626;
            --surface: #ffffff;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .verify-header {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }

        .uni-brand {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .uni-dept {
            font-size: 0.85rem;
            opacity: 0.85;
        }

        .verify-body {
            padding: 28px 24px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .status-valid {
            background-color: #ecfdf5;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .status-invalid {
            background-color: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .status-badge i {
            font-size: 1.1rem;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 0.92rem;
        }

        .details-table tr {
            border-bottom: 1px solid var(--border);
        }

        .details-table tr:last-child {
            border-bottom: none;
        }

        .details-table td {
            padding: 10px 4px;
        }

        .label-col {
            color: var(--text-muted);
            width: 40%;
        }

        .val-col {
            font-weight: 600;
            color: var(--text-main);
            width: 60%;
        }

        .security-footer {
            background-color: #f8fafc;
            border-top: 1px solid var(--border);
            padding: 16px 24px;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
        }

        .hash-code {
            font-family: monospace;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            word-break: break-all;
            display: block;
            margin-top: 6px;
        }
    </style>
</head>

<body>

    <div class="verify-card">
        <header class="verify-header">
            <div class="uni-brand">{{ config('fees.university.name', 'DevVerx Institute of Higher Education') }}</div>
            <div class="uni-dept">Academic Examination Directorate · Document Authenticity System</div>
        </header>

        <div class="verify-body">
            @if ($is_valid)
                <div style="text-align: center;">
                    <div class="status-badge status-valid">
                        <i class="bi bi-patch-check-fill"></i>
                        <span>OFFICIALLY VERIFIED &amp; AUTHENTIC RECORD</span>
                    </div>
                </div>

                <h3 style="font-size: 1.1rem; margin-bottom: 16px; color: #1e1b4b;">{{ $title }}</h3>

                <table class="details-table">
                    @foreach ($record as $label => $val)
                        <tr>
                            <td class="label-col">{{ ucwords(str_replace('_', ' ', $label)) }}:</td>
                            <td class="val-col">{{ $val }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div style="text-align: center;">
                    <div class="status-badge status-invalid">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>DOCUMENT VERIFICATION FAILED</span>
                    </div>
                    <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.95rem;">
                        No authentic university record was found corresponding to this verification token.
                    </p>
                    <p style="font-size: 0.85rem; color: #94a3b8;">
                        Please verify that you scanned an official, unaltered document generated by the university
                        academic portal.
                    </p>
                </div>
            @endif
        </div>

        <footer class="security-footer">
            <div>
                <strong>Tamper-Proof Digital Verification</strong><br>
                Verification Token Hash:
                <span class="hash-code">{{ $hash }}</span>
            </div>
            <div style="margin-top: 8px;">
                Verified at: {{ $verified_at->format('d M Y, h:i A') }} (Server Time)
            </div>
        </footer>
    </div>

</body>

</html>
