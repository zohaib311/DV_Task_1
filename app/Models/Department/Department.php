<?php

namespace App\Models\Department;

use App\Models\Section\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Department extends Model
{
    use HasFactory, Notifiable;
    
    protected $fillable = [
        'name',
    ];

    // Sections Relationship
    public function sections()
    {
        return $this->hasMany(Section::class, 'department_id');
    }

    // Students Relationship
    public function students()
    {
        return $this->hasMany(Student::class, 'department_id');
    }
}
