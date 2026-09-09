<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    function eventForm()
    {
        return view('event.add-event');
    }

    function addEvent()
    {
        return view('event.events');
    }

    function allEvents()
    {
        return view('event.events');
    }
}
