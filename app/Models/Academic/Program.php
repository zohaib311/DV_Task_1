<?php

namespace App\Models\Academic;

use App\Models\Department\Department;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'academic_programs';

    protected $fillable = ['department_id', 'name', 'code', 'duration_years', 'total_semesters', 'is_active'];

    protected $casts = ['duration_years' => 'integer', 'total_semesters' => 'integer', 'is_active' => 'boolean'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function curricula()
    {
        return $this->hasMany(SemesterCurriculum::class);
    }

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function semesterEnrollments()
    {
        return $this->hasMany(StudentSemesterEnrollment::class);
    }
}
