<?php

namespace App\Models\Attendance;

use App\Models\Academic\CourseOffering;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = ['course_offering_id', 'teacher_id', 'held_on', 'type', 'slot', 'status', 'revision'];

    protected $casts = ['held_on' => 'date', 'revision' => 'integer'];

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function audits()
    {
        return $this->hasMany(AttendanceAudit::class);
    }
}
