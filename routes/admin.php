<?php

use App\Http\Controllers\Backend\AdminDashboardController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider. They are grouped
| by the 'admin' role middleware and an 'admin' URL prefix.
| All routes here are also implicitly part of the 'web' middleware group,
| ensuring TenantMiddleware and other web-related middleware apply.
|
*/

// No need for 'role:admin' middleware here, as it will be applied in RouteServiceProvider
// No need for '/admin' prefix here, as it will be applied in RouteServiceProvider

Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

// Add other admin-specific resource routes or custom routes here
// Example:
// Route::resource('users', UserController::class); // For managing users (within the company)
// Route::get('settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
