<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;

class GalleryController extends Controller
{
    public function photos()
    {
        $photos = GalleryPhoto::orderByDesc('id')->paginate(18);

        return view('gallery.photos', compact('photos'));
    }

    public function videos()
    {
        $videos = GalleryVideo::orderByDesc('id')->paginate(12);

        return view('gallery.videos', compact('videos'));
    }
}
