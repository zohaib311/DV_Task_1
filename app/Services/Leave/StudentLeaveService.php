<?php

namespace App\Services\Leave;

use App\Models\Leave\StudentLeaveRequest;
use App\Models\User;
use App\Services\Notifications\AcademicNotificationService;
use App\Services\StudentPortal\StudentWorkspace;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentLeaveService
{
    public function __construct(
        private readonly StudentWorkspace $students,
        private readonly TeacherWorkspace $teachers,
        private readonly AcademicNotificationService $notifications,
    ) {
    }

    public function studentCourses(User $user)
    {
        return $this->students->courses($user)
            ->whereHas('enrollment', fn ($query) => $query->where('status', 'active'))
            ->whereHas('offering', fn ($query) => $query->where('status', 'active')->whereHas('term', fn ($term) => $term->where('status', 'active')))
            ->with(['offering.term', 'offering.teachers.user', 'course', 'enrollment'])
            ->orderByDesc('id');
    }

    public function requestsForStudent(User $user)
    {
        return StudentLeaveRequest::query()
            ->where('requested_by', $user->id)
            ->with(['enrollmentCourse.course', 'enrollmentCourse.enrollment', 'offering.teachers.user', 'reviewer'])
            ->latest();
    }

    public function apply(User $user, array $data, ?UploadedFile $attachment = null): StudentLeaveRequest
    {
        $course = $this->studentCourses($user)->findOrFail($data['student_enrollment_course_id']);
        $term = $course->offering->term;

        if ($data['starts_on'] < $term->starts_on->toDateString() || $data['ends_on'] > $term->ends_on->toDateString()) {
            throw ValidationException::withMessages([
                'starts_on' => 'Choose dates within '.$term->name.' ('.$term->starts_on->format('d M Y').' to '.$term->ends_on->format('d M Y').').',
            ]);
        }

        $overlap = StudentLeaveRequest::query()
            ->where('student_enrollment_course_id', $course->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('starts_on', '<=', $data['ends_on'])
            ->whereDate('ends_on', '>=', $data['starts_on'])
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages([
                'student_enrollment_course_id' => 'You already have a pending or approved leave request for overlapping dates in this course.',
            ]);
        }

        $leave = DB::transaction(function () use ($user, $data, $attachment, $course) {
            $file = [];
            if ($attachment) {
                $file = [
                    'attachment_path' => $attachment->store('leave-attachments', 'public'),
                    'attachment_name' => $attachment->getClientOriginalName(),
                    'attachment_mime' => $attachment->getClientMimeType(),
                    'attachment_size' => $attachment->getSize(),
                ];
            }

            return StudentLeaveRequest::create(array_merge($file, [
                'student_enrollment_course_id' => $course->id,
                'course_offering_id' => $course->course_offering_id,
                'requested_by' => $user->id,
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'leave_type' => $data['leave_type'],
                'reason' => $data['reason'],
            ]));
        });

        $course->loadMissing('offering.teachers.user', 'course');
        $this->notifications->users(
            $this->notifications->assignedTeachers($course->offering),
            'New leave request',
            $user->name.' requested '.$this->label($leave).' leave for '.$course->course_code.'.',
            route('teaching.leaves.show', $leave),
            'leave-request',
            ['leave_request_id' => $leave->id, 'course_offering_id' => $course->course_offering_id],
            $user->id,
        );

        return $leave;
    }

    public function cancel(User $user, int $leaveId): StudentLeaveRequest
    {
        $leave = $this->requestsForStudent($user)->findOrFail($leaveId);
        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages(['leave_request' => 'This leave request has already been reviewed and cannot be cancelled.']);
        }
        $leave->update(['status' => 'cancelled']);

        $leave->loadMissing('offering.teachers.user', 'enrollmentCourse.course');
        $this->notifications->users(
            $this->notifications->assignedTeachers($leave->offering),
            'Leave request cancelled',
            $user->name.' cancelled the leave request for '.$leave->enrollmentCourse->course_code.'.',
            route('teaching.leaves.show', $leave),
            'leave-request',
            ['leave_request_id' => $leave->id],
            $user->id,
        );

        return $leave;
    }

    public function requestsForTeacher(User $user)
    {
        $teacher = $this->teachers->profile($user);

        return StudentLeaveRequest::query()
            ->whereIn('course_offering_id', $this->teachers->offerings($teacher)->select('id'))
            ->with(['enrollmentCourse.enrollment.student', 'enrollmentCourse.course', 'offering.term', 'reviewer'])
            ->latest();
    }

    public function requestForTeacher(User $user, int $leaveId): StudentLeaveRequest
    {
        return $this->requestsForTeacher($user)->findOrFail($leaveId);
    }

    public function review(User $user, int $leaveId, string $decision, ?string $remark): StudentLeaveRequest
    {
        $leave = $this->requestForTeacher($user, $leaveId);
        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages(['leave_request' => 'This leave request has already been reviewed. Refresh the page to see its latest status.']);
        }
        if ($decision === 'rejected' && blank($remark)) {
            throw ValidationException::withMessages(['teacher_remark' => 'Add a short reason so the student understands why this leave request was rejected.']);
        }

        $leave->update([
            'status' => $decision,
            'teacher_remark' => $remark,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);
        $leave->loadMissing('enrollmentCourse.course', 'requester');

        $this->notifications->user(
            $leave->requester,
            'Leave request '.($decision === 'approved' ? 'approved' : 'rejected'),
            'Your '.$this->label($leave).' leave request for '.$leave->enrollmentCourse->course_code.' was '.$decision.'.',
            route('student.leaves.index'),
            'leave-request',
            ['leave_request_id' => $leave->id, 'status' => $decision],
        );

        return $leave;
    }

    public function downloadForStudent(User $user, int $leaveId)
    {
        $leave = $this->requestsForStudent($user)->findOrFail($leaveId);
        abort_unless($leave->attachment_path && Storage::disk('public')->exists($leave->attachment_path), 404);

        return Storage::disk('public')->download($leave->attachment_path, $leave->attachment_name ?: 'leave-attachment');
    }

    public function downloadForTeacher(User $user, int $leaveId)
    {
        $leave = $this->requestForTeacher($user, $leaveId);
        abort_unless($leave->attachment_path && Storage::disk('public')->exists($leave->attachment_path), 404);

        return Storage::disk('public')->download($leave->attachment_path, $leave->attachment_name ?: 'leave-attachment');
    }

    private function label(StudentLeaveRequest $leave): string
    {
        return ucfirst($leave->leave_type).' ('.$leave->starts_on->format('d M').' – '.$leave->ends_on->format('d M Y').')';
    }
}
