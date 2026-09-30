<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'starts_on', 'ends_on'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];

    public function terms()
    {
        return $this->hasMany(AcademicTerm::class);
    }
}
