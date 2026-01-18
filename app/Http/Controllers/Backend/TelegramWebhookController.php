<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BotSetting;
use App\Models\Employee;
use App\Models\TelegramUser;
use App\Services\TelegramAttendanceService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramWebhookController extends Controller
{
    protected Api $telegram;
    protected TelegramAttendanceService $attendanceService;

    public function __construct(TelegramAttendanceService $attendanceService)
    {
        $this->telegram = new Api(config('telegram.bot_token'));
        $this->attendanceService = $attendanceService;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Get update from request
            $update = $this->telegram->getWebhookUpdate();

            Log::info('Telegram webhook received', [
                'update_id' => $update->getUpdateId(),
            ]);

            if ($update->getMessage()) {
                $this->handleMessage($update->getMessage());
            } elseif ($update->getCallbackQuery()) {
                $this->handleCallbackQuery($update->getCallbackQuery());
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming messages
     */
    protected function handleMessage($message)
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        $from = $message->getFrom();

        // Handle location sharing
        if ($message->getLocation()) {
            $this->handleLocation($chatId, $message->getLocation(), $from);
            return;
        }

        // Handle commands
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $text, $from);
            return;
        }

        // Default response
        $this->sendMessage($chatId, "I didn't understand that. Try /help to see available commands.");
    }

    /**
     * Handle bot commands
     */
    protected function handleCommand($chatId, $command, $from)
    {
        $telegramUser = $this->getOrCreateTelegramUser($chatId, $from);

        switch (true) {
            case str_starts_with($command, '/start'):
                $this->handleStart($chatId, $telegramUser);
                break;

            case str_starts_with($command, '/checkin'):
                $this->handleCheckIn($chatId, $telegramUser);
                break;

            case str_starts_with($command, '/checkout'):
                $this->handleCheckOut($chatId, $telegramUser);
                break;

            case str_starts_with($command, '/status'):
                $this->handleStatus($chatId, $telegramUser);
                break;

            case str_starts_with($command, '/today'):
                $this->handleToday($chatId, $telegramUser);
                break;

            case str_starts_with($command, '/help'):
                $this->handleHelp($chatId);
                break;

            default:
                $this->sendMessage($chatId, "Unknown command. Try /help");
        }
    }

    /**
     * Handle /start command
     */
    protected function handleStart($chatId, Employee $telegramUser)
    {
        if (!$telegramUser->employee) {
            $this->sendMessage(
                $chatId,
                "👋 Welcome to HR Management Bot!\n\n" .
                    "❌ Your account is not linked to an employee profile.\n" .
                    "Please contact your HR manager to link your Telegram account."
            );
            return;
        }

        $employee = $telegramUser->employee;
        $company = $employee->company;
        $settings = BotSetting::getForCompany($company->id);

        $welcomeMsg = $settings->welcome_message ?:
            "👋 Welcome {$employee->first_name}!\n\n" .
            "I'm your attendance assistant. Here's what I can do:\n\n" .
            "📍 /checkin - Check in to work\n" .
            "👋 /checkout - Check out from work\n" .
            "📊 /status - View your attendance status\n" .
            "📅 /today - Today's attendance summary\n" .
            "❓ /help - Show help\n\n" .
            "Let's get started! Use /checkin when you arrive at the office.";

        $this->sendMessage($chatId, $welcomeMsg);
    }

    /**
     * Handle /checkin command
     */
    protected function handleCheckIn($chatId, Employee $telegramUser)
    {
        if (!$telegramUser->employee) {
            $this->sendMessage($chatId, "❌ Employee profile not found. Please contact HR.");
            return;
        }

        $company = $telegramUser->employee->company;
        $settings = BotSetting::getForCompany($company->id);

        // Check if location is required
        if ($settings->location_verification_required) {
            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '📍 Share Location',
                        'request_location' => true,
                    ]),
                ]);

            $this->sendMessage(
                $chatId,
                "📍 To check in, please share your location.\n\n" .
                    "Click the button below to share your current location.",
                $keyboard
            );
        } else {
            // Process check-in without location
            $result = $this->attendanceService->processCheckIn($telegramUser);
            $this->sendMessage($chatId, $result['message']);
        }
    }

    /**
     * Handle location sharing
     */
    protected function handleLocation($chatId, $location, $from)
    {
        $telegramUser = $this->getOrCreateTelegramUser($chatId, $from);

        if (!$telegramUser->employee) {
            $this->sendMessage($chatId, "❌ Employee profile not found.");
            return;
        }

        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();

        // Process check-in with location
        $result = $this->attendanceService->processCheckIn(
            $telegramUser,
            $latitude,
            $longitude
        );

        // If WiFi verification is required, send verification link
        if ($result['success'] && isset($result['requires_wifi'])) {
            $verifyUrl = route('telegram.verify-wifi', ['attendance' => $result['attendance_id']]);

            $keyboard = Keyboard::make()
                ->inline()
                ->row([
                    Keyboard::inlineButton([
                        'text' => '📶 Verify WiFi Connection',
                        'url' => $verifyUrl,
                    ]),
                ]);

            $this->sendMessage(
                $chatId,
                $result['message'] . "\n\n" .
                    "📶 Please click the button below to verify your WiFi connection:",
                $keyboard
            );
        } else {
            $this->sendMessage($chatId, $result['message']);
        }
    }

    /**
     * Handle /checkout command
     */
    protected function handleCheckOut($chatId, Employee $telegramUser)
    {
        if (!$telegramUser->employee) {
            $this->sendMessage($chatId, "❌ Employee profile not found.");
            return;
        }

        $result = $this->attendanceService->processCheckOut($telegramUser);
        $this->sendMessage($chatId, $result['message']);
    }

    /**
     * Handle /status command
     */
    protected function handleStatus($chatId, Employee $telegramUser)
    {
        if (!$telegramUser->employee) {
            $this->sendMessage($chatId, "❌ Employee profile not found.");
            return;
        }

        $employee = $telegramUser->employee;
        $company = $employee->company;
        $settings = BotSetting::getForCompany($company->id);
        $today = \Carbon\Carbon::now($settings->timezone)->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            $this->sendMessage(
                $chatId,
                "📊 Today's Status\n\n" .
                    "❌ Not checked in yet\n\n" .
                    "Use /checkin to check in to work."
            );
            return;
        }

        $status = "📊 Today's Status\n\n";
        $status .= "✅ Check-in: " . $attendance->check_in_time->format('H:i') . "\n";

        if ($attendance->status === 'late') {
            $status .= "⚠️ Status: Late\n";
        } else {
            $status .= "✅ Status: On time\n";
        }

        if ($attendance->check_out_time) {
            $status .= "👋 Check-out: " . $attendance->check_out_time->format('H:i') . "\n";
            $status .= "⏱️ Duration: " . $attendance->work_duration . "\n";
        } else {
            $status .= "\n💡 Remember to /checkout when leaving!";
        }

        $this->sendMessage($chatId, $status);
    }

    /**
     * Handle /today command (for managers)
     */
    protected function handleToday($chatId, Employee $telegramUser)
    {
        if (!$telegramUser->employee) {
            $this->sendMessage($chatId, "❌ Employee profile not found.");
            return;
        }

        $employee = $telegramUser->employee;

        // Check if user is a manager
        if (!$employee->directReports()->exists()) {
            $this->sendMessage($chatId, "❌ This command is only available for managers.");
            return;
        }

        $company = $employee->company;
        $summary = $this->attendanceService->getTodaySummary($company->id);

        $message = "📅 Today's Attendance Summary\n\n";
        $message .= "👥 Total Employees: {$summary['total_employees']}\n";
        $message .= "✅ Present: {$summary['present']}\n";
        $message .= "⚠️ Late: {$summary['late']}\n";
        $message .= "❌ Absent: {$summary['absent']}\n";
        $message .= "📊 Attendance Rate: {$summary['attendance_rate']}%";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Handle /help command
     */
    protected function handleHelp($chatId)
    {
        $help = "❓ Available Commands\n\n";
        $help .= "📍 /checkin - Check in to work\n";
        $help .= "👋 /checkout - Check out from work\n";
        $help .= "📊 /status - View your attendance status\n";
        $help .= "📅 /today - Today's summary (managers only)\n";
        $help .= "❓ /help - Show this help message\n\n";
        $help .= "Need assistance? Contact your HR manager.";

        $this->sendMessage($chatId, $help);
    }

    /**
     * Handle callback queries (inline button clicks)
     */
    protected function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $data = $callbackQuery->getData();

        // Handle different callback actions
        // Add custom callback handlers here

        $this->telegram->answerCallbackQuery([
            'callback_query_id' => $callbackQuery->getId(),
        ]);
    }

    /**
     * Get or create Telegram user record
     */
    protected function getOrCreateTelegramUser($chatId, $from): Employee
    {
        $telegramUser = Employee::where('telegram_chat_id', $chatId)->first();

        if (!$telegramUser) {
            // Try to find employee by Telegram username
            $employee = null;
            if ($from->getUsername()) {
                $employee = Employee::whereHas('user', function ($query) use ($from) {
                    $query->where('email', 'LIKE', '%' . $from->getUsername() . '%');
                })->first();
            }

            $telegramUser = Employee::create([
                'company_id' => $employee?->company_id,
                'user_id' => $employee?->user_id,
                'employee_id' => $employee?->id,
                'telegram_chat_id' => $chatId,
                'telegram_username' => $from->getUsername(),
                'first_name' => $from->getFirstName(),
                'last_name' => $from->getLastName(),
            ]);
        }

        $telegramUser->updateLastInteraction();

        return $telegramUser;
    }

    /**
     * Send message to Telegram chat
     */
    protected function sendMessage($chatId, $text, $keyboard = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $params['reply_markup'] = $keyboard;
        }

        $this->telegram->sendMessage($params);
    }
}
