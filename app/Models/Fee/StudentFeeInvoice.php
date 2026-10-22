<?php

namespace App\Models\Fee;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeInvoice extends Model
{
    protected $fillable = ['student_id', 'student_semester_enrollment_id', 'fee_structure_id', 'invoice_number', 'status', 'currency', 'credit_hours', 'line_items', 'subtotal', 'discount_amount', 'late_fee_amount', 'total_amount', 'amount_paid', 'due_date', 'generated_at', 'approved_at'];
    protected $casts = ['line_items' => 'array', 'due_date' => 'date', 'generated_at' => 'datetime', 'approved_at' => 'datetime'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function enrollment(): BelongsTo { return $this->belongsTo(StudentSemesterEnrollment::class, 'student_semester_enrollment_id'); }
    public function structure(): BelongsTo { return $this->belongsTo(FeeStructure::class, 'fee_structure_id'); }
    public function payments(): HasMany { return $this->hasMany(FeePayment::class); }
}
