<?php

namespace App\Jobs;

use App\Services\TelegramCandidateService;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class ProcessTelegramWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $updateData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $updateData)
    {
        $this->updateData = $updateData;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramCandidateService $candidateService)
    {
        // We reconstruct the Update object manually or just use the array data
        // For simplicity, let's assume we pass the raw data needed.

        $chatId = $this->updateData['chat_id'];
        $text = $this->updateData['text'] ?? '';
        $data = $this->updateData['callback_data'] ?? null;
        $messageArray = $this->updateData['message_array'] ?? [];

        // ---------------------------------------------------------
        // COPY YOUR LOGIC FROM THE CONTROLLER HERE
        // ---------------------------------------------------------

        // 1. Handle Callbacks
        if ($data) {
            if (Str::startsWith($data, 'lang_')) {
                $candidateService->handleLanguageSelection($chatId, Str::after($data, 'lang_'));
            } elseif (Str::startsWith($data, 'select_job_')) {
                $lang = Cache::get("user_lang_{$chatId}", 'en');
                $candidateService->showJobPreview($chatId, Str::after($data, 'select_job_'), $lang);
            } elseif (Str::startsWith($data, 'start_form_')) {
                $resp = $candidateService->startApplicationForm($chatId, Str::after($data, 'start_form_'));
                Telegram::sendMessage(['chat_id' => $chatId, 'text' => $resp, 'parse_mode' => 'Markdown']);
            } elseif ($data === 'skip_cover') {
                $candidateService->handleConversation(['text' => 'skip_cover', 'is_callback' => true], $chatId);
            }
            // ... Add Company selection logic here too
            return;
        }

        // 2. Handle Text / Deep Links
        if (Str::startsWith($text, '/start')) {
            $candidateService->handleStartCommand($chatId, $text);
            return;
        }

        if (Cache::has("candidate_session_{$chatId}")) {
            $candidateService->handleConversation($messageArray, $chatId);
            return;
        }

        // 3. Employee Logic (Simplified for brevity)
        $employee = Employee::where('telegram_chat_id', $chatId)->first();
        if ($employee) {
            // ... Call your Employee Logic here ...
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Welcome back employee."]);
            return;
        }

        // 4. Fallback
        $candidateService->handleStartCommand($chatId, '/start');
    }
}
