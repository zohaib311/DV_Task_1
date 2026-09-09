<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'event_name',
        'day',
        'date',
        'time',
        'description',
    ];
}
