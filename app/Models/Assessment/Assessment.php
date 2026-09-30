<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['title', 'held_on', 'maximum', 'weight', 'revision'];

    protected $casts = ['held_on' => 'date', 'maximum' => 'decimal:2', 'weight' => 'decimal:2', 'revision' => 'integer'];

    public function component()
    {
        return $this->belongsTo(AssessmentComponent::class, 'assessment_component_id');
    }

    public function marks()
    {
        return $this->hasMany(AssessmentMark::class);
    }
}
