<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramRouterService
{
    protected $candidateService;
    protected $adminService;
    protected $attendanceService;

    public function __construct(
        TelegramCandidateService $candidateService,
        TelegramAdminService $adminService,
        TelegramAttendanceService $attendanceService
    ) {
        $this->candidateService = $candidateService;
        $this->adminService = $adminService;
        $this->attendanceService = $attendanceService;
    }

    public function routeMessage($update, $chatId)
    {
        // 1. Find Employee (Global Search)
        $employee = Employee::withoutGlobalScopes()
            ->where('telegram_chat_id', $chatId)
            ->first();

        $isAdmin = $this->checkIfAdmin($employee);

        // 2. Handle Callbacks (Buttons)
        if ($update->has('callback_query')) {
            $this->handleCallback($update->getCallbackQuery(), $chatId, $employee, $isAdmin);
            return;
        }

        // 3. Handle Text Messages
        if ($update->has('message')) {
            $this->handleText($update->getMessage(), $chatId, $employee, $isAdmin);
        }
    }

    protected function handleCallback($callback, $chatId, $employee, $isAdmin)
    {
        $data = $callback->getData();
        Telegram::answerCallbackQuery(['callback_query_id' => $callback->getId()]);

        // Admin Actions
        if (Str::startsWith($data, 'admin_') && $isAdmin) {
            if ($data === 'admin_cancel_wizard') {
                Cache::forget("admin_job_wizard_{$chatId}");
                Telegram::sendMessage(['chat_id' => $chatId, 'text' => '❌ Job posting cancelled.']);
            } elseif (Str::startsWith($data, 'admin_cand_')) {
                // Delegate to Admin Service
                $this->adminService->handleCallback($data, $chatId, $employee);
            }
            return;
        }

        // Candidate Actions
        if (Str::startsWith($data, 'lang_') || Str::startsWith($data, 'select_') || Str::startsWith($data, 'start_form_')) {
            // Delegate to Candidate Service (You might need to refactor CandidateService to accept raw data)
            // For now, keeping your existing logic structure:
            if (Str::startsWith($data, 'lang_')) $this->candidateService->handleLanguageSelection($chatId, Str::after($data, 'lang_'));
            // ... map other candidate functions ...
        }
    }

    protected function handleText($message, $chatId, $employee, $isAdmin)
    {
        $text = $message->getText() ?? '';

        // 1. Admin Commands
        if ($isAdmin) {
            if ($this->adminService->handleCommand($text, $chatId, $employee)) {
                return; // Command handled
            }
        }

        // 2. Deep Links / Candidate Flow
        if (Str::startsWith($text, '/start ') && strlen($text) > 7) {
            $this->candidateService->handleStartCommand($chatId, $text);
            return;
        }

        // 3. Employee Logic (Attendance)
        if ($employee) {
            // Delegate to Attendance Service
            // Note: You need to move handleEmployeeLogic logic into AttendanceService
            // For now, we can keep it simple:
            if ($text === '📍 Check In') {
                // $this->attendanceService->askForLocation($chatId, 'check_in');
            }
            return;
        }

        // 4. Registration
        if ($message->has('contact')) {
            $this->handleRegistration($message->getContact(), $chatId);
            return;
        }

        // 5. Fallback
        $this->sendStartWithRegister($chatId);
    }

    protected function handleRegistration($contact, $chatId)
    {
        if ($contact->getUserId() !== $chatId) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🚫 Send your own contact."]);
            return;
        }

        $phone = preg_replace('/[^0-9]/', '', $contact->getPhoneNumber());
        $emp = Employee::withoutGlobalScopes()
            ->whereRaw("REPLACE(phone_number, '+', '') LIKE '%" . substr($phone, -9) . "'")
            ->first();

        if ($emp) {
            $emp->update(['telegram_chat_id' => $chatId, 'telegram_username' => 'user']);
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🎉 Connected!"]);
        } else {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🚫 Not found."]);
        }
    }

    protected function checkIfAdmin($employee)
    {
        if (!$employee) return false;
        $employee->load(['department', 'user']);
        return in_array($employee->department?->name, ['HR', 'Human Resources']) ||
            ($employee->user && $employee->user->isAdmin());
    }

    protected function sendStartWithRegister($chatId)
    {
        // ... (Your existing keyboard code) ...
        Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Please register."]);
    }
}
