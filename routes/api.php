<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*
|--------------------------------------------------------------------------
| Telegram Bot Routes
|--------------------------------------------------------------------------
|
| These routes handle Telegram webhook and setup.
| Note: All routes in api.php automatically get /api/ prefix
|
| So these routes become:
| - POST /api/telegram/webhook
| - GET  /api/telegram/set-webhook
| - GET  /api/telegram/webhook-info
| - GET  /api/telegram/remove-webhook
|
*/

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
Route::get('/telegram/set-webhook', [TelegramWebhookController::class, 'setWebhook']);
Route::get('/telegram/remove-webhook', [TelegramWebhookController::class, 'removeWebhook']);
Route::get('/telegram/webhook-info', [TelegramWebhookController::class, 'getWebhookInfo']);
