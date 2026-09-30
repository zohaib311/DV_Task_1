<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class AssessmentMark extends Model
{
    protected $fillable = ['student_enrollment_course_id', 'obtained'];

    protected $casts = ['obtained' => 'decimal:2'];
}
