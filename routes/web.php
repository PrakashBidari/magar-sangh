<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\MembershipApplicationController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\SisterOrganizationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/storagelink', function () {
    Artisan::call('storage:link');
    return 'Storage link created!';
});

Route::prefix('about')->name('about.')->group(function () {
    Route::get('/', [AboutController::class, 'index'])->name('index');
    Route::get('/committee', [AboutController::class, 'committee'])->name('committee');
    Route::get('/constitution', [AboutController::class, 'constitution'])->name('constitution');
});

Route::prefix('media')->name('media.')->group(function () {
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');

    Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
});

Route::prefix('gallery')->name('gallery.')->group(function () {
    Route::get('/photos', [GalleryController::class, 'photos'])->name('photos');
    Route::get('/videos', [GalleryController::class, 'videos'])->name('videos');
});

Route::get('/sister-organizations', [SisterOrganizationController::class, 'index'])->name('sister-organizations');

Route::get('/membership', [MembershipController::class, 'types'])->name('membership.types');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');

Route::get('/donation-list', function () {
    return view('donation-list');
})->name('donation-list');

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    // Every signed-in account (admin or user) gets the dashboard home.
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // Members: apply, follow the application, download the ID card.
    Route::prefix('my-membership')->name('my-membership.')->group(function () {
        Route::get('/', [MembershipController::class, 'show'])->name('show');
        Route::get('/apply', [MembershipController::class, 'create'])->name('create');
        Route::post('/apply', [MembershipController::class, 'store'])->name('store');
    });

    // Owner or admin (checked in the controller).
    Route::get('/membership/{membership}/card', [MembershipController::class, 'card'])->whereNumber('membership')->name('membership.card');
    Route::get('/membership/{membership}/voucher', [MembershipController::class, 'voucher'])->whereNumber('membership')->name('membership.voucher');

    // Content management is admin only.
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
        Route::prefix('membership')->name('membership.')->controller(MembershipApplicationController::class)->group(function () {
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/approved', 'approved')->name('approved');
            Route::get('/disapproved', 'rejected')->name('rejected');

            Route::prefix('applications/{membership}')->whereNumber('membership')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::delete('/', 'destroy')->name('destroy');
                Route::post('/approve', 'approve')->name('approve');
                Route::post('/disapprove', 'reject')->name('reject');
            });
        });

        // Simple daily income & expense book.
        Route::resource('accounting', TransactionController::class)
            ->parameters(['accounting' => 'transaction'])
            ->except(['show']);

        Route::post('/editor-upload', EditorUploadController::class)->name('editor-upload');

        Route::prefix('manage')->group(function () {
            foreach (config('admin.resources') as $key => $cfg) {
                $registrar = Route::resource($key, $cfg['controller'] ?? ResourceController::class)
                    ->parameters([$key => 'id']);

                if ($cfg['readonly'] ?? false) {
                    $registrar->only(['index', 'show', 'destroy']);
                } else {
                    $registrar->except(['show']);
                }
            }
        });
    });
});
