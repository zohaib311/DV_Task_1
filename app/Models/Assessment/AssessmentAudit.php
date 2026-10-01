<?php

namespace App\Models\Assessment;

use App\Models\Academic\CourseOffering;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AssessmentAudit extends Model
{
    public $timestamps = false;

    protected $fillable = ['course_offering_id', 'user_id', 'action', 'reason', 'before', 'after'];

    protected $casts = ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }
}
