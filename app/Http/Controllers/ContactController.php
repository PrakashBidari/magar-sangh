<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class ContactController extends Controller
{
    public function index()
    {
        $settings = Setting::current();

        return view('contact', compact('settings'));
    }
}
