<?php

namespace App\Models\Academic;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCreditTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'transfer_type',
        'previous_institution',
        'previous_degree',
        'previous_cgpa',
        'transferred_credits',
        'admitted_to_semester',
        'document_path',
        'document_name',
        'remarks',
        'verified_by',
    ];

    protected $casts = [
        'transferred_credits' => 'decimal:1',
        'admitted_to_semester' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
