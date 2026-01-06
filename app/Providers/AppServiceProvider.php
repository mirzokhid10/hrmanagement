<?php

namespace App\Providers;

use App\Services\EmployeeService;
use Illuminate\Pagination\Paginator;
use App\Services\Interfaces\TimeOffServiceInterface; // <-- Add this import
use App\Services\TimeOffService;
use App\Services\Interfaces\EmployeeServiceInterface;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
        $this->app->bind(TimeOffServiceInterface::class, TimeOffService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tell Laravel to use Bootstrap 5 for pagination links
        Paginator::useBootstrapFive();
    }
}
