<?php

namespace App\Providers;

use App\WebSocket\Chat;
use Illuminate\Contracts\Foundation\Application;
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
//        $this->app->singleton(Chat::class, function (Application $app) {
//            return new Chat();
//        });
    }
}
