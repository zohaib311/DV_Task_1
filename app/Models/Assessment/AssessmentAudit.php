<?php

namespace App\Models\Assessment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AssessmentAudit extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'reason', 'before', 'after'];

    protected $casts = ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
