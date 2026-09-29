<?php

namespace App\Policies\Result;

use App\Models\Result\SemesterResult;
use App\Models\User;

class SemesterResultPolicy
{
    public function update(User $user, SemesterResult $semesterResult): bool
    {
        // A draft must remain editable even if legacy or manually corrected
        // data contains an accidental publication timestamp.
        return $semesterResult->status === 'Draft'
            || $semesterResult->published_at === null
            || config('academic.results.allow_published_result_edits');
    }
}
