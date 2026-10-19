<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image'
    ];

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class);
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->getKey().'.notifications';
    }

    public function requestedLeaveRequests()
    {
        return $this->hasMany(\App\Models\Leave\StudentLeaveRequest::class, 'requested_by');
    }

    public function reviewedLeaveRequests()
    {
        return $this->hasMany(\App\Models\Leave\StudentLeaveRequest::class, 'reviewed_by');
    }
}
