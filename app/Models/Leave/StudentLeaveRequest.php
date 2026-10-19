<?php

namespace App\Models\Leave;

use App\Models\Academic\CourseOffering;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeaveRequest extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];
    public const TYPES = ['medical', 'personal', 'official', 'other'];

    protected $fillable = [
        'student_enrollment_course_id', 'course_offering_id', 'requested_by', 'reviewed_by',
        'starts_on', 'ends_on', 'leave_type', 'reason', 'attachment_path', 'attachment_name',
        'attachment_mime', 'attachment_size', 'status', 'teacher_remark', 'reviewed_at',
    ];

    protected $casts = [
        'starts_on' => 'date', 'ends_on' => 'date', 'reviewed_at' => 'datetime',
    ];

    public function enrollmentCourse(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollmentCourse::class, 'student_enrollment_course_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
