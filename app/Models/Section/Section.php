<?php

namespace App\Models\Section;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Section extends Model
{
    //
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'department',
    ];
}
