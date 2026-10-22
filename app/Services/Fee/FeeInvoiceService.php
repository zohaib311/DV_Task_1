<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeStructure;
use App\Models\Fee\StudentFeeInvoice;
use App\Models\Enrollment\StudentSemesterEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeInvoiceService
{
    public function generate(StudentSemesterEnrollment $enrollment): ?StudentFeeInvoice
    {
        return DB::transaction(function () use ($enrollment) {
            $enrollment = StudentSemesterEnrollment::with(['student', 'program', 'term', 'courses'])->lockForUpdate()->findOrFail($enrollment->id);
            if (! $enrollment->program_id) {
                return null;
            }
            $structure = FeeStructure::query()->where('program_id', $enrollment->program_id)->where('is_active', true)
                ->where(function ($query) use ($enrollment) { $query->where('academic_term_id', $enrollment->academic_term_id)->orWhereNull('academic_term_id'); })
                ->orderByRaw('academic_term_id is null')->latest('id')->first();
            if (! $structure) {
                return null;
            }

            $invoice = StudentFeeInvoice::where('student_semester_enrollment_id', $enrollment->id)->lockForUpdate()->first();
            if ($invoice && in_array($invoice->status, ['submitted', 'approved'], true)) {
                return $invoice;
            }
            $credits = (float) $enrollment->courses->sum('credit_hours');
            $isFirstEnrollment = ! StudentFeeInvoice::where('student_id', $enrollment->student_id)
                ->where('student_semester_enrollment_id', '!=', $enrollment->id)->exists();
            $isFinalSemester = $enrollment->program && $enrollment->semester === 'Semester '.$enrollment->program->total_semesters;
            $items = collect([
                ['label' => 'Enrollment fee', 'amount' => (float) $structure->enrollment_fee],
                ['label' => 'Tuition fee ('.number_format($credits, 1).' credit hours × '.number_format((float) $structure->tuition_per_credit_hour, 2).')', 'amount' => $credits * (float) $structure->tuition_per_credit_hour],
                ['label' => 'Examination fee', 'amount' => (float) $structure->exam_fee],
            ]);
            if ($isFirstEnrollment) {
                $items = collect([
                    ['label' => 'Admission fee', 'amount' => (float) $structure->admission_fee],
                    ['label' => 'Registration fee', 'amount' => (float) $structure->registration_fee],
                    ['label' => 'Security fee (refundable)', 'amount' => (float) $structure->security_fee],
                    ['label' => 'Admission processing fee', 'amount' => (float) $structure->processing_fee],
                ])->concat($items);
            }
            if ($isFinalSemester && (float) $structure->convocation_fee > 0) {
                $items->push(['label' => 'Convocation fee', 'amount' => (float) $structure->convocation_fee]);
            }
            $subtotal = (float) $items->sum('amount');
            $attributes = ['fee_structure_id' => $structure->id, 'status' => 'unpaid', 'credit_hours' => $credits,
                'line_items' => $items->values()->all(), 'subtotal' => $subtotal, 'discount_amount' => 0, 'late_fee_amount' => 0,
                'total_amount' => $subtotal, 'amount_paid' => 0, 'due_date' => now()->addDays($structure->due_days), 'generated_at' => now()];
            if ($invoice) {
                $invoice->update($attributes);
                return $invoice->fresh();
            }
            $attributes += ['student_id' => $enrollment->student_id, 'student_semester_enrollment_id' => $enrollment->id,
                'invoice_number' => 'INV-'.now()->format('Y').'-'.str_pad((string) $enrollment->id, 5, '0', STR_PAD_LEFT)];
            return StudentFeeInvoice::create($attributes);
        });
    }

    public function assertPayable(StudentFeeInvoice $invoice): void
    {
        if ($invoice->status === 'approved') {
            throw ValidationException::withMessages(['invoice' => 'This invoice has already been approved.']);
        }
        if ($invoice->status === 'submitted') {
            throw ValidationException::withMessages(['invoice' => 'A payment proof is already awaiting review.']);
        }
    }
}
