<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Challan - {{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/documents/fee-challan-pdf.css') }}">
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
