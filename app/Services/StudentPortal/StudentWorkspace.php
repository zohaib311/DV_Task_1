<?php

namespace App\Services\StudentPortal;

use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Result\SemesterResult;
use App\Models\Student;
use App\Models\User;

class StudentWorkspace
{
    public function landing(User $user): ?string
    {
        if ($user->canAny(['students.manage', 'offerings.manage', 'results.view-all', 'offerings.view-assigned'])) {
            return null;
        }
        foreach (['student.profile.view-own' => 'student.dashboard', 'student.courses.view-own' => 'student.courses',
            'student.attendance.view-own' => 'student.attendance.index', 'student.assessments.view-own' => 'student.assessments.index', 'student.result.view-own' => 'student.results.index', 'notifications.view-own' => 'student.notifications.index'] as $permission => $route) {
            if ($user->can($permission)) {
                return $route;
            }
        }

        return $user->hasRole('Student') || $user->studentProfile ? 'profile.settings.form' : null;
    }

    public function student(User $user): Student
    {
        $student = $user->studentProfile;
        abort_unless($student, 403, 'Please ask an administrator to link your account to your student profile.');

        return $student;
    }

    public function courses(User $user)
    {
        $id = $this->student($user)->id;

        return StudentEnrollmentCourse::whereHas('enrollment', fn ($query) => $query->where('student_id', $id));
    }

    public function results(User $user)
    {
        $id = $this->student($user)->id;

        return SemesterResult::where('student_id', $id)->whereHas('enrollment', fn ($query) => $query->where('student_id', $id))
            ->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail']);
    }
}
