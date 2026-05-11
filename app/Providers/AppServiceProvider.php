<?php

namespace App\Providers;

use App\Services\SeasonContext;
use App\Services\SidebarViewModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SeasonContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.partials.main-nav', function ($view) {
            $view->with('sidebarViewModel', app(SidebarViewModel::class)->current());
        });

        if (! app()->environment('production')) {
            return;
        }

        $configuredUrl = trim((string) config('app.url'));

        if ($configuredUrl === '' || in_array($configuredUrl, ['http://localhost', 'http://127.0.0.1'], true)) {
            return;
        }

        URL::forceRootUrl($configuredUrl);

        if (Str::startsWith($configuredUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
