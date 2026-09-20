<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\News;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::orderByDesc('published_at')->paginate(9);

        return view('media.articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        $related = Article::where('id', '!=', $article->id)->orderByDesc('published_at')->take(3)->get();
        $recentNews = News::orderByDesc('published_at')->take(5)->get();

        return view('media.articles.show', compact('article', 'related', 'recentNews'));
    }
}
