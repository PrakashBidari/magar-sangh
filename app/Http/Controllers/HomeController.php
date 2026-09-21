<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\Setting;
use App\Models\SisterOrganization;

class HomeController extends Controller
{
    public function index()
    {
        $settings = Setting::current();

        $heroSlides = HeroSlide::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        $pastPresidents = CommitteeMember::where('is_past_president', true)
            ->orderBy('sort_order')
            ->take(7)
            ->get();

        $latestNews = News::orderByDesc('published_at')->take(4)->get();
        $upcomingEvent = Event::where('event_date', '>=', now()->subDay())->orderBy('event_date')->first()
            ?? Event::orderByDesc('event_date')->first();
        $photos = GalleryPhoto::orderByDesc('id')->take(6)->get();
        $videos = GalleryVideo::orderByDesc('id')->take(6)->get();
        $sisterOrganizations = SisterOrganization::orderBy('sort_order')->get();

        return view('home', compact(
            'settings', 'heroSlides', 'pastPresidents', 'latestNews', 'upcomingEvent', 'photos', 'videos', 'sisterOrganizations'
        ));
    }
}
