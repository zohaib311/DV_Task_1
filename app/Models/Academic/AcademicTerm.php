<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $fillable = ['academic_year_id', 'name', 'starts_on', 'ends_on', 'status'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}
