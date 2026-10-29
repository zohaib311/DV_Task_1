@extends('student-portal.layout')

@section('heading', 'Fees & payments')
@section('description', 'Review semester invoices, fee breakdowns, and submit a payment proof for verification.')

@section('student-content')
    <section class="portal-summary-grid fee-summary-grid">
        <div class="portal-summary-card"><span>Invoices</span><strong>{{ $invoices->count() }}</strong><small>Generated semester records</small></div>
        <div class="portal-summary-card"><span>Pending payment</span><strong>PKR {{ number_format((float) $invoices->whereIn('status', ['unpaid', 'rejected'])->sum('total_amount'), 0) }}</strong><small>Awaiting a payment submission</small></div>
        <div class="portal-summary-card"><span>Under review</span><strong>{{ $invoices->where('status', 'submitted')->count() }}</strong><small>Payment proof submitted</small></div>
        <div class="portal-summary-card"><span>Approved</span><strong>{{ $invoices->where('status', 'approved')->count() }}</strong><small>Cleared semester invoices</small></div>
    </section>

    <div class="academic-note portal-note-with-icon fee-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Your invoice is calculated from your registered credit hours. Upload a clear JPG, PNG, or PDF payment proof for administrative verification.</span></div>

    <section class="fee-structure-preview">
        <div class="portal-section-heading"><div><h2>Fee structure</h2><p>Current active fee rules for your program. Your generated invoice remains the final payable record.</p></div><span class="portal-badge is-info">{{ $student->program?->code ?? 'Program' }}</span></div>
        <div class="table-responsive"><table class="table academic-table fee-structure-student-table"><thead><tr><th>Applicable term</th><th>Admission</th><th>Registration</th><th>Security</th><th>Processing</th><th>Enrollment</th><th>Tuition / credit</th><th>Exam</th><th>Convocation</th></tr></thead><tbody>@forelse($structures as $structure)<tr><td><strong>{{ $structure->name }}</strong><small>{{ $structure->term ? $structure->term->name.' · '.$structure->term->academicYear->name : 'All future terms' }}</small></td><td>PKR {{ number_format($structure->admission_fee, 0) }}</td><td>PKR {{ number_format($structure->registration_fee, 0) }}</td><td>PKR {{ number_format($structure->security_fee, 0) }}</td><td>PKR {{ number_format($structure->processing_fee, 0) }}</td><td>PKR {{ number_format($structure->enrollment_fee, 0) }}</td><td>PKR {{ number_format($structure->tuition_per_credit_hour, 0) }}</td><td>PKR {{ number_format($structure->exam_fee, 0) }}</td><td>PKR {{ number_format($structure->convocation_fee, 0) }}</td></tr>@empty<tr><td colspan="9">No active fee structure has been configured for your program yet.</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="fee-invoice-list">
        @forelse($invoices as $invoice)
            @php($canSubmit = in_array($invoice->status, ['unpaid', 'rejected'], true))
            <article class="fee-invoice-card">
                <header class="fee-invoice-header">
                    <div>
                        <span class="portal-code">{{ $invoice->invoice_number }}</span>
                        <h2>{{ $invoice->enrollment?->semester ?? 'Semester' }} <small>{{ $invoice->enrollment?->academic_year }}</small></h2>
                        <p>{{ number_format((float) $invoice->credit_hours, 1) }} registered credit hours · Due {{ $invoice->due_date?->format('d M Y') ?? '—' }}</p>
                        <a href="{{ route('student.fees.challan-pdf', $invoice) }}" class="portal-btn" style="padding: 4px 10px; font-size: 0.8rem; margin-top: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="bi bi-file-earmark-pdf-fill" aria-hidden="true"></i> Download Bank Challan (PDF)
                        </a>
                    </div>
                    <span @class(['fee-status', 'is-approved' => $invoice->status === 'approved', 'is-submitted' => $invoice->status === 'submitted', 'is-rejected' => $invoice->status === 'rejected'])>{{ ucfirst($invoice->status) }}</span>
                </header>
                <div class="fee-invoice-body">
                    <div class="fee-breakdown"><h3>Fee breakdown</h3>@foreach($invoice->line_items as $item)<div><span>{{ $item['label'] }}</span><strong>PKR {{ number_format((float) $item['amount'], 0) }}</strong></div>@endforeach</div>
                    <div class="fee-total"><span>Total payable</span><strong>PKR {{ number_format((float) $invoice->total_amount, 0) }}</strong><small>Paid: PKR {{ number_format((float) $invoice->amount_paid, 0) }}</small></div>
                </div>
                @if($invoice->payments->isNotEmpty())<div class="fee-payment-history">@foreach($invoice->payments as $payment)<div><i class="bi bi-receipt" aria-hidden="true"></i><span>{{ ucfirst($payment->status) }} payment · PKR {{ number_format((float) $payment->amount, 0) }} · {{ $payment->paid_at->format('d M Y') }}</span>@if($payment->review_note)<small>{{ $payment->review_note }}</small>@endif @if($payment->proof_path)<a href="{{ route('student.fees.payments.proof', [$invoice, $payment]) }}">View proof</a>@endif</div>@endforeach</div>@endif
                @if($canSubmit)
                    <details class="fee-submit-panel" @if($invoice->status === 'rejected') open @endif><summary><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Submit payment proof</summary>
                        <form method="POST" enctype="multipart/form-data" action="{{ route('student.fees.payments.submit', $invoice) }}" class="fee-payment-form">@csrf
                            <div><label>Payment method</label><select name="payment_method" required><option value="">Select method</option><option value="bank_transfer">Bank transfer</option><option value="challan">Bank challan</option><option value="easypaisa">Easypaisa</option><option value="jazzcash">JazzCash</option><option value="cash">Cash</option></select></div>
                            <div><label>Paid amount</label><input name="amount" type="number" min="0.01" max="{{ $invoice->total_amount }}" step="0.01" value="{{ $invoice->total_amount }}" required></div>
                            <div><label>Payment date</label><input name="paid_at" type="date" max="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}" required></div>
                            <div><label>Reference no. <small>Optional</small></label><input name="reference_number" maxlength="120"></div>
                            <div class="fee-proof-field"><label>Receipt / challan proof</label><input name="proof" type="file" accept=".pdf,.jpg,.jpeg,.png" required><small>PDF, JPG, or PNG · Maximum 5 MB</small></div>
                            <button class="portal-btn" type="submit"><i class="bi bi-send-check" aria-hidden="true"></i> Submit for verification</button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <div class="portal-empty-state"><i class="bi bi-receipt" aria-hidden="true"></i><strong>No semester fee invoice is available yet.</strong><span>Your invoice is generated after semester enrollment and course registration.</span></div>
        @endforelse
    </section>
@endsection
