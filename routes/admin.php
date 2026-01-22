<?php

use App\Http\Controllers\Backend\AdminDashboardController;
use App\Http\Controllers\Backend\AnnouncementController;
use App\Http\Controllers\Backend\AttendanceController;
use App\Http\Controllers\Backend\BotSettingsController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\DocumentController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\HHIntegrationController;
use App\Http\Controllers\Backend\MeetingController;
use App\Http\Controllers\Backend\OfficeLocationController;
use App\Http\Controllers\Backend\OfficeWifiController;

use App\Http\Controllers\Backend\OnboardingController;
use App\Http\Controllers\Backend\OrgChartController;
use App\Http\Controllers\Backend\RecruitmentController;
use App\Http\Controllers\Backend\TimeOffController;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::post('/employee/{employee}/send-welcome', [EmployeeController::class, 'sendWelcomeEmail'])
    ->name('employee.send-welcome');
Route::post('/employee/{employee}/telegram-welcome', [EmployeeController::class, 'sendTelegramWelcome'])
    ->name('employee.telegram-welcome');
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

//////////////////////////////////////////////////
// Bot Settings Routes
///////////////////////////////////////////////////

Route::get('/bot-settings', [BotSettingsController::class, 'index'])
    ->name('bot-settings.index');
Route::put('/bot-settings', [BotSettingsController::class, 'update'])
    ->name('bot-settings.update');

//////////////////////////////////////////////////
// Office Location Routes
///////////////////////////////////////////////////

Route::resource('office-location', OfficeLocationController::class);

//////////////////////////////////////////////////
//  Office WiFi Networks
///////////////////////////////////////////////////

Route::resource('office-wifi', OfficeWifiController::class);

//////////////////////////////////////////////////
//  Attendance Management Routes
///////////////////////////////////////////////////

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->name('attendance.index');
Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
    ->name('attendance.show');

//////////////////////////////////////////////////
//  Documents Management Routes
///////////////////////////////////////////////////


Route::get('policies', [DocumentController::class, 'policies'])->name('document.policies');

Route::get('employee/{employee}/document', [DocumentController::class, 'index'])->name('document.index');

Route::post('document', [DocumentController::class, 'store'])->name('document.store');

Route::get('document/{document}/download', [DocumentController::class, 'download'])->name('document.download');
Route::delete('document/{document}', [DocumentController::class, 'destroy'])->name('document.destroy');

//////////////////////////////////////////////////
//  Recruitment Management Routes
///////////////////////////////////////////////////

Route::get('recruitment/all', [RecruitmentController::class, 'list'])->name('recruitment.list');
Route::get('/companies/{company_id}/departments', [RecruitmentController::class, 'getDepartments'])
    ->name('recruitment.get_departments');
Route::put('recruitment/{recruitment}/status', [RecruitmentController::class, 'updateStatus'])
    ->name('recruitment.status');
Route::resource('recruitment', RecruitmentController::class);


//////////////////////////////////////////////////
//  Recruitment Management Routes
///////////////////////////////////////////////////

Route::patch('/candidates/{candidate}/status', [RecruitmentController::class, 'updateCandidateStatus'])
    ->name('candidates.update-status');

Route::get('/candidates/{candidate}/download', [RecruitmentController::class, 'downloadCandidateResume'])
    ->name('candidates.download');

Route::patch('/candidates/{candidate}/schedule', [RecruitmentController::class, 'updateInterviewSchedule'])
    ->name('candidates.update-schedule');

//////////////////////////////////////////////////
//  HH Integration Management Routes
///////////////////////////////////////////////////

Route::get('hh/connect', [HHIntegrationController::class, 'connect'])->name('hh.connect');
Route::get('hh/callback', [HHIntegrationController::class, 'callback'])->name('hh.callback');

//////////////////////////////////////////////////
//  Onboarding Management (Day 11)
//////////////////////////////////////////////////

Route::get('/companies/{company}/employees-ajax', [OnboardingController::class, 'getEmployeesByCompany'])
    ->name('companies.employees-ajax');

Route::patch('/onboarding/{onboarding}/status', [OnboardingController::class, 'updateStatus'])
    ->name('onboarding.update-status');

Route::resource('onboarding', OnboardingController::class);

//////////////////////////////////////////////////
//  Announcements
//////////////////////////////////////////////////

Route::get('/departments/{department}/employees-ajax', function (\App\Models\Department $department) {

    return $department->employees()
        ->withoutGlobalScopes() // 🔥 Important: Admins might need to bypass tenant scopes
        ->select('id', 'first_name', 'last_name')
        ->get()
        ->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name]);
})->name('departments.employees-ajax');

Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'destroy']);

//////////////////////////////////////////////////
//  Org Chart
//////////////////////////////////////////////////

Route::get('org-chart', [OrgChartController::class, 'index'])->name('org-chart.index');
Route::get('org-chart/data', [OrgChartController::class, 'data'])->name('org-chart.data');

//////////////////////////////////////////////////
//  Meetings Management
//////////////////////////////////////////////////