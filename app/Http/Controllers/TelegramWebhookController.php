<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; // <--- IMPORTANT
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    protected TelegramRouterService $router;

    public function __construct(TelegramRouterService $router)
    {
        $this->router = $router;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handle(Request $request)
    {
        try {
            $update = Telegram::getWebhookUpdate();

            // Safety check
            if (!$update->has('message') && !$update->has('callback_query')) {
                return response('OK');
            }

            // Get chat ID
            if ($update->has('callback_query')) {
                $chatId = $update->getCallbackQuery()->getMessage()->getChat()->getId();
            } else {
                $chatId = $update->getMessage()->getChat()->getId();
            }

            // Prevent concurrent processing for same user
            $lock = Cache::lock("bot_processing_{$chatId}", 5);

            if (!$lock->get()) {
                return response('OK');
            }

            try {
                // Route to appropriate handler
                $this->router->route($update, $chatId);
            } finally {
                $lock->release();
            }
        } catch (\Throwable $e) {
            // Log error
            Log::error('Telegram Bot Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Send error to developer (optional - change to your chat ID)
            try {
                $debugChatId = config('telegram.debug_chat_id', '632710453');

                $errorMsg = "⚠️ *Bot Error*\n\n";
                $errorMsg .= "📄 " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
                $errorMsg .= "❌ " . substr($e->getMessage(), 0, 200);

                Telegram::sendMessage([
                    'chat_id' => $debugChatId,
                    'text' => $errorMsg,
                    'parse_mode' => 'Markdown'
                ]);
            } catch (\Exception $ex) {
                // Silent fail if can't send error message
            }
        }

        return response('OK');
    }

    /**
     * Set webhook URL (for initial setup)
     */
    public function setWebhook()
    {
        $url = config('telegram.webhook_url');

        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook URL not configured in .env file'
            ]);
        }

        try {
            $response = Telegram::setWebhook(['url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook set successfully',
                'url' => $url,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove webhook (for testing with polling)
     */
    public function removeWebhook()
    {
        try {
            Telegram::removeWebhook();

            return response()->json([
                'success' => true,
                'message' => 'Webhook removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get webhook info (for debugging)
     */
    public function getWebhookInfo()
    {
        try {
            $info = Telegram::getWebhookInfo();

            return response()->json([
                'success' => true,
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
