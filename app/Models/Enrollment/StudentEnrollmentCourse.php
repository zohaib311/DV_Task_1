<?php

namespace App\Models\Enrollment;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollmentCourse extends Model
{
    protected $fillable = [
        'student_semester_enrollment_id',
        'course_id',
        'credit_hours',
        'total_marks',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:1',
        'total_marks' => 'integer',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentSemesterEnrollment::class, 'student_semester_enrollment_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
