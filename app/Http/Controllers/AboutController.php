<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Models\Setting;

class AboutController extends Controller
{
    public function index()
    {
        $settings = Setting::current();

        return view('about.index', compact('settings'));
    }

    public function committee()
    {
        $current = CommitteeMember::where('is_current', true)->orderBy('sort_order')->get();
        $pastPresidents = CommitteeMember::where('is_past_president', true)->orderBy('sort_order')->get();

        return view('about.committee', compact('current', 'pastPresidents'));
    }

    public function constitution()
    {
        $settings = Setting::current();

        return view('about.constitution', compact('settings'));
    }
}
