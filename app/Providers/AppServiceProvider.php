<?php

namespace App\Providers;

use App\Services\DocumentService;
use App\Services\EmployeeService;
use App\Services\Interfaces\DocumentServiceInterface;
use Illuminate\Pagination\Paginator;
use App\Services\Interfaces\TimeOffServiceInterface; // <-- Add this import
use App\Services\TimeOffService;
use App\Services\Interfaces\EmployeeServiceInterface;
use App\Services\Interfaces\RecruitmentServiceInterface;
use App\Services\RecruitmentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
        $this->app->bind(TimeOffServiceInterface::class, TimeOffService::class);
        $this->app->bind(DocumentServiceInterface::class, DocumentService::class);
        $this->app->bind(RecruitmentServiceInterface::class, RecruitmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tell Laravel to use Bootstrap 5 for pagination links
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            $tenant = app()->has('tenant') ? app('tenant') : null;
            $view->with('currentTenant', $tenant);
        });
    }
}
