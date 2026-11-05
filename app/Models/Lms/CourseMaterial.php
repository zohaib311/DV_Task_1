<?php

namespace App\Models\Lms;

use App\Models\Academic\CourseOffering;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CourseMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_offering_id',
        'title',
        'week_number',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'external_url',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'week_number' => 'integer',
        'file_size' => 'integer',
        'is_published' => 'boolean',
    ];

    public function courseOffering(): BelongsTo
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasFile(): bool
    {
        return ! empty($this->file_path) && Storage::disk('local')->exists($this->file_path);
    }

    public function formattedFileSize(): string
    {
        if (! $this->file_size) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $this->file_size > 0 ? floor(log($this->file_size, 1024)) : 0;

        return number_format($this->file_size / pow(1024, $power), 1) . ' ' . ($units[$power] ?? 'B');
    }
}
