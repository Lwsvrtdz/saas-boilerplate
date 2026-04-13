<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        Auth::viaRequest('module-token', function (Request $request) {
            return app(ApiTokenService::class)->resolveUserFromRequest($request);
        });
    }
}
