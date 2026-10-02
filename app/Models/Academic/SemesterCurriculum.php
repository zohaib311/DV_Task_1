<?php

namespace App\Models\Academic;

use App\Models\Department\Department;
use Illuminate\Database\Eloquent\Model;

class SemesterCurriculum extends Model
{
    protected $table = 'semester_curricula';

    protected $fillable = ['department_id', 'program_id', 'semester', 'version', 'status', 'approved_at', 'approved_by'];

    protected $casts = ['approved_at' => 'datetime'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function courses()
    {
        return $this->hasMany(CurriculumCourse::class);
    }
}
