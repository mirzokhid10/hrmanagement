<?php

namespace App\Services;

use App\Jobs\SendAnnouncementToTelegram;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class AnnouncementService
{
    public function createAnnouncement(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Determine Company ID
            $companyId = Auth::user()->isAdmin() && isset($data['company_id'])
                ? $data['company_id']
                : Auth::user()->company_id;

            // 2. Create Announcement
            $announcement = Announcement::create([
                'company_id' => $companyId,
                'title' => $data['title'],
                'content' => $data['content'],
                'audience_type' => $data['audience_type'], // 'company', 'department', 'employees'
                'department_id' => $data['department_id'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // 3. Attach Specific Employees (if selected)
            if (!empty($data['employee_ids'])) {
                $announcement->recipients()->attach($data['employee_ids']);
            }

            // 4. Dispatch Telegram Job
            if (isset($data['send_to_telegram']) && $data['send_to_telegram']) {
                SendAnnouncementToTelegram::dispatch($announcement);
            }

            return $announcement;
        });
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        // Security check handled in controller or policy
        return $announcement->delete();
    }
}
