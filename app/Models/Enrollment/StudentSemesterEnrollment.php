<?php

namespace App\Models\Enrollment;

use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Result\SemesterResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentSemesterEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'department_id',
        'program_id',
        'section_id',
        'semester',
        'academic_year',
        'status',
        'enrolled_at',
        'completed_at',
        'academic_term_id',
        'semester_curriculum_id',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\AcademicTerm::class, 'academic_term_id');
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\SemesterCurriculum::class, 'semester_curriculum_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Program::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(StudentEnrollmentCourse::class);
    }

    public function semesterResult(): HasOne
    {
        return $this->hasOne(SemesterResult::class);
    }
}
