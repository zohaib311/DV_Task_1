<?php

namespace App\Services\Notifications;

use App\Models\Academic\CourseOffering;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Illuminate\Support\Collection;

class AcademicNotificationService
{
    public function user(?User $user, string $title, string $message, ?string $url = null, string $category = 'academic', array $context = []): void
    {
        $user?->notify(new AcademicUpdateNotification($title, $message, $url, $category, $context));
    }

    public function users(iterable $users, string $title, string $message, ?string $url = null, string $category = 'academic', array $context = [], ?int $exceptUserId = null): void
    {
        collect($users)->filter(fn ($user) => $user instanceof User && $user->id !== $exceptUserId)
            ->unique('id')->each(fn (User $user) => $this->user($user, $title, $message, $url, $category, $context));
    }

    public function assignedTeachers(CourseOffering $offering): Collection
    {
        return $offering->teachers()->with('user')->get()->pluck('user')->filter()->unique('id')->values();
    }

    public function enrolledStudents(CourseOffering $offering): Collection
    {
        return User::whereHas('studentProfile.semesterEnrollments.courses', fn ($query) => $query->where('course_offering_id', $offering->id))->get();
    }

    public function academicStaff(): Collection
    {
        return User::whereHas('roles', fn ($query) => $query->whereIn('name', [
            'Super Admin', 'Academic Admin', 'HOD / Program Coordinator',
        ]))->get();
    }
}
