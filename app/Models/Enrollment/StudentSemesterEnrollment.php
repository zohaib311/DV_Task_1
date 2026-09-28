<?php

namespace App\Models\Enrollment;

use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSemesterEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'department_id',
        'section_id',
        'semester',
        'academic_year',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(StudentEnrollmentCourse::class);
    }
}
