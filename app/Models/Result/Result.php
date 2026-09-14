<?php

namespace App\Models\Result;

use App\Models\Course\Course;
use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'section_id',
        'percentage',
        'gpa',
        'cgpa',
        'grade',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
