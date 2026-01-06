<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    public const HOME = '/dashboard';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));


        Route::middleware(['web', 'tenant', 'auth', 'role:admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));

        // Same for HR routes.
        Route::middleware(['web', 'tenant', 'auth', 'role:hr'])
            ->prefix('hr')
            ->name('hr.')
            ->group(base_path('routes/hr.php'));
    }
}
