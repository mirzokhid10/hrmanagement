<?php

namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Employee;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendAnnouncementToTelegram implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Announcement $announcement;
    /**
     * Create a new job instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Start Query
        $query = Employee::where('company_id', $this->announcement->company_id)
            ->whereNotNull('telegram_chat_id');

        // FILTER LOGIC
        if ($this->announcement->audience_type === 'employees') {
            // Only specific people
            $recipientIds = $this->announcement->recipients()->pluck('employees.id');
            $query->whereIn('id', $recipientIds);
        }
        elseif ($this->announcement->audience_type === 'department') {
            // Everyone in Dept
            $query->where('department_id', $this->announcement->department_id);

            // Edge Case: If user selected "Specific Employees" INSIDE a department view,
            // the service saved it as 'employees' type, so the logic above handles it.
            // But if you want "All Dept" + "Specific Extras", logic varies.
            // For simplicity, we stick to the 3 strict types.
        }

        $query->chunk(100, function ($employees) {
            foreach ($employees as $employee) {
                $this->sendToEmployee($employee);
            }
        });
    }

    protected function sendToEmployee(Employee $employee)
    {
        $emoji = $this->announcement->audience_type === 'all' ? '📢' : '🔒';

        $message = "{$emoji} **Yangi E'lon**\n\n" .
            "**{$this->announcement->title}**\n\n" .
            "{$this->announcement->content}\n\n" .
            "__" . $this->announcement->created_at->format('d.m.Y H:i') . "__";

        try {
            Telegram::sendMessage([
                'chat_id' => $employee->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            // Optional: Sleep slightly to avoid hitting Telegram Rate Limits (30 msgs/sec)
            usleep(100000); // 0.1 second

        } catch (\Exception $e) {
            Log::error("Failed to send announcement to {$employee->id}: " . $e->getMessage());
        }
    }
}
