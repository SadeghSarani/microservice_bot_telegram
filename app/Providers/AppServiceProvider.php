<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
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
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });
    }
}
