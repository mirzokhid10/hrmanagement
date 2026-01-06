<?php

use App\Http\Controllers\Backend\AdminDashboardController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\TimeOffController;
use Illuminate\Support\Facades\Auth;
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

Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
    ->name('dashboard'); // This becomes /admin/dashboard

// Employee CRUD Routes
Route::resource('employee', EmployeeController::class)
    ->names('employee'); // This becomes /employee/*

// --- New: Time-Off Management for Admins (using Route::resource) ---
Route::resource('time-offs', TimeOffController::class);

// Custom actions for approval/rejection (these are not standard RESTful actions, so add them separately)
Route::post('time-offs/{timeOff}/approve', [TimeOffController::class, 'approve'])->name('time-offs.approve');
Route::post('time-offs/{timeOff}/reject', [TimeOffController::class, 'reject'])->name('time-offs.reject');
Route::patch('time-offs/{timeOff}/status', [TimeOffController::class, 'updateStatus'])->name('time-offs.updateStatus');

Route::resource('department', DepartmentController::class)
    ->names('department');
