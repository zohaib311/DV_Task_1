<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
