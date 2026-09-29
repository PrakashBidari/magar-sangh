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
        $stats = [];
        $money = [];

        if ($request->user()->hasRole('admin')) {
            // Money collected per section.
            $income = (float) Transaction::where('type', Transaction::INCOME)->sum('amount');
            $expense = (float) Transaction::where('type', Transaction::EXPENSE)->sum('amount');
            $money = [
                ['label' => 'Lakhan Thapa Pratisthan Donations', 'icon' => '💰', 'value' => (float) Donation::sum('amount'), 'route' => route('dashboard.donations.index')],
                ['label' => 'Membership Fees', 'icon' => '🎫', 'value' => Membership::totalCollected(), 'route' => route('dashboard.membership.approved')],
                ['label' => 'Accounting Income', 'icon' => '📥', 'value' => $income, 'route' => route('dashboard.accounting.index', ['type' => Transaction::INCOME])],
                ['label' => 'Accounting Expense', 'icon' => '📤', 'value' => $expense, 'route' => route('dashboard.accounting.index', ['type' => Transaction::EXPENSE]), 'negative' => true],
                ['label' => 'Accounting Balance', 'icon' => '📒', 'value' => $income - $expense, 'route' => route('dashboard.accounting.index')],
            ];

            $stats = [
                ['label' => 'Total Users', 'value' => number_format(User::count()), 'route' => 'dashboard.users.index'],
                ['label' => 'Pending Applications', 'value' => number_format(Membership::where('status', Membership::PENDING)->count()), 'route' => 'dashboard.membership.pending', 'accent' => true],
                ['label' => 'Active Members', 'value' => number_format(Membership::active()->count()), 'route' => 'dashboard.membership.approved'],
                ['label' => 'Donations', 'value' => number_format(Donation::count()), 'route' => 'dashboard.donations.index'],
                ['label' => 'Contact Messages', 'value' => number_format(ContactMessage::count()), 'route' => 'dashboard.messages.index'],
                ['label' => 'News', 'value' => number_format(News::count()), 'route' => 'dashboard.news.index'],
                ['label' => 'Articles', 'value' => number_format(Article::count()), 'route' => 'dashboard.articles.index'],
                ['label' => 'Events', 'value' => number_format(Event::count()), 'route' => 'dashboard.events.index'],
                ['label' => 'Gallery Photos', 'value' => number_format(GalleryPhoto::count()), 'route' => 'dashboard.gallery-photos.index'],
            ];
        }

        return view('dashboard.index', [
            'stats' => $stats,
            'money' => $money,
            'user' => $request->user(),
            'membership' => $request->user()->memberships()->with('type')->latest('id')->first(),
        ]);
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}
