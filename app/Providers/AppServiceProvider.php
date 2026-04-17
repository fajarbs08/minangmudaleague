<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Paginator::useBootstrapFive();

        if (!app()->environment('production')) {
            return;
        }

        $configuredUrl = (string) config('app.url');

        if (Str::startsWith($configuredUrl, 'https://')) {
            URL::forceRootUrl($configuredUrl);
            URL::forceScheme('https');

            return;
        }

        if (!app()->runningInConsole() && request()->headers->get('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
