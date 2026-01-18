<?php

namespace App\Console\Commands\Hr;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Exceptions\TelegramSDKException;

class CheckBotStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:bot-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of the Telegram Webhook for the HRMS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking Telegram Bot Status...');

        try {
            // Get Webhook Info from Telegram API
            $response = Telegram::getWebhookInfo();

            // Get Me (Bot Profile) to confirm token is valid
            $me = Telegram::getMe();

            $this->newLine();
            $this->components->twoColumnDetail('Bot Name', $me->firstName . ' (@' . $me->username . ')');
            $this->components->twoColumnDetail('Bot ID', $me->id);
            $this->newLine();

            $headers = ['Metric', 'Value'];
            $data = [
                ['Webhook URL', $response->url ?: '❌ Not Set (Polling Mode)'],
                ['Pending Updates', $response->pendingUpdateCount],
                ['Max Connections', $response->maxConnections ?? 'N/A'],
                ['Last Error Date', $response->lastErrorDate ? date('Y-m-d H:i:s', $response->lastErrorDate) : 'None'],
                ['Last Error Message', $response->lastErrorMessage ?? 'None'],
            ];

            $this->table($headers, $data);

            if (empty($response->url)) {
                $this->warn('⚠️  Webhook is not set. The bot might be using Polling or is inactive.');
            } else {
                $this->info('✅ Webhook is active and healthy.');
            }

            return Command::SUCCESS;
        } catch (TelegramSDKException $e) {
            $this->error('❌ Telegram API Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}