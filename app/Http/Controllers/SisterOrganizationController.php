<?php

namespace App\Http\Controllers;

use App\Models\SisterOrganization;

class SisterOrganizationController extends Controller
{
    public function index()
    {
        $organizations = SisterOrganization::orderBy('sort_order')->get();

        return view('sister-organizations', compact('organizations'));
    }
}
