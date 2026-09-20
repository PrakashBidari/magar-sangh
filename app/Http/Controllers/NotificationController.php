<?php

namespace App\Http\Controllers;

use App\Models\NotificationItem;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationItem::orderByDesc('published_at')->paginate(12);

        return view('media.notifications.index', compact('notifications'));
    }

    public function show(NotificationItem $notification)
    {
        return view('media.notifications.show', compact('notification'));
    }
}
