<?php

namespace App\Services\Teaching;

use App\Models\Academic\CourseOffering;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeacherWorkspace
{
    public function profile(User $user): Teacher
    {
        abort_unless($user->can('offerings.view-assigned'), 403);
        $teacher = $user->teacherProfile;
        if (! $teacher) {
            abort(response()->view('teaching.unlinked', [], 403));
        }

        return $teacher;
    }

    public function offerings(Teacher $teacher): Builder
    {
        // Scope before lookup: even privileged users cannot use this workspace
        // to inspect another teacher's classes. Admin management stays separate.
        return CourseOffering::query()->whereHas('teachers', fn (Builder $query) => $query->where('teachers.id', $teacher->id));
    }
}
