<?php

namespace App\Http\Controllers;

use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $newsItems = News::orderByDesc('published_at')->paginate(9);

        return view('media.news.index', compact('newsItems'));
    }

    public function show(News $news)
    {
        $related = News::where('id', '!=', $news->id)->orderByDesc('published_at')->take(3)->get();
        $recentNews = News::where('id', '!=', $news->id)->orderByDesc('published_at')->take(5)->get();

        return view('media.news.show', compact('news', 'related', 'recentNews'));
    }
}
