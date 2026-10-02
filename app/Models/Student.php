<?php

namespace App\Models;

use App\Models\Course\Course;
use App\Models\Academic\Program;
use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Result\Result;
use App\Models\Result\SemesterResult;
use App\Models\Section\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'registration_no',
        'name',
        'email',
        'phone',
        'department_id',
        'program_id',
        'section_id',
        'semester',
        'course_ids',
        'image',
        'user_id',
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function semesterEnrollments()
    {
        return $this->hasMany(StudentSemesterEnrollment::class);
    }

    public function semesterResults()
    {
        return $this->hasMany(SemesterResult::class);
    }

    public function activeSemesterEnrollment(): HasOne
    {
        return $this->hasOne(StudentSemesterEnrollment::class)
            ->where('status', 'active')
            ->latestOfMany('enrolled_at');
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
