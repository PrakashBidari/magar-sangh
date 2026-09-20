<?php

namespace App\Http\Controllers;

use App\Models\Publication;

class PublicationController extends Controller
{
    public function index()
    {
        $publications = Publication::orderByDesc('published_date')->paginate(12);

        return view('media.publications.index', compact('publications'));
    }
}
