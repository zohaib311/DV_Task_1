<?php

namespace App\Models\Academic;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Model;

class CurriculumCourse extends Model
{
    public const SNAPSHOT_FIELDS = ['course_id', 'course_code', 'course_name', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks'];

    protected $fillable = ['semester_curriculum_id', 'course_id', 'type', 'course_code', 'course_name', 'credit_hours', 'total_marks', 'attendance_marks', 'mid_marks', 'final_marks'];

    protected $casts = ['credit_hours' => 'decimal:1'];

    public function curriculum()
    {
        return $this->belongsTo(SemesterCurriculum::class, 'semester_curriculum_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}
