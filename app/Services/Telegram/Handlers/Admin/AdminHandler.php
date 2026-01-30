<?php

namespace App\Services\Telegram\Handlers\Admin;

use App\Models\Company;
use App\Services\Telegram\Helpers\TelegramKeyboardBuilder;
use App\Services\Telegram\Helpers\TelegramAuthChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class AdminHandler
{
    protected HRHandler $hrHandler;
    protected TelegramAuthChecker $authChecker;

    public function __construct(HRHandler $hrHandler, TelegramAuthChecker $authChecker)
    {
        $this->hrHandler = $hrHandler;
        $this->authChecker = $authChecker;
    }

    /**
     * Send main menu for super admin
     */
    public function sendMainMenu(int $chatId, array $auth): void
    {
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId) {
            // No company selected - show selector
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        // Company selected - show admin menu with company name
        $company = Company::withoutGlobalScopes()->find($activeCompanyId);

        if (!$company) {
            // Company doesn't exist anymore
            Cache::forget("admin_active_company_{$chatId}");
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        $employee = $auth['employee'];
        $greeting = "🔐 *Super Admin Dashboard*\n\n";
        $greeting .= "Welcome, {$employee->first_name}!\n";
        $greeting .= "Active Company: *{$company->name}*";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $greeting,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::adminMainMenu($company->name)
        ]);
    }

    /**
     * Handle text button presses
     */
    public function handleText(string $text, int $chatId, array $auth): void
    {
        // Handle company selector button
        if (Str::startsWith($text, '🏢 Company:') || Str::startsWith($text, '🏢 Select Company')) {
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        // Handle global stats
        if ($text === '📊 Global Stats') {
            $this->showGlobalStats($chatId, $auth);
            return;
        }

        // Handle all companies list
        if ($text === '🏢 All Companies') {
            $this->listAllCompanies($chatId, $auth);
            return;
        }

        // For all other buttons, check if company is selected
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Please select a company first.",
            ]);
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        // Create modified auth with active company
        $modifiedAuth = $auth;
        $modifiedAuth['company_id'] = $activeCompanyId;

        // Delegate to HR handler
        $this->hrHandler->handleText($text, $chatId, $modifiedAuth);
    }

    /**
     * Handle callback queries
     */
    public function handleCallback($callback, int $chatId, array $auth): void
    {
        $data = $callback->getData();

        // Handle company selection
        if (Str::startsWith($data, 'admin_select_company_')) {
            $companyId = (int) Str::after($data, 'admin_select_company_');
            $this->setActiveCompany($chatId, $auth, $companyId);
            return;
        }

        // For all other callbacks, check if company is selected and delegate
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId && !Str::startsWith($data, 'admin_')) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Please select a company first.",
            ]);
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        // Create modified auth with active company
        $modifiedAuth = $auth;
        $modifiedAuth['company_id'] = $activeCompanyId;

        // Delegate to HR handler
        $this->hrHandler->handleCallback($callback, $chatId, $modifiedAuth);
    }

    /**
     * Show company selector
     */
    protected function showCompanySelector(int $chatId, array $auth): void
    {
        $companies = Company::withoutGlobalScopes()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($companies->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ No active companies found in the system.",
            ]);
            return;
        }

        $keyboard = Keyboard::make()->inline();

        foreach ($companies as $company) {
            // Count employees for quick info
            $employeeCount = $company->employees()->count();

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => "🏢 {$company->name} ({$employeeCount} employees)",
                    'callback_data' => "admin_select_company_{$company->id}"
                ])
            ]);
        }

        $message = "🏢 *Select Company to Manage*\n\n";
        $message .= "Choose a company to access its HR features:";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Set active company for admin
     */
    protected function setActiveCompany(int $chatId, array $auth, int $companyId): void
    {
        $company = Company::withoutGlobalScopes()->find($companyId);

        if (!$company) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Company not found.",
            ]);
            return;
        }

        // Store in cache for 24 hours
        $this->authChecker->setActiveCompanyForAdmin($chatId, $companyId);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ *Switched to: {$company->name}*\n\nYou can now manage this company.",
            'parse_mode' => 'Markdown',
        ]);

        // Show admin menu
        $this->sendMainMenu($chatId, $auth);
    }

    /**
     * Show global statistics across all companies
     */
    protected function showGlobalStats(int $chatId, array $auth): void
    {
        $totalCompanies = Company::withoutGlobalScopes()->where('is_active', true)->count();

        $totalEmployees = \App\Models\Employee::withoutGlobalScopes()->count();

        $totalOpenJobs = \App\Models\Recruitment::withoutGlobalScopes()
            ->where('status', 'published')
            ->count();

        $totalCandidates = \App\Models\Candidate::withoutGlobalScopes()
            ->where('status', 'pending')
            ->count();

        // Today's attendance across all companies
        $today = now()->toDateString();
        $todayPresent = \App\Models\Attendance::withoutGlobalScopes()
            ->where('date', $today)
            ->whereNotNull('check_in_time')
            ->count();

        $message = "🌐 *Global Statistics*\n";
        $message .= "_All Companies Combined_\n\n";
        $message .= "🏢 *Companies:* {$totalCompanies}\n";
        $message .= "👥 *Total Employees:* {$totalEmployees}\n";
        $message .= "✅ *Present Today:* {$todayPresent}\n\n";
        $message .= "💼 *Recruitment:*\n";
        $message .= "• Open Jobs: {$totalOpenJobs}\n";
        $message .= "• Pending Candidates: {$totalCandidates}";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::adminMainMenu()
        ]);
    }

    /**
     * List all companies with details
     */
    protected function listAllCompanies(int $chatId, array $auth): void
    {
        $companies = Company::withoutGlobalScopes()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($companies->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "No active companies found.",
            ]);
            return;
        }

        $message = "🏢 *All Companies*\n\n";

        foreach ($companies as $company) {
            $employeeCount = $company->employees()->count();
            $openJobs = \App\Models\Recruitment::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('status', 'published')
                ->count();

            $message .= "*{$company->name}*\n";
            $message .= "• Employees: {$employeeCount}\n";
            $message .= "• Open Jobs: {$openJobs}\n";
            $message .= "• Subdomain: `{$company->subdomain}`\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::adminMainMenu()
        ]);
    }

    /**
     * Continue wizards (delegate to HR handler with active company)
     */
    public function continueJobPostingWizard($update, int $chatId, array $auth): void
    {
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId) {
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        $modifiedAuth = $auth;
        $modifiedAuth['company_id'] = $activeCompanyId;

        $this->hrHandler->continueJobPostingWizard($update, $chatId, $modifiedAuth);
    }

    public function continueAddEmployeeWizard($update, int $chatId, array $auth): void
    {
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId) {
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        $modifiedAuth = $auth;
        $modifiedAuth['company_id'] = $activeCompanyId;

        $this->hrHandler->continueAddEmployeeWizard($update, $chatId, $modifiedAuth);
    }

    public function continueAnnouncementWizard($update, int $chatId, array $auth): void
    {
        $activeCompanyId = $this->authChecker->getActiveCompanyForAdmin($chatId);

        if (!$activeCompanyId) {
            $this->showCompanySelector($chatId, $auth);
            return;
        }

        $modifiedAuth = $auth;
        $modifiedAuth['company_id'] = $activeCompanyId;

        $this->hrHandler->continueAnnouncementWizard($update, $chatId, $modifiedAuth);
    }
}
