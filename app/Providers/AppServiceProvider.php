<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
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
        RateLimiter::for('api', function () {
            $key = request()->user()?->id ?: request()->ip();
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('uploads', function () {
            $key = request()->user()?->id ?: request()->ip();
            return [
                Limit::perMinute(10)->by($key),
                Limit::perDay(100)->by($key),
            ];
        });
    }
}
