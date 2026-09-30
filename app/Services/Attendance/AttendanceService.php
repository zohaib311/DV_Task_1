<?php

namespace App\Services\Attendance;

use App\Models\Academic\CourseOffering;
use App\Models\Attendance\AttendanceSession;
use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\User;
use App\Services\Teaching\TeacherWorkspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function offering(User $user, int $id): CourseOffering
    {
        abort_unless($user->can('attendance.manage-assigned'), 403);
        $workspace = app(TeacherWorkspace::class);

        return $workspace->offerings($workspace->profile($user))->with('term')->findOrFail($id);
    }

    public function create(User $user, int $offeringId, array $data): AttendanceSession
    {
        return DB::transaction(function () use ($user, $offeringId, $data) {
            $offering = $this->offering($user, $offeringId);
            $offering = CourseOffering::with('term')->lockForUpdate()->findOrFail($offering->id);
            $this->assertOpen($offering);
            $date = $data['held_on'];
            if ($date < $offering->term->starts_on->toDateString() || $date > $offering->term->ends_on->toDateString() || $date > today()->toDateString()) {
                $this->invalid('held_on', 'Choose a non-future date within the teaching term.');
            }
            if ($offering->attendanceSessions()->whereDate('held_on', $date)->where('type', $data['type'])->where('slot', $data['slot'])->exists()) {
                $this->invalid('held_on', 'This date, session type and slot already exist. Open the existing session.');
            }
            $courses = $offering->enrollmentCourses()->whereHas('enrollment', fn ($query) => $query->where('status', 'active')->whereDate('enrolled_at', '<=', $date)->whereDoesntHave('semesterResult', fn ($query) => $query->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])))->get();
            if ($courses->isEmpty()) {
                $this->invalid('held_on', 'No eligible active students were enrolled on this date. Published enrollments are read-only.');
            }
            if ($offering->attendance_policy === null) {
                $policy = config('attendance.policy');
                foreach (['present_credit', 'late_credit', 'absent_credit'] as $key) {
                    if (! is_numeric($policy[$key] ?? null) || $policy[$key] < 0 || $policy[$key] > 1) {
                        $this->invalid('policy', 'Attendance policy credits must be between zero and one.');
                    }
                }
                $offering->forceFill(['attendance_policy' => $policy])->save();
            }
            $session = $offering->attendanceSessions()->create(['teacher_id' => $user->teacherProfile->id, 'held_on' => $date, 'type' => $data['type'], 'slot' => $data['slot'], 'status' => 'draft']);
            $session->records()->createMany($courses->map(fn ($course) => ['student_enrollment_course_id' => $course->id])->all());
            $this->audit($session, $user, 'created', null, null);

            return $session;
        });
    }

    public function update(User $user, int $offeringId, int $sessionId, array $data, bool $cancel = false): void
    {
        DB::transaction(function () use ($user, $offeringId, $sessionId, $data, $cancel) {
            $offering = $this->offering($user, $offeringId);
            $offering = CourseOffering::with('term')->lockForUpdate()->findOrFail($offering->id);
            $session = $offering->attendanceSessions()->lockForUpdate()->findOrFail($sessionId);
            $this->assertOpen($offering);
            if ($session->status === 'cancelled') {
                $this->invalid('session', 'Cancelled sessions are read-only.');
            }
            if ($session->revision !== (int) $data['revision']) {
                $this->invalid('revision', 'Another teacher has changed this session. Reload it before saving.');
            }
            $session->load('records.enrollmentCourse');
            $enrollmentIds = $session->records->pluck('enrollmentCourse.student_semester_enrollment_id');
            $enrollments = StudentSemesterEnrollment::whereIn('id', $enrollmentIds)->orderBy('id')->lockForUpdate()->get();
            foreach ($enrollments as $enrollment) {
                if ($enrollment->status !== 'active' || $enrollment->semesterResult()->whereNotNull('published_at')->whereIn('status', ['Pass', 'Fail'])->exists()) {
                    $this->invalid('session', 'This session contains a completed, promoted, withdrawn or published enrollment and is read-only.');
                }
            }
            $reason = trim($data['reason'] ?? '');
            if (($cancel || $session->status === 'completed') && strlen($reason) < 5) {
                $this->invalid('reason', 'Provide a reason of at least five characters for corrections or cancellations.');
            }
            $before = $this->snapshot($session);
            if (! $cancel) {
                $submitted = collect($data['records'])->keyBy('id');
                if ($submitted->count() !== $session->records->count() || $session->records->pluck('id')->diff($submitted->keys())->isNotEmpty()) {
                    $this->invalid('records', 'Submit exactly the students in this saved session roster.');
                }
                foreach ($session->records as $record) {
                    $record->update(['status' => $submitted[$record->id]['status'], 'note' => $submitted[$record->id]['note'] ?? null]);
                }
            }
            $session->update(['status' => $cancel ? 'cancelled' : 'completed', 'revision' => $session->revision + 1]);
            $this->audit($session, $user, $cancel ? 'cancelled' : 'saved', $reason ?: null, $before);
        });
    }

    private function assertOpen(CourseOffering $offering): void
    {
        if ($offering->status !== 'active' || $offering->term->status !== 'active') {
            $this->invalid('session', 'Attendance can only be changed for an active offering in an active term.');
        }
    }

    private function snapshot(AttendanceSession $session): array
    {
        return ['status' => $session->status, 'revision' => $session->revision, 'records' => $session->records()->orderBy('id')->get(['id', 'student_enrollment_course_id', 'status', 'note'])->toArray()];
    }

    private function audit(AttendanceSession $session, User $user, string $action, ?string $reason, ?array $before): void
    {
        $session->audits()->create(['user_id' => $user->id, 'action' => $action, 'reason' => $reason, 'before' => $before, 'after' => $this->snapshot($session)]);
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
