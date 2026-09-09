<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    function eventForm()
    {
        return view('event.add-event');
    }

    function addEvent(Request $request)
    {
        $validated = $request->validate([
            'event_name'  => 'required|string|max:255',
            'time'        => 'required|date_format:H:i',
            'day'         => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'date'        => 'required|date',
            'description' => 'required|string|max:1000',
        ]);

        Event::create($validated);

        return redirect()
            ->route('allEvents')
            ->with('success', 'Event added successfully!');
    }

    function allEvents()
    {

        $events = Event::all();
        return view('event.events', ['events' => $events]);
    }
}
