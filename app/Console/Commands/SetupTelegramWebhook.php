<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SetupTelegramWebhook extends Command
{
    protected $signature = 'telegram:setup-webhook {--remove : Remove webhook instead}';
    protected $description = 'Set up Telegram webhook';

    public function handle()
    {
        $botToken = config('telegram.bot_token');

        if (!$botToken) {
            $this->error("❌ TELEGRAM_BOT_TOKEN not found in .env file!");
            $this->info("Add this to your .env:");
            $this->info("TELEGRAM_BOT_TOKEN=your_bot_token_here");
            return 1;
        }

        $telegram = new Api($botToken);

        // If --remove flag is set, remove webhook
        if ($this->option('remove')) {
            return $this->removeWebhook($telegram);
        }

        $webhookUrl = config('telegram.webhook_url');

        if (!$webhookUrl) {
            $this->error("❌ TELEGRAM_WEBHOOK_URL not found in .env file!");
            $this->info("Add this to your .env:");
            $this->info("TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook");
            return 1;
        }

        // Check if URL is HTTPS (required for production)
        if (!str_starts_with($webhookUrl, 'https://')) {
            $this->warn("⚠️  Warning: Webhook URL should use HTTPS in production!");
            $this->warn("Current URL: {$webhookUrl}");
            $this->newLine();
            $this->info("For local development, use polling mode instead:");
            $this->info("php artisan telegram:polling");
            $this->newLine();

            if (!$this->confirm('Continue anyway? (Not recommended for production)', false)) {
                return 1;
            }
        }

        try {
            $this->info("Setting webhook to: {$webhookUrl}");

            $response = $telegram->setWebhook([
                'url' => $webhookUrl,
                'allowed_updates' => ['message', 'callback_query', 'edited_message'],
            ]);

            if ($response) {
                $this->info("✅ Webhook set successfully!");
                $this->info("URL: {$webhookUrl}");
                $this->newLine();
                $this->info("Verify with: php artisan telegram:webhook-info");
            } else {
                $this->error("❌ Failed to set webhook");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->newLine();
            $this->info("💡 For local development, try polling mode instead:");
            $this->info("php artisan telegram:polling");
            return 1;
        }

        return 0;
    }

    protected function removeWebhook(Api $telegram)
    {
        try {
            $telegram->removeWebhook();
            $this->info("✅ Webhook removed successfully!");
            $this->info("You can now use polling mode: php artisan telegram:polling");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
