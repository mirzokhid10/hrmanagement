<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class CheckBirthdays extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-birthdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('m-d');

        // Find employees with birthday today
        $employees = Employee::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$today])
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) return;

        foreach ($employees as $emp) {
            // Notify Company HR Channel (You need to store this chat_id in Company settings)
            // For now, let's notify the connected HR managers of that company

            $admins = Employee::where('company_id', $emp->company_id)
                ->whereNotNull('telegram_chat_id')
                // ->where(...) add admin check logic here
                ->get();

            foreach ($admins as $admin) {
                TelegraM::sendMessage([
                    'chat_id' => $admin->telegram_chat_id,
                    'text' => "🎂 **Happy Birthday!**\n\nToday is **{$emp->first_name} {$emp->last_name}**'s birthday! Don't forget to wish them.",
                    'parse_mode' => 'Markdown'
                ]);
            }
        }
    }
}
