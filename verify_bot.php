<?php

use Illuminate\Support\Facades\Route;

/**
 * Simple Telegram Bot Verification Script
 *
 * Run this from project root:
 * php verify_bot.php
 */

echo "\n";
echo "🤖 TELEGRAM BOT VERIFICATION\n";
echo str_repeat("=", 60) . "\n\n";

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Check .env
echo "1. Checking .env configuration...\n";
$token = env('TELEGRAM_BOT_TOKEN');
$webhook = env('TELEGRAM_WEBHOOK_URL');

if (!$token) {
    echo "   ❌ TELEGRAM_BOT_TOKEN not set!\n\n";
    exit(1);
}
echo "   ✅ Bot token configured\n";

if (!$webhook) {
    echo "   ❌ TELEGRAM_WEBHOOK_URL not set!\n\n";
    exit(1);
}
echo "   ✅ Webhook URL: $webhook\n";

// Check if webhook has /api/ prefix
if (!str_contains($webhook, '/api/telegram/webhook')) {
    echo "   ⚠️  WARNING: Webhook should include /api/telegram/webhook\n";
    echo "   Current: $webhook\n";
    echo "   Should be: " . str_replace('/telegram/webhook', '/api/telegram/webhook', $webhook) . "\n\n";
} else {
    echo "   ✅ Webhook URL format correct\n";
}
echo "\n";

// Test 2: Check routes
echo "2. Checking routes...\n";
$routes = Route::getRoutes();
$telegramRoutes = 0;

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'telegram')) {
        $telegramRoutes++;
        $method = implode('|', $route->methods());
        echo "   ✅ $method api/{$route->uri()}\n";
    }
}

if ($telegramRoutes === 0) {
    echo "   ❌ NO TELEGRAM ROUTES FOUND!\n";
    echo "   → Add routes to routes/api.php\n\n";
    exit(1);
}
echo "\n";

// Test 3: Check controller
echo "3. Checking controller file...\n";
$controllerPath = app_path('Http/Controllers/TelegramWebhookController.php');
if (!file_exists($controllerPath)) {
    echo "   ❌ TelegramWebhookController.php NOT FOUND!\n";
    echo "   Expected: $controllerPath\n\n";
    exit(1);
}
echo "   ✅ Controller exists\n\n";

// Test 4: Check Telegram API
echo "4. Testing Telegram API connection...\n";
$url = "https://api.telegram.org/bot{$token}/getMe";
$response = @file_get_contents($url);

if (!$response) {
    echo "   ❌ Cannot connect to Telegram API\n";
    echo "   Check your internet connection\n\n";
    exit(1);
}

$data = json_decode($response, true);
if ($data['ok']) {
    echo "   ✅ Connected to Telegram API\n";
    echo "   Bot: @{$data['result']['username']}\n";
    echo "   ID: {$data['result']['id']}\n\n";
} else {
    echo "   ❌ Invalid bot token\n\n";
    exit(1);
}

// Test 5: Check current webhook status
echo "5. Checking webhook status...\n";
$url = "https://api.telegram.org/bot{$token}/getWebhookInfo";
$response = @file_get_contents($url);
$data = json_decode($response, true);

if ($data['ok']) {
    $info = $data['result'];

    if (empty($info['url'])) {
        echo "   ⚠️  Webhook not set\n";
        echo "   → Visit /api/telegram/set-webhook to set it\n\n";
    } else {
        echo "   Current webhook: {$info['url']}\n";

        if ($info['url'] !== $webhook) {
            echo "   ⚠️  Webhook URL mismatch!\n";
            echo "   Config says: $webhook\n";
            echo "   Telegram has: {$info['url']}\n";
            echo "   → Delete and set webhook again\n\n";
        } else {
            echo "   ✅ Webhook URL matches config\n";
        }

        if ($info['pending_update_count'] > 0) {
            echo "   ⚠️  {$info['pending_update_count']} pending updates\n";
        } else {
            echo "   ✅ No pending updates\n";
        }

        if (isset($info['last_error_message'])) {
            echo "   ❌ Last error: {$info['last_error_message']}\n";
            echo "   Date: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
        } else {
            echo "   ✅ No errors\n";
        }
    }
}
echo "\n";

// Summary
echo str_repeat("=", 60) . "\n";
echo "📊 SUMMARY\n\n";

if ($telegramRoutes > 0 && file_exists($controllerPath) && $data['ok']) {
    echo "✅ Basic configuration looks good!\n\n";
    echo "Next steps:\n";
    echo "1. Clear cache: php artisan route:clear\n";
    echo "2. Set webhook: Visit /api/telegram/set-webhook in browser\n";
    echo "3. Test: Send /start to @{$data['result']['username']}\n\n";
} else {
    echo "❌ Configuration issues found. Fix the errors above.\n\n";
}

echo str_repeat("=", 60) . "\n\n";
