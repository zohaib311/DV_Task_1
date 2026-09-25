<?php

namespace App\Models;

use App\Models\Course\Course;
use App\Models\Department\Department;
use App\Models\Result\Result;
use App\Models\Section\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'department_id',
        'section_id',
        'course_ids',
        'image',
    ];

    // Array Cast for Multiple Courses JSON Column
    protected $casts = [
        'course_ids' => 'array',
    ];

    // Department Relationship
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Section Relationship
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    // Results Relationship
    public function results()
    {
        return $this->hasMany(Result::class, 'student_id');
    }

    // Helper Attribute to get assigned Courses Models
    public function getAssignedCoursesAttribute()
    {
        if (empty($this->course_ids)) {
            return collect();
        }
        return Course::whereIn('id', $this->course_ids)->get();
    }
}
