<?php

use App\Http\Controllers\Hr\HrManagerDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HR Manager Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider. They are grouped
| by the 'hr' role middleware and an 'hr' URL prefix.
| All routes here are also implicitly part of the 'web' middleware group,
| ensuring TenantMiddleware and other web-related middleware apply.
|
*/

// No need for 'role:hr' middleware here, as it will be applied in RouteServiceProvider
// No need for '/hr' prefix here, as it will be applied in RouteServiceProvider

// Change 'hr_manager.dashboard' to 'dashboard'
Route::get('/dashboard', [HrManagerDashboardController::class, 'index'])->name('dashboard'); // This becomes hr.dashboard

// Add other HR manager-specific resource routes or custom routes here
// Example:
// Route::resource('employees', EmployeeController::class);
// Route::resource('time-offs', TimeOffController::class);
// Route::resource('vacancies', VacancyController::class);