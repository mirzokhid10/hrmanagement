<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Handlers\PublicHandler;
use App\Services\Telegram\Handlers\EmployeeHandler;
use App\Services\Telegram\Handlers\Admin\HRHandler;
use App\Services\Telegram\Handlers\Admin\AdminHandler;
use App\Services\Telegram\Helpers\TelegramAuthChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramRouterService
{
    protected TelegramAuthChecker $authChecker;
    protected PublicHandler $publicHandler;
    protected EmployeeHandler $employeeHandler;
    protected HRHandler $hrHandler;
    protected AdminHandler $adminHandler;

    public function __construct(
        TelegramAuthChecker $authChecker,
        PublicHandler $publicHandler,
        EmployeeHandler $employeeHandler,
        HRHandler $hrHandler,
        AdminHandler $adminHandler
    ) {
        $this->authChecker = $authChecker;
        $this->publicHandler = $publicHandler;
        $this->employeeHandler = $employeeHandler;
        $this->hrHandler = $hrHandler;
        $this->adminHandler = $adminHandler;
    }

    /**
     * Main routing logic
     */
    public function route($update, int $chatId): void
    {
        // Get user authentication context
        $auth = $this->authChecker->getUserType($chatId);

        // Priority 1: Check for active wizard sessions (any user type)
        if ($this->hasActiveWizard($chatId)) {
            $this->continueWizard($update, $chatId, $auth);
            return;
        }

        // Priority 2: Handle callbacks (button clicks)
        if ($update->has('callback_query')) {
            $this->handleCallback($update->getCallbackQuery(), $chatId, $auth);
            return;
        }

        // Priority 3: Handle text messages
        if ($update->has('message')) {
            $this->handleMessage($update->getMessage(), $chatId, $auth);
            return;
        }
    }

    /**
     * Handle callback queries (inline button clicks)
     */
    protected function handleCallback($callback, int $chatId, array $auth): void
    {
        $data = $callback->getData();

        // Always answer callback to remove loading state
        Telegram::answerCallbackQuery(['callback_query_id' => $callback->getId()]);

        try {
            Telegram::answerCallbackQuery(['callback_query_id' => $callback->getId()]);
        } catch (\Exception $e) {
            // Universal cancel button
            if ($data === 'cancel_wizard') {
                $this->cancelAllWizards($chatId);
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❌ Operation cancelled.'
                ]);
                $this->sendMainMenu($chatId, $auth);
                return;
            }
        }

        // Route to appropriate handler based on callback prefix
        if (Str::startsWith($data, 'admin_') && $auth['type'] === 'admin') {
            $this->adminHandler->handleCallback($callback, $chatId, $auth);
        } elseif (Str::startsWith($data, 'hr_') && in_array($auth['type'], ['hr', 'admin'])) {
            $this->hrHandler->handleCallback($callback, $chatId, $auth);
        } elseif (Str::startsWith($data, 'emp_') && in_array($auth['type'], ['employee', 'hr', 'admin'])) {
            $this->employeeHandler->handleCallback($callback, $chatId, $auth);
        } elseif (Str::startsWith($data, ['public_', 'lang_', 'select_', 'start_', 'skip_'])) {
            $this->publicHandler->handleCallback($callback, $chatId, $auth);
        } else {
            // Try all handlers (for shared callbacks)
            $this->publicHandler->handleCallback($callback, $chatId, $auth);
        }
    }

    /**
     * Handle text messages and button presses
     */
    protected function handleMessage($message, int $chatId, array $auth): void
    {
        $text = $message->getText() ?? '';

        // Handle /start with deep links first (for job applications)
        if (Str::startsWith($text, '/start ') && strlen($text) > 7) {
            $this->publicHandler->handleStartCommand($chatId, $text, $auth);
            return;
        }

        // Handle contact sharing (registration)
        if ($message->has('contact')) {
            $this->publicHandler->handleContactRegistration($message->getContact(), $chatId);
            return;
        }

        // Handle location sharing (attendance)
        if ($message->has('location')) {
            if (in_array($auth['type'], ['employee', 'hr', 'admin'])) {
                $this->employeeHandler->handleLocation($message->getLocation(), $chatId, $auth);
            }
            return;
        }

        // Handle document uploads (resume in application)
        if ($message->has('document')) {
            $this->publicHandler->handleDocument($message->getDocument(), $chatId);
            return;
        }

        // Route custom keyboard button presses by user type
        match ($auth['type']) {
            'admin' => $this->adminHandler->handleText($text, $chatId, $auth),
            'hr' => $this->hrHandler->handleText($text, $chatId, $auth),
            'employee' => $this->employeeHandler->handleText($text, $chatId, $auth),
            'public' => $this->publicHandler->handleText($text, $chatId, $auth),
        };
    }

    /**
     * Continue active wizard session
     */
    protected function continueWizard($update, int $chatId, array $auth): void
    {
        // Check which wizard is active
        if (Cache::has("wizard_job_posting_{$chatId}")) {
            if ($auth['type'] === 'admin') {
                $this->adminHandler->continueJobPostingWizard($update, $chatId, $auth);
            } else {
                $this->hrHandler->continueJobPostingWizard($update, $chatId, $auth);
            }
        } elseif (Cache::has("wizard_application_{$chatId}")) {
            $this->publicHandler->continueApplicationWizard($update, $chatId, $auth);
        } elseif (Cache::has("wizard_add_employee_{$chatId}")) {
            if ($auth['type'] === 'admin') {
                $this->adminHandler->continueAddEmployeeWizard($update, $chatId, $auth);
            } else {
                $this->hrHandler->continueAddEmployeeWizard($update, $chatId, $auth);
            }
        } elseif (Cache::has("wizard_announcement_{$chatId}")) {
            if ($auth['type'] === 'admin') {
                $this->adminHandler->continueAnnouncementWizard($update, $chatId, $auth);
            } else {
                $this->hrHandler->continueAnnouncementWizard($update, $chatId, $auth);
            }
        }
    }

    /**
     * Check if user has any active wizard
     */
    protected function hasActiveWizard(int $chatId): bool
    {
        return Cache::has("wizard_job_posting_{$chatId}")
            || Cache::has("wizard_application_{$chatId}")
            || Cache::has("wizard_add_employee_{$chatId}")
            || Cache::has("wizard_announcement_{$chatId}");
    }

    /**
     * Cancel all active wizards for user
     */
    protected function cancelAllWizards(int $chatId): void
    {
        Cache::forget("wizard_job_posting_{$chatId}");
        Cache::forget("wizard_application_{$chatId}");
        Cache::forget("wizard_add_employee_{$chatId}");
        Cache::forget("wizard_announcement_{$chatId}");
        Cache::forget("attendance_action_{$chatId}");
    }

    /**
     * Send appropriate main menu based on user type
     */
    protected function sendMainMenu(int $chatId, array $auth): void
    {
        match ($auth['type']) {
            'admin' => $this->adminHandler->sendMainMenu($chatId, $auth),
            'hr' => $this->hrHandler->sendMainMenu($chatId, $auth),
            'employee' => $this->employeeHandler->sendMainMenu($chatId, $auth),
            'public' => $this->publicHandler->sendWelcome($chatId),
        };
    }
}
