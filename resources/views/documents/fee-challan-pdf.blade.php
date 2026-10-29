<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Challan - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        .challan-container {
            width: 100%;
            border-collapse: collapse;
        }

        .challan-slip {
            width: 32.5%;
            vertical-align: top;
            padding: 0 10px;
        }

        .border-right {
            border-right: 1.5pt dashed #94a3b8;
        }

        .slip-header {
            text-align: center;
            border-bottom: 1pt solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .bank-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }

        .uni-title {
            font-size: 8pt;
            font-weight: bold;
            color: #334155;
        }

        .copy-type {
            display: inline-block;
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 6px;
            font-size: 7.2pt;
        }

        .info-table td {
            padding: 1.5px 0;
        }

        .info-label {
            color: #64748b;
            width: 38%;
        }

        .info-val {
            font-weight: bold;
            color: #0f172a;
            width: 62%;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 6px;
        }

        .breakdown-table th {
            background-color: #f1f5f9;
            color: #334155;
            padding: 3px 4px;
            border-top: 0.5pt solid #cbd5e1;
            border-bottom: 0.5pt solid #cbd5e1;
            text-align: left;
        }

        .breakdown-table td {
            padding: 2.5px 4px;
            border-bottom: 0.5pt dotted #e2e8f0;
        }

        .text-right {
            text-align: right;
        }

        .total-box {
            background-color: #f8fafc;
            border: 1pt solid #cbd5e1;
            padding: 4px;
            margin-bottom: 6px;
        }

        .total-row {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
        }

        .bank-details {
            background-color: #f0fdf4;
            border: 0.5pt solid #bbf7d0;
            padding: 3px 5px;
            font-size: 6.8pt;
            margin-bottom: 6px;
            color: #166534;
        }

        .instructions {
            font-size: 6.2pt;
            color: #64748b;
            line-height: 1.25;
            margin-bottom: 8px;
        }

        .sign-area {
            width: 100%;
            margin-top: 15px;
            font-size: 6.8pt;
        }

        .sign-box {
            width: 48%;
            text-align: center;
            border-top: 0.5pt solid #64748b;
            padding-top: 2px;
        }

        .qr-section {
            text-align: center;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <table class="challan-container" cellpadding="0" cellspacing="0">
        <tr>
            @foreach (['Bank Copy', 'University Accounts Copy', 'Student Copy'] as $index => $copyName)
                <td class="challan-slip {{ $index < 2 ? 'border-right' : '' }}">

                    <div class="slip-header">
                        <div class="bank-title">{{ $bank['name'] }}</div>
                        <div class="uni-title">{{ $university['name'] }}</div>
                        <div class="copy-type">{{ $copyName }}</div>
                    </div>

                    <div class="bank-details">
                        <strong>A/C Title:</strong> {{ $bank['account_title'] }}<br>
                        <strong>A/C No:</strong> {{ $bank['account_number'] }}<br>
                        <strong>IBAN:</strong> {{ $bank['iban'] }}
                    </div>

                    <table class="info-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="info-label">Challan No:</td>
                            <td class="info-val">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Issue Date:</td>
                            <td class="info-val">{{ $invoice->generated_at?->format('d M Y') ?? date('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Due Date:</td>
                            <td class="info-val" style="color: #b91c1c;">
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Student Name:</td>
                            <td class="info-val">{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Reg. Number:</td>
                            <td class="info-val">{{ $student->registration_no ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Program:</td>
                            <td class="info-val">
                                {{ $student->program?->code ?: $student->program?->name ?? 'UG Program' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Semester / Term:</td>
                            <td class="info-val">{{ $invoice->enrollment?->semester }}
                                ({{ $invoice->enrollment?->academic_year }})</td>
                        </tr>
                    </table>

                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th>Fee Particulars</th>
                                <th class="text-right">Amount (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->line_items ?? [] as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td class="text-right">{{ number_format((float) $item['amount'], 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td>Tuition Fee ({{ number_format((float) $invoice->credit_hours, 1) }} Cr. Hrs)
                                    </td>
                                    <td class="text-right">{{ number_format((float) $invoice->total_amount, 0) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="total-box">
                        <table style="width: 100%; font-size: 7.5pt;">
                            <tr class="total-row">
                                <td>PAYABLE WITHIN DUE DATE:</td>
                                <td class="text-right" style="color: #1e3a8a;">PKR
                                    {{ number_format((float) $invoice->total_amount, 0) }}</td>
                            </tr>
                            <tr style="color: #b91c1c; font-size: 7pt;">
                                <td>Late Fee Surcharge:</td>
                                <td class="text-right">PKR 500</td>
                            </tr>
                            <tr style="font-weight: bold; font-size: 7.5pt; color: #7f1d1d;">
                                <td>PAYABLE AFTER DUE DATE:</td>
                                <td class="text-right">PKR {{ number_format((float) $invoice->total_amount + 500, 0) }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="instructions">
                        • Fee can be deposited at any online branch of {{ $bank['name'] }}.<br>
                        • Non-refundable / non-transferable under university rules.<br>
                        • Student must upload bank deposit slip in portal for clearance.
                    </div>

                    <table class="sign-area" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="sign-box">Depositor's Signature</td>
                            <td style="width: 4%;"></td>
                            <td class="sign-box">Bank Cashier / Officer</td>
                        </tr>
                    </table>

                    @if (!empty($qr_code))
                        <div class="qr-section">
                            <img src="{{ $qr_code }}" width="45" height="45" alt="QR">
                            <div style="font-size: 5.5pt; color: #94a3b8;">Verify:
                                {{ substr($invoice->verification_hash ?? '', 0, 10) }}...</div>
                        </div>
                    @endif

                </td>
            @endforeach
        </tr>
    </table>

</body>

</html>
