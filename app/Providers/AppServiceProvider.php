<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('siteSettings', Setting::current());
        });

        Paginator::defaultView('partials.pagination');

        // The admin role can do everything, whatever permissions exist.
        Gate::before(fn (User $user) => $user->hasRole(Permissions::ADMIN_ROLE) ? true : null);

        // Opens the admin side of the dashboard: any permission at all.
        Gate::define('access-admin', fn (User $user) => $user->getAllPermissions()->isNotEmpty());
    }
}
