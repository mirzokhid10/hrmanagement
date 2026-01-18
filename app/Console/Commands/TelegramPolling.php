<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\TelegramWebhookController;
use Illuminate\Console\Command;
use Telegram\Bot\Api;


class TelegramPolling extends Command
{
    protected $signature = 'telegram:polling';
    protected $description = 'Run Telegram bot in polling mode (for local development)';

    public function handle()
    {
        $botToken = config('telegram.bot_token');

        if (!$botToken) {
            $this->error("❌ TELEGRAM_BOT_TOKEN not found in .env!");
            return 1;
        }

        $telegram = new Api($botToken);

        // Remove webhook if set
        try {
            $telegram->removeWebhook();
            $this->info("✅ Webhook removed (polling mode enabled)");
        } catch (\Exception $e) {
            // Ignore
        }

        $this->info("🤖 Bot is now running in polling mode...");
        $this->info("Press Ctrl+C to stop");
        $this->newLine();

        $offset = 0;

        while (true) {
            try {
                $updates = $telegram->getUpdates([
                    'offset' => $offset,
                    'timeout' => 30,
                ]);

                foreach ($updates as $update) {
                    $offset = $update->getUpdateId() + 1;

                    $this->info("📨 New update: " . $update->getUpdateId());

                    // Process the update using the same controller logic
                    $this->processUpdate($telegram, $update);
                }
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                sleep(5);
            }

            usleep(100000); // 100ms delay
        }
    }

    protected function processUpdate(Api $telegram, $update)
    {
        try {
            // Import controller logic
            $controller = app(TelegramWebhookController::class);

            // Create a mock request with the update
            $request = request();
            $request->merge([
                'update' => $update->toArray(),
            ]);

            // Process through the controller
            if ($update->getMessage()) {
                $message = $update->getMessage();
                $chatId = $message->getChat()->getId();
                $text = $message->getText() ?? '[Media]';
                $from = $message->getFrom()->getFirstName();

                $this->line("👤 From: {$from}");
                $this->line("💬 Message: {$text}");

                // Handle the message
                $this->handleMessage($telegram, $message);
            }

            $this->newLine();
        } catch (\Exception $e) {
            $this->error("Processing error: " . $e->getMessage());
        }
    }

    protected function handleMessage($telegram, $message)
    {
        // Re-use the webhook controller logic
        // For now, we'll create a simple dispatcher

        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        // This will be handled by TelegramWebhookController
        // We're just logging here for polling mode
    }
}
