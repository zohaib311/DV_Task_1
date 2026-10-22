<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee\FeePayment;
use App\Services\Notifications\AcademicNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FeePaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $payments = FeePayment::with(['invoice.enrollment', 'invoice.student', 'submitter', 'reviewer'])
            ->when($status, fn ($query) => $query->where('status', $status))->latest()->paginate(20)->withQueryString();

        return view('fees.payments.index', compact('payments', 'status'));
    }

    public function approve(Request $request, FeePayment $payment, AcademicNotificationService $notifications)
    {
        $payment = DB::transaction(function () use ($request, $payment) {
            $payment = FeePayment::with('invoice.student.user')->lockForUpdate()->findOrFail($payment->id);
            abort_unless($payment->status === 'submitted', 422, 'Only submitted payments can be approved.');
            $payment->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_note' => $request->input('review_note')]);
            $approved = (float) $payment->invoice->payments()->where('status', 'approved')->sum('amount');
            $payment->invoice->update(['amount_paid' => $approved, 'status' => $approved >= (float) $payment->invoice->total_amount ? 'approved' : 'unpaid',
                'approved_at' => $approved >= (float) $payment->invoice->total_amount ? now() : null]);
            return $payment->fresh('invoice.student.user');
        });
        $full = $payment->invoice->status === 'approved';
        $notifications->user($payment->invoice->student->user, $full ? 'Fee payment approved' : 'Partial fee payment approved',
            $full ? "Your payment for {$payment->invoice->invoice_number} has been approved." : "Your partial payment for {$payment->invoice->invoice_number} was approved. Please submit the remaining balance.",
            route('student.fees.index'), 'fee', ['invoice_id' => $payment->invoice->id]);
        return back()->with('success', 'Payment approved.');
    }

    public function reject(Request $request, FeePayment $payment, AcademicNotificationService $notifications)
    {
        $data = $request->validate(['review_note' => ['required', 'string', 'max:1000']]);
        $payment = DB::transaction(function () use ($request, $payment, $data) {
            $payment = FeePayment::with('invoice.student.user')->lockForUpdate()->findOrFail($payment->id);
            abort_unless($payment->status === 'submitted', 422, 'Only submitted payments can be rejected.');
            $payment->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_note' => $data['review_note']]);
            $payment->invoice->update(['status' => 'rejected']);
            return $payment->fresh('invoice.student.user');
        });
        $notifications->user($payment->invoice->student->user, 'Fee payment needs correction',
            "Your payment for {$payment->invoice->invoice_number} was rejected: {$payment->review_note}", route('student.fees.index'), 'fee', ['invoice_id' => $payment->invoice->id]);
        return back()->with('success', 'Payment rejected and the student was notified.');
    }

    public function proof(FeePayment $payment)
    {
        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);
        return Storage::disk('local')->download($payment->proof_path, 'payment-proof-'.$payment->id.'.'.pathinfo($payment->proof_path, PATHINFO_EXTENSION));
    }
}
