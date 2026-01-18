<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class RemoveTelegramWebhook extends Command
{
    protected $signature = 'telegram:remove-webhook';
    protected $description = 'Remove Telegram webhook';

    public function handle()
    {
        $telegram = new Api(config('telegram.bot_token'));

        try {
            $telegram->removeWebhook();
            $this->info("✅ Webhook removed successfully!");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
