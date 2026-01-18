<?php

use App\Http\Controllers\Backend\TelegramWebhookController;
use Illuminate\Support\Facades\Route;


//////////////////////////////////////////////////
// Telegram Webhook
///////////////////////////////////////////////////

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handleWebhook'])
    ->name('telegram.webhook');
