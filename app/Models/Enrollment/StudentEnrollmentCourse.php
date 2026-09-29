<?php

namespace App\Models\Enrollment;

use App\Models\Course\Course;
use App\Models\Result\SemesterResultItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentEnrollmentCourse extends Model
{
    protected $fillable = [
        'student_semester_enrollment_id',
        'course_id',
        'credit_hours',
        'total_marks',
        'attendance_marks',
        'mid_marks',
        'final_marks',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:1',
        'total_marks' => 'integer',
        'attendance_marks' => 'integer',
        'mid_marks' => 'integer',
        'final_marks' => 'integer',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentSemesterEnrollment::class, 'student_semester_enrollment_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function resultItem(): HasOne
    {
        return $this->hasOne(SemesterResultItem::class, 'student_enrollment_course_id');
    }
}
