<?php

namespace App\Models\Attendance;

use App\Models\Enrollment\StudentEnrollmentCourse;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = ['student_enrollment_course_id', 'status', 'note'];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function enrollmentCourse()
    {
        return $this->belongsTo(StudentEnrollmentCourse::class, 'student_enrollment_course_id');
    }
}
