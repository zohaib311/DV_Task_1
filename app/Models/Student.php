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
        'registration_no',
        'name',
        'email',
        'phone',
        'department_id',
        'section_id',
        'semester',
        'course_ids',
        'image',
    ];

    protected static function booted()
    {
        static::creating(function ($student) {
            if (empty($student->registration_no)) {
                $year = date('Y');
                $maxId = static::max('id') ?? 0;
                $nextId = $maxId + 1;
                $student->registration_no = 'REG-' . $year . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

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
