<?php

namespace App\Models\Assessment;

use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StudentAssessmentSubmission extends Model
{
    protected $fillable = ['assessment_id', 'student_enrollment_course_id', 'submitted_by', 'answer_text', 'attachment_path', 'original_filename', 'status', 'submitted_at', 'teacher_feedback', 'reviewed_at'];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function enrollmentCourse()
    {
        return $this->belongsTo(StudentEnrollmentCourse::class, 'student_enrollment_course_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
