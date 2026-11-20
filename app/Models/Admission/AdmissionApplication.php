<?php

namespace App\Models\Admission;

use App\Models\Academic\Program;
use App\Models\Department\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_no',
        'name',
        'email',
        'phone',
        'cnic',
        'father_name',
        'gender',
        'dob',
        'address',
        'city',
        'department_id',
        'program_id',
        'admission_type',
        'inter_degree',
        'inter_board',
        'inter_roll_no',
        'inter_total_marks',
        'inter_obtained_marks',
        'inter_percentage',
        'previous_institution',
        'previous_degree',
        'previous_cgpa',
        'transferred_credits',
        'scholarship_name',
        'document_path',
        'document_name',
        'applicant_remarks',
        'status',
        'admin_notes',
        'enrolled_student_id',
        'enrolled_at',
        'enrolled_by',
    ];

    protected $casts = [
        'dob' => 'date',
        'inter_total_marks' => 'decimal:2',
        'inter_obtained_marks' => 'decimal:2',
        'inter_percentage' => 'decimal:2',
        'transferred_credits' => 'decimal:1',
        'enrolled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($app) {
            if (empty($app->application_no)) {
                $year = date('Y');
                $maxId = static::max('id') ?? 0;
                $nextId = $maxId + 1;
                $app->application_no = 'APP-' . $year . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrolledStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'enrolled_student_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function isEnrolled(): bool
    {
        return $this->status === 'enrolled' && ! empty($this->enrolled_student_id);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'under_review' => 'bg-info text-dark',
            'approved' => 'bg-primary text-white',
            'enrolled' => 'bg-success text-white',
            'rejected' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'approved' => 'Approved / Shortlisted',
            'enrolled' => 'Officially Enrolled',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }
}
