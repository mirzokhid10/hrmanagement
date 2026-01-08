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


//////////////////////////////////////////////////
// Admin Dashboard Route
//////////////////////////////////////////////////

Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
    ->name('dashboard'); // This becomes /admin/dashboard

//////////////////////////////////////////////////
// Employee CRUD Routes
///////////////////////////////////////////////////

Route::resource('employee', EmployeeController::class)
    ->names('employee');

// Route to fetch departments based on selected company (for dynamic dropdowns via AJAX)
Route::get('/companies/{company}/departments', [EmployeeController::class, 'getDepartmentsByCompany'])
    ->name('companies.departments');

//////////////////////////////////////////////////
// Time-Off Management Routes
///////////////////////////////////////////////////

Route::resource('time-offs', TimeOffController::class);
Route::post('time-offs/{timeOff}/approve', [TimeOffController::class, 'approve'])->name('time-offs.approve');
Route::post('time-offs/{timeOff}/reject', [TimeOffController::class, 'reject'])->name('time-offs.reject');
Route::patch('time-offs/{timeOff}/status', [TimeOffController::class, 'updateStatus'])->name('time-offs.updateStatus');

Route::get('/ajax/get-departments/{companyId}', [TimeOffController::class, 'getDepartments'])->name('ajax.get-departments');
Route::get('/ajax/get-employees', [TimeOffController::class, 'getEmployees'])->name('ajax.get-employees');
//////////////////////////////////////////////////
// Department Management Routes
///////////////////////////////////////////////////

Route::resource('department', DepartmentController::class)
    ->names('department');
