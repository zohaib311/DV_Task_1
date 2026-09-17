<?php

namespace App\Models\Department;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Department extends Model
{

    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
    ];
}
