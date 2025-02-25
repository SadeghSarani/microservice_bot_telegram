<?php

namespace App\Providers;

use App\Models\TelegramUser;
use App\Observers\TelegramUserObserver;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('telegramBot', function () {
            return new \App\Service\TelegramBot();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });

        TelegramUser::observe(TelegramUserObserver::class);
    }
}
