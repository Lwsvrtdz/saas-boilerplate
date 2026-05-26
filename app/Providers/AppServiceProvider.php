<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Services\ApiTokenService;

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
        RateLimiter::for('auth', function (Request $request): Limit {
            $email = (string) $request->input('email', '');

            return Limit::perMinute((int) config('boilerplate.auth_rate_limit_per_minute'))
                ->by($request->ip().'|'.strtolower($email));
        });

        Auth::viaRequest('module-token', function (Request $request) {
            return app(ApiTokenService::class)->resolveUserFromRequest($request);
        });
    }
}
