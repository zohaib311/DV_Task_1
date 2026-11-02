<?php

namespace App\Models\Timetable;

use App\Models\Academic\AcademicTerm;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_term_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_type',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TimetableAllocation::class);
    }

    public function getTimeWindowAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');

        return "{$start} - {$end}";
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('h:i A');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('h:i A');
    }
}
