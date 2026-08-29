<?php

namespace App\Providers;

use App\Contracts\CartContract;
use App\Contracts\MenuBrowsingContract;
use App\Contracts\TableVerificationContract;
use App\Services\CartService;
use App\Services\MenuService;
use App\Services\TableVerificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CartContract::class, CartService::class);
        $this->app->bind(MenuBrowsingContract::class, MenuService::class);
        $this->app->bind(TableVerificationContract::class, TableVerificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
            str_contains(request()->header('host', ''), 'ngrok-free.app') ||
            str_contains(request()->header('host', ''), 'ngrok.io') ||
            str_contains(request()->header('host', ''), 'trycloudflare.com') ||
            str_contains(request()->header('host', ''), 'cloudflare')
        ) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
