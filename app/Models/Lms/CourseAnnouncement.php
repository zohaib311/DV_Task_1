<?php

namespace App\Models\Lms;

use App\Models\Academic\CourseOffering;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_offering_id',
        'title',
        'content',
        'priority',
        'created_by',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }
}
