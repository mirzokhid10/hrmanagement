<?php

namespace App\Providers;

use App\Services\EmployeeService;
use App\Services\Interfaces\EmployeeServiceInterface;
use Illuminate\Support\ServiceProvider;

class EmployeeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
    }

    public function boot(): void
    {
        //
    }
}
