<?php

namespace App\Models\Course;

use App\Models\Enrollment\StudentEnrollmentCourse;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'credit_hours',
        'total_marks',
        'is_active',
    ];

    protected $casts = [
        'credit_hours' => 'decimal:1',
        'total_marks' => 'integer',
        'is_active' => 'boolean',
    ];

    public function enrollmentCourses()
    {
        return $this->hasMany(StudentEnrollmentCourse::class);
    }
}
