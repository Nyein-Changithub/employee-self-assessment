<?php

namespace App\Providers;

use App\Support\Rbac;
use Illuminate\Support\Facades\Gate;
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
        // The admin role passes every permission check, including permissions created later in the UI.
        Gate::before(fn ($user) => $user?->hasRole(Rbac::SUPER_ROLE) ? true : null);
        //
    }
}
