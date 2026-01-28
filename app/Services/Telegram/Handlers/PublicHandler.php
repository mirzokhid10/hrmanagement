<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Recruitment;
use App\Models\Candidate;
use App\Services\Telegram\Helpers\TelegramKeyboardBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class PublicHandler
{
    /**
     * Send welcome message for non-registered users
     */
    public function sendWelcome(int $chatId): void
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 *Welcome!*\n\nAre you:\n" .
                "• 💼 Looking for a job? Browse our openings!\n" .
                "• 👤 An employee? Click the button below to register.",
            'parse_mode' => 'Markdown',
            'reply_markup' => Keyboard::make()->inline()
                ->row([
                    Keyboard::inlineButton(['text' => '💼 Browse Jobs', 'callback_data' => 'public_browse_jobs']),
                ])
                ->row([
                    Keyboard::inlineButton(['text' => '👤 Employee Login', 'callback_data' => 'public_employee_login']),
                ])
        ]);
    }

    /**
     * Handle /start command with deep links
     */
    public function handleStartCommand(int $chatId, string $text, array $auth): void
    {
        // If already registered, don't show welcome
        if ($auth['type'] !== 'public') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "You're already registered! Use the menu below.",
            ]);
            return;
        }

        Cache::forget("candidate_session_{$chatId}");

        $intention = ['type' => 'general'];

        // Parse deep link: /start apply_5
        if (Str::startsWith($text, '/start apply_')) {
            $intention['type'] = 'job';
            $intention['id'] = Str::after($text, 'apply_');
        }
        // Parse company page: /start c_1
        elseif (Str::startsWith($text, '/start c_')) {
            $intention['type'] = 'company';
            $intention['id'] = Str::after($text, 'c_');
        }

        Cache::put("user_intention_{$chatId}", $intention, now()->addMinutes(15));

        // Always ask language first
        $this->askLanguage($chatId);
    }

    /**
     * Ask user to select language
     */
    protected function askLanguage(int $chatId): void
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Assalomu Alaykum! 🇺🇿\n\nPlease select a language.\n\nПожалуйста, выберите язык.",
            'reply_markup' => TelegramKeyboardBuilder::languageSelection()
        ]);
    }

    /**
     * Handle callback queries
     */
    public function handleCallback($callback, int $chatId, array $auth): void
    {
        $data = $callback->getData();

        // Language selection
        if (Str::startsWith($data, 'lang_')) {
            $lang = Str::after($data, 'lang_');
            Cache::put("user_lang_{$chatId}", $lang, now()->addDay());

            $intention = Cache::get("user_intention_{$chatId}");

            if ($intention && $intention['type'] === 'job') {
                $this->showJobPreview($chatId, $intention['id'], $lang);
            } elseif ($intention && $intention['type'] === 'company') {
                $this->listJobsForCompany($chatId, $intention['id'], $lang);
            } else {
                $this->listAllCompanies($chatId, $lang);
            }
            return;
        }

        // Company selection
        if (Str::startsWith($data, 'select_company_')) {
            $companyId = Str::after($data, 'select_company_');
            $lang = Cache::get("user_lang_{$chatId}", 'en');
            $this->listJobsForCompany($chatId, $companyId, $lang);
            return;
        }

        // Job selection
        if (Str::startsWith($data, 'select_job_')) {
            $jobId = Str::after($data, 'select_job_');
            $lang = Cache::get("user_lang_{$chatId}", 'en');
            $this->showJobPreview($chatId, $jobId, $lang);
            return;
        }

        // Start application
        if (Str::startsWith($data, 'start_form_')) {
            $jobId = Str::after($data, 'start_form_');
            $lang = Cache::get("user_lang_{$chatId}", 'en');
            $this->startApplicationWizard($chatId, $jobId, $lang);
            return;
        }

        // Skip cover letter
        if ($data === 'skip_cover') {
            $this->handleApplicationStep($chatId, ['skip' => true]);
            return;
        }

        // Browse jobs button
        if ($data === 'public_browse_jobs') {
            $lang = Cache::get("user_lang_{$chatId}", 'en');
            $this->listAllCompanies($chatId, $lang);
            return;
        }

        // Employee login button
        if ($data === 'public_employee_login') {
            $this->askForContact($chatId);
            return;
        }
    }

    /**
     * Handle text messages (not used much in button-only interface)
     */
    public function handleText(string $text, int $chatId, array $auth): void
    {
        // If in application wizard, handle text
        if (Cache::has("wizard_application_{$chatId}")) {
            $this->handleApplicationStep($chatId, ['text' => $text]);
            return;
        }

        // Otherwise show welcome
        $this->sendWelcome($chatId);
    }

    /**
     * Handle contact sharing (employee registration)
     */
    public function handleContactRegistration($contact, int $chatId): void
    {
        if ($contact->getUserId() !== $chatId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "🚫 Please send your own contact information."
            ]);
            return;
        }

        $phone = preg_replace('/[^0-9]/', '', $contact->getPhoneNumber());

        // Find employee by phone (last 9 digits match)
        $employee = Employee::withoutGlobalScopes()
            ->whereRaw("REPLACE(phone_number, '+', '') LIKE '%" . substr($phone, -9) . "'")
            ->first();

        if (!$employee) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "🚫 *Not Found*\n\nYour phone number is not in our system. Please contact your HR manager to be added first.",
                'parse_mode' => 'Markdown',
                'reply_markup' => TelegramKeyboardBuilder::removeKeyboard()
            ]);
            return;
        }

        // Link telegram account
        $employee->update([
            'telegram_chat_id' => $chatId,
            'telegram_username' => $contact->getFirstName()
        ]);

        // Clear auth cache
        app(\App\Services\Telegram\TelegramAuthChecker::class)->clearCache($chatId);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🎉 *Welcome, {$employee->first_name}!*\n\nYour account has been connected successfully.",
            'parse_mode' => 'Markdown',
        ]);

        // Show appropriate menu based on role
        $auth = app(\App\Services\Telegram\TelegramAuthChecker::class)->getUserType($chatId);

        if ($auth['type'] === 'hr' || $auth['type'] === 'admin') {
            app(\App\Services\Telegram\Handlers\HRHandler::class)->sendMainMenu($chatId, $auth);
        } else {
            app(\App\Services\Telegram\Handlers\EmployeeHandler::class)->sendMainMenu($chatId, $auth);
        }
    }

    /**
     * Ask user to share contact
     */
    protected function askForContact(int $chatId): void
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📱 *Employee Login*\n\nPlease share your phone number to verify your employment.",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::requestContact('📱 Share My Number')
        ]);
    }

    /**
     * Handle document uploads (resume)
     */
    public function handleDocument($document, int $chatId): void
    {
        if (Cache::has("wizard_application_{$chatId}")) {
            $this->handleApplicationStep($chatId, ['document' => $document]);
        }
    }

    /**
     * List all active companies
     */
    protected function listAllCompanies(int $chatId, string $lang): void
    {
        $companies = Company::withoutGlobalScopes()
            ->where('is_active', true)
            ->take(10)
            ->get();

        if ($companies->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "No companies with open positions right now."
            ]);
            return;
        }

        $buttons = $companies->map(function ($company) {
            return [
                'text' => "🏢 " . $company->name,
                'callback' => "select_company_{$company->id}"
            ];
        })->toArray();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🏢 *Select a Company*\n\nChoose a company to view their open positions:",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::inlineGrid($buttons, 1)
        ]);
    }

    /**
     * List jobs for specific company
     */
    protected function listJobsForCompany(int $chatId, int $companyId, string $lang): void
    {
        $company = Company::withoutGlobalScopes()->find($companyId);

        if (!$company) {
            $this->listAllCompanies($chatId, $lang);
            return;
        }

        $jobs = Recruitment::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'published')
            ->latest()
            ->get();

        if ($jobs->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "No open positions at {$company->name} right now."
            ]);
            return;
        }

        $buttons = $jobs->map(function ($job) {
            return [
                'text' => "💼 " . $job->title,
                'callback' => "select_job_{$job->id}"
            ];
        })->toArray();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🏢 *{$company->name}*\n\nSelect a position to view details:",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::inlineGrid($buttons, 1)
        ]);
    }

    /**
     * Show job details with apply button
     */
    protected function showJobPreview(int $chatId, int $jobId, string $lang): void
    {
        $job = Recruitment::withoutGlobalScopes()->with('company')->find($jobId);

        if (!$job || $job->status !== 'published') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "🚫 This job is no longer available."
            ]);
            return;
        }

        $salary = $job->salary_range ?? 'Negotiable';
        $location = $job->location ?? 'Tashkent';

        $message = "🏢 *{$job->company->name}*\n\n";
        $message .= "💼 *Position:* {$job->title}\n";
        $message .= "💰 *Salary:* {$salary}\n";
        $message .= "📍 *Location:* {$location}\n\n";
        $message .= "📝 *Description:*\n" . Str::limit($job->description, 300);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => Keyboard::make()->inline()->row([
                Keyboard::inlineButton([
                    'text' => '✅ Apply Now',
                    'callback_data' => "start_form_{$job->id}"
                ])
            ])
        ]);
    }

    /**
     * Start application wizard
     */
    protected function startApplicationWizard(int $chatId, int $jobId, string $lang): void
    {
        $job = Recruitment::withoutGlobalScopes()->find($jobId);

        if (!$job) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Job not found."]);
            return;
        }

        $wizard = [
            'step' => 'first_name',
            'job_id' => $job->id,
            'company_id' => $job->company_id,
            'lang' => $lang,
            'data' => []
        ];

        Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👍 *Great! Let's get started.*\n\n1️⃣ Please enter your *First Name*:",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::cancelButton()
        ]);
    }

    /**
     * Continue application wizard
     */
    public function continueApplicationWizard($update, int $chatId, array $auth): void
    {
        $wizard = Cache::get("wizard_application_{$chatId}");

        if (!$wizard) {
            $this->sendWelcome($chatId);
            return;
        }

        // Extract data from update
        $input = [];
        if ($update->has('message')) {
            $message = $update->getMessage();
            $input['text'] = $message->getText() ?? '';
            $input['contact'] = $message->has('contact') ? $message->getContact() : null;
            $input['document'] = $message->has('document') ? $message->getDocument() : null;
        } elseif ($update->has('callback_query')) {
            $input['callback'] = $update->getCallbackQuery()->getData();
        }

        $this->handleApplicationStep($chatId, $input, $wizard);
    }

    /**
     * Handle each step of application
     */
    protected function handleApplicationStep(int $chatId, array $input, ?array $wizard = null): void
    {
        if (!$wizard) {
            $wizard = Cache::get("wizard_application_{$chatId}");
        }

        if (!$wizard) return;

        $step = $wizard['step'];
        $text = $input['text'] ?? '';

        switch ($step) {
            case 'first_name':
                $wizard['data']['first_name'] = $text;
                $wizard['step'] = 'last_name';
                Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Good. Now enter your *Last Name*:",
                    'parse_mode' => 'Markdown'
                ]);
                break;

            case 'last_name':
                $wizard['data']['last_name'] = $text;
                $wizard['step'] = 'email';
                Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Thanks. Please enter your *Email Address*:",
                    'parse_mode' => 'Markdown'
                ]);
                break;

            case 'email':
                if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "⚠️ Please enter a valid email address."
                    ]);
                    return;
                }

                $wizard['data']['email'] = $text;
                $wizard['step'] = 'phone';
                Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Got it. Please share your *Phone Number*:",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => TelegramKeyboardBuilder::requestContact('📱 Share My Contact')
                ]);
                break;

            case 'phone':
                $phone = $input['contact'] ? $input['contact']->getPhoneNumber() : $text;
                $wizard['data']['phone'] = $phone;
                $wizard['step'] = 'resume';
                Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Almost done! Please upload your *Resume/CV* (PDF or DOCX):",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => TelegramKeyboardBuilder::removeKeyboard()
                ]);
                break;

            case 'resume':
                if (!isset($input['document'])) {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "⚠️ Please upload a document file (PDF or DOCX)."
                    ]);
                    return;
                }

                $path = $this->downloadTelegramFile($input['document']->getFileId());
                $wizard['data']['resume_path'] = $path;
                $wizard['step'] = 'cover_letter';
                Cache::put("wizard_application_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Resume received! Finally, write a short *Cover Letter* or click Skip:",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => TelegramKeyboardBuilder::skipButton('skip_cover')
                ]);
                break;

            case 'cover_letter':
                $coverLetter = isset($input['skip']) ? null : $text;
                $wizard['data']['cover_letter'] = $coverLetter;

                // Save to database
                $this->saveCandidate($wizard);

                Cache::forget("wizard_application_{$chatId}");

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ *Application Submitted!*\n\nThank you. Our HR team will review your application and contact you soon.",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => TelegramKeyboardBuilder::removeKeyboard()
                ]);
                break;
        }
    }

    /**
     * Save candidate to database
     */
    protected function saveCandidate(array $wizard): void
    {
        $candidate = Candidate::create([
            'company_id' => $wizard['company_id'],
            'recruitment_id' => $wizard['job_id'],
            'first_name' => $wizard['data']['first_name'],
            'last_name' => $wizard['data']['last_name'],
            'email' => $wizard['data']['email'],
            'phone' => $wizard['data']['phone'],
            'resume_path' => $wizard['data']['resume_path'],
            'cover_letter' => $wizard['data']['cover_letter'],
            'source' => 'telegram',
            'status' => 'pending'
        ]);

        // Notify HR managers (reuse existing job)
        dispatch(new \App\Jobs\NotifyHROfNewCandidate($candidate));
    }

    /**
     * Download file from Telegram servers
     */
    protected function downloadTelegramFile(string $fileId): string
    {
        $token = config('telegram.bot_token');

        $response = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to get file info');
        }

        $filePath = $response->json()['result']['file_path'];
        $fileContent = Http::get("https://api.telegram.org/file/bot{$token}/{$filePath}")->body();

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $localPath = 'resumes/' . Str::random(40) . '.' . $ext;

        Storage::put($localPath, $fileContent);

        return $localPath;
    }
}
