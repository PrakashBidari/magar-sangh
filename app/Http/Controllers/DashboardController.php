<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\Membership;
use App\Models\News;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = [];
        $money = [];
        $quickAdd = [];

        if ($user->can('access-admin')) {
            // Values are closures so only the cards this user may see are counted.
            $income = fn () => (float) Transaction::where('type', Transaction::INCOME)->sum('amount');
            $expense = fn () => (float) Transaction::where('type', Transaction::EXPENSE)->sum('amount');

            // Money collected per section.
            $money = [
                ['label' => 'Lakhan Thapa Pratisthan Donations', 'icon' => '💰', 'value' => fn () => (float) Donation::approved()->sum('amount'), 'route' => 'dashboard.donations.index', 'can' => 'donations.view'],
                ['label' => 'Membership Fees', 'icon' => '🎫', 'value' => fn () => Membership::totalCollected(), 'route' => 'dashboard.membership.approved', 'can' => 'membership-applications.view'],
                ['label' => 'Accounting Income', 'icon' => '📥', 'value' => $income, 'route' => 'dashboard.accounting.index', 'params' => ['type' => Transaction::INCOME], 'can' => 'accounting.view'],
                ['label' => 'Accounting Expense', 'icon' => '📤', 'value' => $expense, 'route' => 'dashboard.accounting.index', 'params' => ['type' => Transaction::EXPENSE], 'negative' => true, 'can' => 'accounting.view'],
                ['label' => 'Accounting Balance', 'icon' => '📒', 'value' => fn () => $income() - $expense(), 'route' => 'dashboard.accounting.index', 'can' => 'accounting.view'],
            ];

            $stats = [
                ['label' => 'Total Users', 'value' => fn () => User::count(), 'route' => 'dashboard.users.index', 'can' => 'users.view'],
                ['label' => 'Pending Applications', 'value' => fn () => Membership::where('status', Membership::PENDING)->count(), 'route' => 'dashboard.membership.pending', 'accent' => true, 'can' => 'membership-applications.view'],
                ['label' => 'Active Members', 'value' => fn () => Membership::active()->count(), 'route' => 'dashboard.membership.approved', 'can' => 'membership-applications.view'],
                ['label' => 'Donations', 'value' => fn () => Donation::count(), 'route' => 'dashboard.donations.index', 'can' => 'donations.view'],
                ['label' => 'Pending Donations', 'value' => fn () => Donation::where('status', Donation::PENDING)->count(), 'route' => 'dashboard.donations.index', 'accent' => true, 'can' => 'donations.approve'],
                ['label' => 'Contact Messages', 'value' => fn () => ContactMessage::count(), 'route' => 'dashboard.messages.index', 'can' => 'messages.view'],
                ['label' => 'News', 'value' => fn () => News::count(), 'route' => 'dashboard.news.index', 'can' => 'news.view'],
                ['label' => 'Pending News', 'value' => fn () => News::where('status', News::PENDING)->count(), 'route' => 'dashboard.news.index', 'accent' => true, 'can' => 'news.approve'],
                ['label' => 'Articles', 'value' => fn () => Article::count(), 'route' => 'dashboard.articles.index', 'can' => 'articles.view'],
                ['label' => 'Events', 'value' => fn () => Event::count(), 'route' => 'dashboard.events.index', 'can' => 'events.view'],
                ['label' => 'Gallery Photos', 'value' => fn () => GalleryPhoto::count(), 'route' => 'dashboard.gallery-photos.index', 'can' => 'gallery-photos.view'],
            ];

            $visible = fn (array $cards) => collect($cards)
                ->filter(fn ($card) => $user->can($card['can']))
                ->map(fn ($card) => ['value' => ($card['value'])(), 'url' => route($card['route'], $card['params'] ?? [])] + $card)
                ->values()
                ->all();

            $money = $visible($money);
            $stats = array_map(fn ($stat) => ['value' => number_format($stat['value'])] + $stat, $visible($stats));

            $quickAdd = collect(['news', 'articles', 'events', 'notifications', 'publications', 'gallery-photos', 'gallery-videos', 'committee', 'sister-organizations', 'donations'])
                ->filter(fn ($key) => $user->can($key.'.create'))
                ->values()
                ->all();
        }

        return view('dashboard.index', [
            'stats' => $stats,
            'money' => $money,
            'quickAdd' => $quickAdd,
            'user' => $request->user(),
            'membership' => $request->user()->memberships()->with('type')->latest('id')->first(),
        ]);
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}
