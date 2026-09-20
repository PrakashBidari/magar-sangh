<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'donations' => Donation::count(),
            'donation_total' => Donation::sum('amount'),
            'messages' => ContactMessage::count(),
        ];

        return view('dashboard.index', compact('stats'));
    }

    public function users()
    {
        return view('dashboard.users');
    }

    public function userShow(User $user)
    {
        return view('dashboard.user-show', compact('user'));
    }

    public function donations()
    {
        return view('dashboard.donations');
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}
