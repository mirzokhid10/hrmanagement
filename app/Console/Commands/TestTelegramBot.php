<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class TestTelegramBot extends Command
{
    protected $signature = 'telegram:test';
    protected $description = 'Test Telegram bot connection';

    public function handle()
    {
        $botToken = config('telegram.bot_token');

        if (!$botToken) {
            $this->error("❌ TELEGRAM_BOT_TOKEN not found in .env!");
            return 1;
        }

        $this->info("Testing bot connection...");

        try {
            $telegram = new Api($botToken);
            $me = $telegram->getMe();

            $this->info("✅ Connection successful!");
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Bot ID', $me->getId()],
                    ['Bot Name', $me->getFirstName()],
                    ['Username', '@' . $me->getUsername()],
                    ['Can Join Groups', $me->getCanJoinGroups() ? 'Yes' : 'No'],
                    ['Can Read Messages', $me->getCanReadAllGroupMessages() ? 'Yes' : 'No'],
                ]
            );

            $this->newLine();
            $this->info("💡 Test the bot by sending a message to @{$me->getUsername()}");
        } catch (\Exception $e) {
            $this->error("❌ Connection failed!");
            $this->error("Error: " . $e->getMessage());
            $this->newLine();
            $this->info("Check your TELEGRAM_BOT_TOKEN in .env file");
            return 1;
        }

        return 0;
    }
}
