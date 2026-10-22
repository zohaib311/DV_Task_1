<?php

namespace App\Models\Fee;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = ['student_fee_invoice_id', 'submitted_by', 'reviewed_by', 'payment_method', 'reference_number', 'amount', 'paid_at', 'proof_path', 'status', 'review_note', 'reviewed_at'];
    protected $casts = ['paid_at' => 'date', 'reviewed_at' => 'datetime'];
    public function invoice(): BelongsTo { return $this->belongsTo(StudentFeeInvoice::class, 'student_fee_invoice_id'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
