<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\WiFiVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $tenant = app('tenant'); // Get current tenant
    return view('dashboard', compact('tenant'));
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//////////////////////////////////////////////////
// Telegram WiFi Verification (public routes)
//////////////////////////////////////////////////

Route::get('/telegram/verify-wifi/{attendance_id}', function ($attendanceId) {
    $ip = request()->ip();
    $attendance = \App\Models\Attendance::find($attendanceId);

    if (!$attendance) {
        return view('telegram.wifi-error', ['message' => 'Attendance record not found']);
    }

    $networks = \App\Models\OfficeWifiNetwork::where('company_id', $attendance->company_id)
        ->where('is_active', true)
        ->get();

    foreach ($networks as $network) {
        if ($network->isIpInRange($ip)) {
            $attendance->update([
                'is_wifi_verified' => true,
                'check_in_ip' => $ip
            ]);
            return view('telegram.wifi-success');
        }
    }

    return view('telegram.wifi-error', [
        'message' => 'Not connected to office WiFi',
        'ip' => $ip
    ]);
})->name('telegram.verify-wifi');


//////////////////////////////////////////////////
// Employee Task Controller (public routes)
//////////////////////////////////////////////////

// Route::get('/my-onboarding', [EmployeeTaskController::class, 'index'])->name('my-onboarding');
// Route::post('/my-onboarding/{task}/complete', [EmployeeTaskController::class, 'complete'])->name('my-onboarding.complete');

require __DIR__ . '/auth.php';
