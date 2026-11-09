<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('desktop-pos-sync', static function (Request $request): Limit|array {
            if (in_array($request->input('machine_id'), config('desktop_pos.special_machine_ids', []), true)) {
                return Limit::none();
            }

            return Limit::perMinute(120)->by($request->route()->getDomain().'|'.$request->ip());
        });
    }
}
