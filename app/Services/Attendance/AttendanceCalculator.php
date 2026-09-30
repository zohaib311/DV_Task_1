<?php

namespace App\Services\Attendance;

use App\Models\Attendance\AttendanceRecord;
use App\Models\Enrollment\StudentEnrollmentCourse;

class AttendanceCalculator
{
    public function summary(StudentEnrollmentCourse $course): array
    {
        $policy = $course->offering?->attendance_policy ?? config('attendance.policy');
        $counts = AttendanceRecord::where('student_enrollment_course_id', $course->id)
            ->whereHas('session', fn ($query) => $query->where('status', 'completed'))
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $valid = (int) $counts->sum() - ($policy['excused_excluded'] ? ($counts['excused'] ?? 0) : 0);
        $credit = ($counts['present'] ?? 0) * $policy['present_credit'] + ($counts['late'] ?? 0) * $policy['late_credit'] + ($counts['absent'] ?? 0) * $policy['absent_credit'];
        $percentage = $valid > 0 ? round($credit / $valid * 100, 2) : null;

        return [
            'counts' => $counts->all(), 'valid_sessions' => $valid, 'percentage' => $percentage,
            'marks' => $valid > 0 ? round($credit / $valid * $course->attendance_marks, 2) : null,
            'maximum' => $course->attendance_marks, 'policy' => $policy,
        ];
    }
}
