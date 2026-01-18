<?php

namespace App\Console\Commands;

use App\Models\BotSetting;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Api;

class SendAttendanceReminders extends Command
{
    protected $signature = 'attendance:send-reminders';
    protected $description = 'Send attendance reminders to employees via Telegram';

    public function handle()
    {
        $telegram = new Api(config('telegram.bot_token'));

        // Get all companies with reminder enabled
        $companies = Company::whereHas('botSettings', function ($query) {
            $query->where('send_daily_reminders', true);
        })->get();

        foreach ($companies as $company) {
            $settings = BotSetting::getForCompany($company->id);
            $today = Carbon::now($settings->timezone)->toDateString();

            // Get employees who haven't checked in
            $employees = Employee::where('company_id', $company->id)
                ->where('status', 'active')
                ->whereDoesntHave('attendances', function ($query) use ($today) {
                    $query->where('date', $today)
                        ->whereNotNull('check_in_time');
                })
                ->get();

            foreach ($employees as $employee) {
                $telegramUser = Employee::where('employee_id', $employee->id)->first();

                if ($telegramUser && $telegramUser->is_active) {
                    try {
                        $telegram->sendMessage([
                            'chat_id' => $telegramUser->telegram_chat_id,
                            'text' => "⏰ Reminder: Don't forget to check in!\n\nUse /checkin to mark your attendance.",
                        ]);
                    } catch (\Exception $e) {
                        $this->error("Failed to send reminder to {$employee->full_name}: " . $e->getMessage());
                    }
                }
            }

            $this->info("Sent reminders to {$employees->count()} employees in {$company->name}");
        }

        $this->info('✅ Attendance reminders sent successfully!');
    }
}
