<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderByDesc('event_date')->paginate(9);

        return view('media.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        return view('media.events.show', compact('event'));
    }
}
