<?php

namespace App\Models\Result;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterResultAudit extends Model
{
    protected $fillable = [
        'semester_result_id',
        'updated_by',
        'action',
        'before',
        'after',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function semesterResult(): BelongsTo
    {
        return $this->belongsTo(SemesterResult::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
