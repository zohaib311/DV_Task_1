<?php

namespace App\Models\Assessment;

use App\Models\Academic\CourseOffering;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    protected $fillable = ['submitted_by', 'reviewed_by', 'status', 'snapshot', 'review_note', 'reviewed_at'];

    protected $casts = ['snapshot' => 'array', 'reviewed_at' => 'datetime'];

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
