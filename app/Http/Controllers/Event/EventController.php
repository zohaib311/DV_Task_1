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
            'start_time'  => 'required',
            'end_time'    => 'required',
            'day'         => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'date'        => 'required|date',
            'description' => 'required|string|max:1000',
        ]);

        Event::create($validated);

        return redirect()
            ->route('allEvents')
            ->with('success', 'Event added successfully!');
    }

    function editEventForm($id)
    {
        $event = Event::findOrFail($id);

        return view('event.edit-event', [
            'event' => $event
        ]);
    }

    function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'event_name'  => 'required|string|max:255',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'day'         => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'date'        => 'required|date',
            'description' => 'required|string|max:1000',
        ]);

        $event->update($validated);

        return redirect()
            ->route('allEvents')
            ->with('success', 'Event updated successfully!');
    }

    function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()
            ->route('allEvents')
            ->with('success', 'Event deleted successfully!');
    }

    function allEvents()
    {
        $events = Event::all();
        return view('event.events', ['events' => $events]);
    }
}
