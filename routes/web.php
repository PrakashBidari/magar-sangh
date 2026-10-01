<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\DonationSettingController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\MembershipApplicationController;
use App\Http\Controllers\Admin\MembershipSettingController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SifarisRequestController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MyDonationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\SifarisController;
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
    Route::get('/committee/{committeeType}', [AboutController::class, 'committeeShow'])->whereNumber('committeeType')->name('committee.show');
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

Route::get('/sifaris', [SifarisController::class, 'info'])->name('sifaris');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');

Route::get('/donation-list', function () {
    return view('donation-list', ['documents' => \App\Models\DonationDocument::latest('id')->get()]);
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

    // Every user: request a sifaris (recommendation letter) and download it once approved.
    Route::prefix('my-sifaris')->name('my-sifaris.')->controller(SifarisController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/apply', 'create')->name('create');
        Route::post('/apply', 'store')->name('store');
    });
    // Owner (approved only) or admin (checked in the controller).
    Route::get('/sifaris/{sifaris}/letter', [SifarisController::class, 'letter'])->whereNumber('sifaris')->name('sifaris.letter');

    // Every user: add donations and fix the ones an admin returns (own donations only).
    Route::resource('my-donations', MyDonationController::class)
        ->parameters(['my-donations' => 'id'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->whereNumber('id');

    // Owner or admin (checked in the controller).
    Route::get('/membership/{membership}/card', [MembershipController::class, 'card'])->whereNumber('membership')->name('membership.card');
    Route::get('/membership/{membership}/voucher', [MembershipController::class, 'voucher'])->whereNumber('membership')->name('membership.voucher');

    // Content management: every route checks its own permission (see App\Support\Permissions).
    // The admin role passes every check.
    Route::middleware('can:access-admin')->group(function () {
        Route::get('/settings', [DashboardController::class, 'settings'])->middleware('can:settings.manage')->name('settings');
        Route::prefix('membership')->name('membership.')->controller(MembershipApplicationController::class)->group(function () {
            Route::middleware('can:membership-applications.view')->group(function () {
                Route::get('/pending', 'pending')->name('pending');
                Route::get('/approved', 'approved')->name('approved');
                Route::get('/disapproved', 'rejected')->name('rejected');
            });

            Route::middleware('can:membership-settings.manage')->group(function () {
                Route::get('/settings', [MembershipSettingController::class, 'edit'])->name('settings');
                Route::put('/settings', [MembershipSettingController::class, 'update'])->name('settings.update');
            });

            Route::prefix('applications/{membership}')->whereNumber('membership')->group(function () {
                Route::get('/', 'show')->middleware('can:membership-applications.view')->name('show');
                Route::get('/edit', 'edit')->middleware('can:membership-applications.edit')->name('edit');
                Route::put('/', 'update')->middleware('can:membership-applications.edit')->name('update');
                Route::delete('/', 'destroy')->middleware('can:membership-applications.delete')->name('destroy');
                Route::post('/approve', 'approve')->middleware('can:membership-applications.approve')->name('approve');
                Route::post('/disapprove', 'reject')->middleware('can:membership-applications.approve')->name('reject');
            });
        });

        Route::prefix('sifaris')->name('sifaris.')->controller(SifarisRequestController::class)->group(function () {
            Route::middleware('can:sifaris.view')->group(function () {
                Route::get('/pending', 'pending')->name('pending');
                Route::get('/approved', 'approved')->name('approved');
                Route::get('/disapproved', 'rejected')->name('rejected');
            });

            Route::prefix('{sifaris}')->whereNumber('sifaris')->group(function () {
                Route::get('/', 'show')->middleware('can:sifaris.view')->name('show');
                Route::get('/edit', 'edit')->middleware('can:sifaris.edit')->name('edit');
                Route::put('/', 'update')->middleware('can:sifaris.edit')->name('update');
                Route::put('/letter', 'updateLetter')->middleware('can:sifaris.edit')->name('letter.update');
                Route::delete('/', 'destroy')->middleware('can:sifaris.delete')->name('destroy');
                Route::post('/approve', 'approve')->middleware('can:sifaris.approve')->name('approve');
                Route::post('/disapprove', 'reject')->middleware('can:sifaris.approve')->name('reject');
            });
        });

        // Lakhan Thapa Pratisthan page content shown above the donation list.
        Route::middleware('can:donation-settings.manage')->group(function () {
            Route::get('/donation-settings', [DonationSettingController::class, 'edit'])->name('donation-settings');
            Route::put('/donation-settings', [DonationSettingController::class, 'update'])->name('donation-settings.update');
            Route::post('/donation-settings/documents', [DonationSettingController::class, 'storeDocument'])->name('donation-settings.documents.store');
            Route::delete('/donation-settings/documents/{document}', [DonationSettingController::class, 'destroyDocument'])->whereNumber('document')->name('donation-settings.documents.destroy');
        });

        // Simple daily income & expense book.
        $withPermissions = fn ($registrar, string $section) => $registrar
            ->middlewareFor(['index', 'show'], "can:{$section}.view")
            ->middlewareFor(['create', 'store'], "can:{$section}.create")
            ->middlewareFor(['edit', 'update'], "can:{$section}.edit")
            ->middlewareFor('destroy', "can:{$section}.delete");

        $withPermissions(Route::resource('accounting', TransactionController::class)
            ->parameters(['accounting' => 'transaction'])
            ->except(['show']), 'accounting');

        // Roles and the all-roles permission matrix.
        Route::prefix('access')->group(function () use ($withPermissions) {
            Route::get('/permissions', [RoleController::class, 'matrix'])->middleware('can:roles.view')->name('permissions.index');
            Route::put('/permissions', [RoleController::class, 'updateMatrix'])->middleware('can:roles.edit')->name('permissions.update');

            $withPermissions(Route::resource('roles', RoleController::class)->except(['show']), 'roles');
        });

        Route::post('/editor-upload', EditorUploadController::class)->name('editor-upload');

        Route::prefix('manage')->group(function () use ($withPermissions) {
            foreach (config('admin.resources') as $key => $cfg) {
                if ($cfg['approvable'] ?? false) {
                    Route::controller($cfg['controller'] ?? ResourceController::class)->prefix($key.'/{id}')->whereNumber('id')->middleware("can:{$key}.approve")->group(function () use ($key) {
                        Route::post('/approve', 'approve')->name($key.'.approve');
                        Route::post('/disapprove', 'reject')->name($key.'.reject');
                    });
                }

                $registrar = Route::resource($key, $cfg['controller'] ?? ResourceController::class)
                    ->parameters([$key => 'id']);

                if ($cfg['readonly'] ?? false) {
                    $registrar->only(['index', 'show', 'destroy']);
                } else {
                    $registrar->except(['show']);
                }

                $withPermissions($registrar, $key);
            }
        });
    });
});
