<?php

namespace App\Providers;

use App\Support\Rbac;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        // Behind Vercel's proxy the request looks like http, so force https links (CSS/JS, forms).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // The admin role passes every permission check, including permissions created later in the UI.
        Gate::before(fn ($user) => $user?->hasRole(Rbac::SUPER_ROLE) ? true : null);
        //
    }
}
