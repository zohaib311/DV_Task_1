<?php

namespace App\Models\Fee;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $fillable = ['program_id', 'academic_term_id', 'name', 'admission_fee', 'registration_fee', 'security_fee', 'processing_fee', 'enrollment_fee', 'tuition_per_credit_hour', 'exam_fee', 'convocation_fee', 'late_fee', 'due_days', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'due_days' => 'integer'];

    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function term(): BelongsTo { return $this->belongsTo(AcademicTerm::class, 'academic_term_id'); }
}
