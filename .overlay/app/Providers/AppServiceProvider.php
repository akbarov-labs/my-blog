<?php

namespace App\Providers;

use App\Models\Profile;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Make the site profile (name, footer text, ...) available to every
     * Blade view without every controller having to pass it explicitly.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            View::share('profile', Profile::current());
        } catch (\Throwable $e) {
            // Table not migrated yet — views fall back gracefully via ?-> null-safe access.
        }
    }
}
