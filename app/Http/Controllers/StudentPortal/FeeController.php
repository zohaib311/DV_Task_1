<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\StudentFeeInvoice;
use App\Services\Fee\FeeInvoiceService;
use App\Services\Notifications\AcademicNotificationService;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeeController extends Controller
{
    public function index(Request $request, StudentWorkspace $workspace, FeeInvoiceService $service)
    {
        $student = $workspace->student($request->user());
        $student->semesterEnrollments()->where('status', 'active')->get()->each(fn ($enrollment) => $service->generate($enrollment));
        $invoices = StudentFeeInvoice::with(['enrollment', 'payments' => fn ($query) => $query->latest()])
            ->where('student_id', $student->id)->latest('id')->get();
        $structures = FeeStructure::with(['program', 'term.academicYear'])->where('program_id', $student->program_id)
            ->where('is_active', true)->latest('id')->get();

        return view('student-portal.fees.index', compact('student', 'invoices', 'structures'));
    }

    public function submit(Request $request, StudentFeeInvoice $invoice, StudentWorkspace $workspace, FeeInvoiceService $service, AcademicNotificationService $notifications)
    {
        $student = $workspace->student($request->user());
        abort_unless($invoice->student_id === $student->id, 404);
        $service->assertPayable($invoice);
        $data = $request->validate([
            'payment_method' => ['required', 'in:bank_transfer,challan,easypaisa,jazzcash,cash'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->total_amount],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $payment = $invoice->payments()->create($data + [
            'submitted_by' => $request->user()->id,
            'proof_path' => $request->file('proof')->store('fee-proofs', 'local'),
            'status' => 'submitted',
        ]);
        $invoice->update(['status' => 'submitted']);
        $notifications->users($notifications->academicStaff(), 'Fee payment submitted',
            "{$student->name} submitted {$invoice->currency} ".number_format((float) $payment->amount, 2)." for {$invoice->invoice_number}.",
            route('fees.payments.index'), 'fee', ['invoice_id' => $invoice->id, 'payment_id' => $payment->id]);

        return back()->with('success', 'Your payment proof was submitted for verification.');
    }

    public function proof(Request $request, StudentFeeInvoice $invoice, int $payment, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        abort_unless($invoice->student_id === $student->id, 404);
        $payment = $invoice->payments()->findOrFail($payment);
        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->download($payment->proof_path, 'payment-proof-'.$invoice->invoice_number.'.'.pathinfo($payment->proof_path, PATHINFO_EXTENSION));
    }
}
