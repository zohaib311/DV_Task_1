<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'started_at',
        'completed_at',
        'score_obtained',
        'total_points',
        'answers',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score_obtained' => 'decimal:2',
        'total_points' => 'decimal:2',
        'answers' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['submitted', 'timed_out'], true);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get remaining time in seconds based on duration_minutes and started_at.
     */
    public function remainingSeconds(): int
    {
        if ($this->isCompleted()) {
            return 0;
        }

        $durationSeconds = $this->quiz->duration_minutes * 60;
        $elapsedSeconds = now()->diffInSeconds($this->started_at);
        $remaining = $durationSeconds - $elapsedSeconds;

        return max(0, $remaining);
    }

    public function percentage(): float
    {
        if ($this->total_points <= 0) {
            return 0.0;
        }

        return round(($this->score_obtained / $this->total_points) * 100, 1);
    }
}
