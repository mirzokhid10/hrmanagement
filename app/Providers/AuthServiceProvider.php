<?php

namespace App\Providers;

use App\Models\OfficeLocation;
use App\Policies\OfficeLocationPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        // ... other policies
        OfficeLocation::class => OfficeLocationPolicy::class,
    ];
}
