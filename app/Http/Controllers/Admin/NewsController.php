<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * News needs approval before it shows on the site.
 * News added by someone with "news.approve" is approved straight away; any save
 * by someone without it sends the news back to pending.
 */
class NewsController extends ResourceController
{
    protected function persist(Request $request, ?Model $model): Model
    {
        $news = parent::persist($request, $model);

        if ($request->user()->can('news.approve')) {
            if (! $model) {
                $news->approve($request->user());
            }
        } elseif ($news->status !== News::PENDING || $news->reviewed_by) {
            $news->update(['status' => News::PENDING, 'reviewed_by' => null, 'reviewed_at' => null]);
        }

        return $news;
    }
}
