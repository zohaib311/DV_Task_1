<?php

namespace App\Models\Result;

use App\Models\Course\Course;
use App\Models\Enrollment\StudentEnrollmentCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterResultItem extends Model
{
    protected $fillable = [
        'semester_result_id',
        'student_enrollment_course_id',
        'course_id',
        'course_code',
        'course_name',
        'credit_hours',
        'total_marks',
        'attendance_marks',
        'mid_marks',
        'final_marks',
        'obtained_marks',
        'attendance_obtained_marks',
        'mid_obtained_marks',
        'final_obtained_marks',
        'percentage',
        'grade',
        'grade_point',
        'status',
        'assessment_submission_id', 'assessment_snapshot',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:1',
        'total_marks' => 'integer',
        'attendance_marks' => 'integer',
        'mid_marks' => 'integer',
        'final_marks' => 'integer',
        'obtained_marks' => 'decimal:2',
        'attendance_obtained_marks' => 'decimal:2',
        'mid_obtained_marks' => 'decimal:2',
        'final_obtained_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'assessment_snapshot' => 'array',
    ];

    public function semesterResult(): BelongsTo
    {
        return $this->belongsTo(SemesterResult::class);
    }

    public function enrollmentCourse(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollmentCourse::class, 'student_enrollment_course_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
