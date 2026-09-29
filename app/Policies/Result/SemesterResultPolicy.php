<?php

namespace App\Policies\Result;

use App\Models\Result\SemesterResult;
use App\Models\User;

class SemesterResultPolicy
{
    public function update(User $user, SemesterResult $semesterResult): bool
    {
        return $semesterResult->published_at === null
            || config('academic.results.allow_published_result_edits');
    }
}
