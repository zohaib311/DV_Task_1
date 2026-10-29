<?php

namespace App\Models\Result;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SemesterResult extends Model
{
    protected $fillable = [
        'student_semester_enrollment_id',
        'student_id',
        'semester_percentage',
        'sgpa',
        'cgpa',
        'status',
        'verification_hash',
        'published_at',
        'source', 'revision', 'reviewed_by', 'reviewed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (SemesterResult $result) {
            if (empty($result->verification_hash)) {
                $result->verification_hash = hash('sha256', 'res_' . uniqid('', true) . Str::random(16));
            }
        });
    }

    protected $casts = [
        'semester_percentage' => 'decimal:2',
        'sgpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'revision' => 'integer',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentSemesterEnrollment::class, 'student_semester_enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SemesterResultItem::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SemesterResultAudit::class);
    }
}
