<?php

namespace App\Http\Controllers;

use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $newsItems = News::approved()->orderByDesc('published_at')->paginate(9);

        return view('media.news.index', compact('newsItems'));
    }

    public function show(News $news)
    {
        abort_unless($news->isApproved(), 404);

        $related = News::approved()->where('id', '!=', $news->id)->orderByDesc('published_at')->take(3)->get();
        $recentNews = News::approved()->where('id', '!=', $news->id)->orderByDesc('published_at')->take(5)->get();

        return view('media.news.show', compact('news', 'related', 'recentNews'));
    }
}
