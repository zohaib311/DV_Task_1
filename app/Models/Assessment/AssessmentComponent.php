<?php

namespace App\Models\Assessment;

use App\Models\Academic\CourseOffering;
use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    public const CODES = ['attendance', 'assignment', 'quiz', 'midterm', 'final', 'practical', 'project'];

    protected $fillable = ['code', 'allocation'];

    protected $casts = ['allocation' => 'decimal:2'];

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
