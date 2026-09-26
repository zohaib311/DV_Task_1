<?php

namespace App\Models\Section;

use App\Models\Department\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Section extends Model
{
    //
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'department_id',
    ];

    // Department Relationship
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Students Relationship
    public function students()
    {
        return $this->hasMany(\App\Models\Student::class, 'section_id');
    }
}
