<?php

namespace App\Services;

use App\Models\Recruitment;
use App\Models\Candidate;
use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramCandidateService
{
    // =========================================================================
    // 🌍 LOCALIZATION REPOSITORY
    // =========================================================================
    protected function getText($key, $lang = 'en', $params = [])
    {
        $lines = [
            'select_lang' => "Assalomu Alaykum! \n\n🇺🇿 Iltimos, tilni tanlang.\n🇬🇧 Please select a language.\n🇷🇺 Пожалуйста, выберите язык.",
            'no_companies' => [
                'uz' => "Hozircha faol kompaniyalar yo'q.",
                'en' => "No active companies found.",
                'ru' => "Активных компаний не найдено."
            ],
            'select_company' => [
                'uz' => "Qaysi kompaniya vakansiyalarini ko'rmoqchisiz?",
                'en' => "Please select a company to view jobs:",
                'ru' => "Выберите компанию для просмотра вакансий:"
            ],
            'no_jobs_company' => [
                'uz' => "Bu kompaniyada hozircha vakansiyalar yo'q.",
                'en' => "No open positions at this company.",
                'ru' => "В этой компании пока нет вакансий."
            ],
            'jobs_header' => [
                'uz' => "🏢 **:company** dagi mavjud vakansiyalar:",
                'en' => "🏢 Open positions at **:company**:",
                'ru' => "🏢 Вакансии в **:company**:"
            ],
            'job_not_found' => [
                'uz' => "🚫 Vakansiya topilmadi yoki yopilgan.",
                'en' => "🚫 Vacancy not found or closed.",
                'ru' => "🚫 Вакансия не найдена или закрыта."
            ],
            'job_card_labels' => [
                'uz' => ['pos' => 'Lavozim', 'sal' => 'Maosh', 'loc' => 'Manzil', 'desc' => 'Tavsif', 'btn' => '✅ Ariza Topshirish'],
                'en' => ['pos' => 'Position', 'sal' => 'Salary', 'loc' => 'Location', 'desc' => 'Description', 'btn' => '✅ Apply Now'],
                'ru' => ['pos' => 'Должность', 'sal' => 'Зарплата', 'loc' => 'Адрес', 'desc' => 'Описание', 'btn' => '✅ Подать Заявку'],
            ],
            'welcome_apply' => [
                'uz' => "👍 Ajoyib! Keling, arizani to'ldiramiz.\n\n1️⃣ Iltimos, **Ismingizni** kiriting:",
                'en' => "👍 Great! Let's get started.\n\n1️⃣ Please enter your **First Name**:",
                'ru' => "👍 Отлично! Давайте начнем.\n\n1️⃣ Введите ваше **Имя**:"
            ],
            'ask_lastname' => [
                'uz' => "Yaxshi. Endi **Familiyangizni** kiriting:",
                'en' => "Good. Now enter your **Last Name**:",
                'ru' => "Хорошо. Теперь введите вашу **Фамилию**:"
            ],
            'ask_email' => [
                'uz' => "Rahmat. **Email** manzilingizni kiriting:",
                'en' => "Thanks. Please enter your **Email Address**:",
                'ru' => "Спасибо. Введите ваш **Email**:"
            ],
            'invalid_email' => [
                'uz' => "⚠️ Iltimos, to'g'ri email kiriting.",
                'en' => "⚠️ Please enter a valid email address.",
                'ru' => "⚠️ Пожалуйста, введите корректный email."
            ],
            'ask_phone' => [
                'uz' => "Qabul qilindi. Iltimos, **Telefon raqamingizni** yuborish uchun pastdagi tugmani bosing:",
                'en' => "Got it. Please click the button below to share your **Phone Number**:",
                'ru' => "Принято. Нажмите кнопку ниже, чтобы отправить **Номер телефона**:"
            ],
            'btn_phone' => [
                'uz' => "📱 Raqamimni yuborish",
                'en' => "📱 Share My Contact",
                'ru' => "📱 Отправить контакт"
            ],
            'ask_resume' => [
                'uz' => "Deyarli tugadi! Iltimos, **Resume/CV** (PDF/Word) faylini yuklang.",
                'en' => "Almost done! Please upload your **Resume/CV** (PDF or DOCX).",
                'ru' => "Почти готово! Загрузите ваше **Резюме** (PDF или DOCX)."
            ],
            'ask_file_error' => [
                'uz' => "⚠️ Iltimos, fayl yuklang (Rasm yoki matn emas).",
                'en' => "⚠️ Please upload a document file.",
                'ru' => "⚠️ Пожалуйста, загрузите файл документа."
            ],
            'ask_cover' => [
                'uz' => "Fayl qabul qilindi. Qisqacha **Xat (Cover Letter)** yozing yoki **O'tkazib yuborish** tugmasini bosing:",
                'en' => "File received. Finally, write a short **Cover Letter** or click **Skip**:",
                'ru' => "Файл получен. Напишите краткое **Сопроводительное письмо** или нажмите **Пропустить**:"
            ],
            'btn_skip' => [
                'uz' => "⏭ O'tkazib yuborish",
                'en' => "⏭ Skip This Step",
                'ru' => "⏭ Пропустить"
            ],
            'success' => [
                'uz' => "✅ **Ariza qabul qilindi!**\n\nRahmat. Tez orada HR menejerlarimiz siz bilan bog'lanishadi.",
                'en' => "✅ **Application Received!**\n\nThank you. Our HR team will review your application soon.",
                'ru' => "✅ **Заявка принята!**\n\nСпасибо. Наша HR команда скоро свяжется с вами."
            ]
        ];

        $text = $lines[$key][$lang] ?? ($lines[$key]['en'] ?? $lines[$key]);

        foreach ($params as $k => $v) {
            $text = str_replace(":$k", $v, $text);
        }

        return $text;
    }

    // =========================================================================
    // 🟢 PHASE 1: ENTRY & ROUTING
    // =========================================================================

    /**
     * Step 1: Handle /start command. Detect intent, then ask Language.
     */
    public function handleStartCommand($chatId, $text)
    {
        Cache::forget("candidate_session_{$chatId}");
        Cache::forget("user_intention_{$chatId}");

        $intention = ['type' => 'general'];

        // 1. Specific Job Link (e.g. /start apply_5)
        if (Str::startsWith($text, '/start apply_')) {
            $intention['type'] = 'job';
            $intention['id'] = Str::after($text, 'apply_');
        }
        // 2. Company Page Link (e.g. /start c_1)
        elseif (Str::startsWith($text, '/start c_')) {
            $intention['type'] = 'company';
            $intention['id'] = Str::after($text, 'c_');
        }

        Cache::put("user_intention_{$chatId}", $intention, now()->addMinutes(15));

        // Ask Language
        $keyboard = Keyboard::make()->inline()->row([
            Keyboard::inlineButton(['text' => "🇺🇿 O'zbekcha", 'callback_data' => 'lang_uz']),
            Keyboard::inlineButton(['text' => "🇬🇧 English", 'callback_data' => 'lang_en']),
            Keyboard::inlineButton(['text' => "🇷🇺 Русский", 'callback_data' => 'lang_ru']),
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->getText('select_lang'),
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Step 2: Handle Language Selection & Route User
     */
    public function handleLanguageSelection($chatId, $langCode)
    {
        Cache::put("user_lang_{$chatId}", $langCode, now()->addDay());
        $intention = Cache::get("user_intention_{$chatId}");

        // ROUTING LOGIC
        if ($intention && $intention['type'] === 'job') {
            return $this->showJobPreview($chatId, $intention['id'], $langCode);
        }

        if ($intention && $intention['type'] === 'company') {
            return $this->listJobsForCompany($chatId, $intention['id'], $langCode);
        }

        // Default: List All Companies
        return $this->listAllCompanies($chatId, $langCode);
    }

    // =========================================================================
    // 📋 PHASE 2: LISTINGS (Companies & Jobs)
    // =========================================================================

    /**
     * Show list of all active companies (Multi-Tenant aware)
     */
    public function listAllCompanies($chatId, $lang)
    {
        $companies = Company::withoutGlobalScopes()
            ->where('is_active', true) // Assuming you have this flag
            ->take(10)
            ->get();

        if ($companies->isEmpty()) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => $this->getText('no_companies', $lang)]);
            return;
        }

        $keyboard = Keyboard::make()->inline();
        foreach ($companies as $company) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "🏢 " . $company->name,
                    'callback_data' => "select_company_{$company->id}"
                ])
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->getText('select_company', $lang),
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Show Jobs for a specific Company
     */
    public function listJobsForCompany($chatId, $companyId, $lang)
    {
        $company = Company::withoutGlobalScopes()->find($companyId);
        if (!$company) {
            return $this->listAllCompanies($chatId, $lang); // Fallback
        }

        $jobs = Recruitment::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'published') // Ensure this matches DB
            ->latest()
            ->get();

        if ($jobs->isEmpty()) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => $this->getText('no_jobs_company', $lang)]);
            return;
        }

        $keyboard = Keyboard::make()->inline();
        foreach ($jobs as $job) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "💼 " . $job->title,
                    'callback_data' => "select_job_{$job->id}"
                ])
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->getText('jobs_header', $lang, ['company' => $company->name]),
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Show the "Job Card" with Details + Apply Button
     */
    public function showJobPreview($chatId, $jobId, $lang)
    {
        $job = Recruitment::withoutGlobalScopes()->with('company')->find($jobId);

        if (!$job || $job->status !== 'published') {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => $this->getText('job_not_found', $lang)]);
            return;
        }

        $labels = $this->getText('job_card_labels', $lang);
        $salary = $job->salary_range ?? 'N/A';
        $location = $job->location ?? 'Tashkent';

        $msg = "🏢 **{$job->company->name}**\n\n";
        $msg .= "💼 **{$labels['pos']}:** {$job->title}\n";
        $msg .= "💰 **{$labels['sal']}:** {$salary}\n";
        $msg .= "📍 **{$labels['loc']}:** {$location}\n\n";
        $msg .= "📝 **{$labels['desc']}:**\n" . Str::limit($job->description, 300);

        // THE APPLY BUTTON
        $keyboard = Keyboard::make()->inline()->row([
            Keyboard::inlineButton([
                'text' => $labels['btn'],
                'callback_data' => "start_form_{$job->id}"
            ])
        ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    // =========================================================================
    // 📝 PHASE 3: APPLICATION WIZARD (State Machine)
    // =========================================================================

    /**
     * Initialize the Application Session
     */
    public function startApplicationForm($chatId, $jobId)
    {
        $job = Recruitment::withoutGlobalScopes()->find($jobId);
        $lang = Cache::get("user_lang_{$chatId}", 'en');

        // Init Session
        $sessionData = [
            'step' => 1,
            'recruitment_id' => $job->id,
            'company_id' => $job->company_id,
            'lang' => $lang,
            'data' => []
        ];

        Cache::put("candidate_session_{$chatId}", $sessionData, now()->addMinutes(30));

        return $this->getText('welcome_apply', $lang);
    }

    /**
     * Main Loop to handle User Input
     */
    public function handleConversation($messageArray, $chatId)
    {
        $session = Cache::get("candidate_session_{$chatId}");

        if (!$session) {
            // Session expired or invalid -> Restart
            $this->handleStartCommand($chatId, '/start');
            return null;
        }

        // Handle "Skip Cover Letter" Button
        if (isset($messageArray['is_callback']) && $messageArray['text'] === 'skip_cover') {
            $session['data']['cover_letter'] = null;
            $this->saveCandidateToDb($chatId, $session);
            Cache::forget("candidate_session_{$chatId}");
            return $this->getText('success', $session['lang']);
        }

        return $this->processStep($chatId, $messageArray, $session);
    }

    /**
     * Step-by-Step Logic
     */
    protected function processStep($chatId, $message, $session)
    {
        $step = $session['step'];
        $text = $message['text'] ?? '';
        $contact = $message['contact'] ?? null; // Button contact
        $lang = $session['lang'];

        switch ($step) {
            case 1: // First Name -> Ask Last Name
                $session['data']['first_name'] = $text;
                $session['step'] = 2;
                $response = $this->getText('ask_lastname', $lang);
                $keyboard = Keyboard::remove();
                break;

            case 2: // Last Name -> Ask Email
                $session['data']['last_name'] = $text;
                $session['step'] = 3;
                $response = $this->getText('ask_email', $lang);
                break;

            case 3: // Email -> Ask Phone (BUTTON)
                if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    return $this->getText('invalid_email', $lang);
                }
                $session['data']['email'] = $text;
                $session['step'] = 4;

                $response = $this->getText('ask_phone', $lang);

                // Reply Keyboard (Bottom of screen)
                $keyboard = Keyboard::make()
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->row([
                        Keyboard::button([
                            'text' => $this->getText('btn_phone', $lang),
                            'request_contact' => true // ✨ Magic Button
                        ])
                    ]);
                break;

            case 4: // Phone -> Ask Resume
                // Accept Text OR Button Contact
                $phone = $contact ? $contact['phone_number'] : $text;

                // Basic cleanup
                $session['data']['phone'] = $phone;
                $session['step'] = 5;

                $response = $this->getText('ask_resume', $lang);
                $keyboard = Keyboard::remove(); // Remove Phone button
                break;

            case 5: // Resume -> Ask Cover Letter (SKIP BUTTON)
                if (!isset($message['document'])) {
                    return $this->getText('ask_file_error', $lang);
                }

                $path = $this->downloadTelegramFile($message['document']['file_id']);
                $session['data']['resume_path'] = $path;
                $session['step'] = 6;

                $response = $this->getText('ask_cover', $lang);

                // Inline Keyboard (Inside message)
                $keyboard = Keyboard::make()->inline()->row([
                    Keyboard::inlineButton([
                        'text' => $this->getText('btn_skip', $lang),
                        'callback_data' => 'skip_cover'
                    ])
                ]);
                break;

            case 6: // Cover Letter -> SAVE
                $session['data']['cover_letter'] = $text;
                $this->saveCandidateToDb($chatId, $session);
                Cache::forget("candidate_session_{$chatId}");

                $response = $this->getText('success', $lang);
                $keyboard = Keyboard::remove();
                break;

            default:
                return "Error";
        }

        // Update Session
        if ($step < 6) {
            Cache::put("candidate_session_{$chatId}", $session, now()->addMinutes(30));
        }

        // Send Reply
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $response,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard ?? null
        ]);

        return null;
    }

    // =========================================================================
    // 💾 DB & FILES
    // =========================================================================

    protected function saveCandidateToDb($chatId, $session)
    {
        $candidate = Candidate::create([
            'company_id' => $session['company_id'],
            'recruitment_id' => $session['recruitment_id'],
            'telegram_chat_id' => $chatId,
            'first_name' => $session['data']['first_name'],
            'last_name' => $session['data']['last_name'],
            'email' => $session['data']['email'],
            'phone' => $session['data']['phone'],
            'resume_path' => $session['data']['resume_path'],
            'cover_letter' => $session['data']['cover_letter'],
            'source' => 'telegram',
            'status' => 'pending'
        ]);

        app(\App\Services\TelegramAdminService::class)->notifyAdminsOfNewCandidate($candidate);
    }

    protected function downloadTelegramFile($fileId)
    {
        $token = config('services.telegram.bot_token');

        // 1. Get Path
        $response = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");

        if (!$response->successful()) return null;

        $filePath = $response->json()['result']['file_path'];

        // 2. Download Content
        $fileContent = Http::get("https://api.telegram.org/file/bot{$token}/{$filePath}")->body();

        // 3. Save
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $localPath = 'resumes/' . Str::random(40) . '.' . $ext;

        Storage::put($localPath, $fileContent);

        return $localPath;
    }
}
