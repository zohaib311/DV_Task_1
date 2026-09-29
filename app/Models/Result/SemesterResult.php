<?php

namespace App\Models\Result;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemesterResult extends Model
{
    protected $fillable = [
        'student_semester_enrollment_id',
        'student_id',
        'semester_percentage',
        'sgpa',
        'cgpa',
        'status',
        'published_at',
    ];

    protected $casts = [
        'semester_percentage' => 'decimal:2',
        'sgpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'published_at' => 'datetime',
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
