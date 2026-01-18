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

Route::get('/telegram/verify-wifi/{attendance}', [WiFiVerificationController::class, 'show'])
    ->name('telegram.verify-wifi');
Route::post('/telegram/verify-wifi/{attendance}', [WiFiVerificationController::class, 'verify'])
    ->name('telegram.verify-wifi.process');


// Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
require __DIR__ . '/auth.php';
