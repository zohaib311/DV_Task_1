<?php

namespace App\Policies\Result;

use App\Models\Result\SemesterResult;
use App\Models\User;

class SemesterResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('results.view-all');
    }

    public function view(User $user, SemesterResult $semesterResult): bool
    {
        return $user->can('results.view-all');
    }

    public function create(User $user): bool
    {
        return $user->can('results.create');
    }

    public function publish(User $user): bool
    {
        return $user->can('results.publish');
    }

    public function update(User $user, SemesterResult $semesterResult): bool
    {
        if (! $user->can('results.edit')) {
            return false;
        }

        // A published result is historical data. Its correction needs a separate,
        // explicitly assigned permission regardless of its calculated outcome.
        return $semesterResult->status === 'Draft'
            || $semesterResult->published_at === null
            || $user->can('results.edit-published');
    }
}
