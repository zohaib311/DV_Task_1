<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['title', 'instructions', 'question_file_path', 'question_original_filename', 'question_mime_type', 'question_file_size',
        'held_on', 'maximum', 'weight', 'revision', 'submission_required', 'submissions_due_at', 'marks_released_at'];

    protected $casts = ['held_on' => 'date', 'maximum' => 'decimal:2', 'weight' => 'decimal:2', 'revision' => 'integer',
        'question_file_size' => 'integer', 'submission_required' => 'boolean', 'submissions_due_at' => 'datetime', 'marks_released_at' => 'datetime'];

    public function component()
    {
        return $this->belongsTo(AssessmentComponent::class, 'assessment_component_id');
    }

    public function marks()
    {
        return $this->hasMany(AssessmentMark::class);
    }

    public function studentSubmissions()
    {
        return $this->hasMany(StudentAssessmentSubmission::class);
    }
}
