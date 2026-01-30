<?php

namespace App\Services\Telegram\Handlers\Admin;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Recruitment;
use App\Models\TimeOff;
use App\Models\Attendance;
use App\Services\Telegram\Handlers\EmployeeHandler;
use App\Services\Telegram\Helpers\TelegramKeyboardBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class HRHandler
{
    /**
     * Send main menu for HR managers
     */
    public function sendMainMenu(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];
        $companyId = $auth['company_id'];

        // Count pending candidates for this company
        $pendingCount = Candidate::where('company_id', $companyId)
            ->where('status', 'pending')
            ->count();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👨‍💼 *HR Manager Dashboard*\n\nWelcome, {$employee->first_name}!",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::hrMainMenu($pendingCount)
        ]);
    }

    /**
     * Handle text button presses
     */
    public function handleText(string $text, int $chatId, array $auth): void
    {
        match ($text) {
            '💼 Post New Job' => $this->startJobPostingWizard($chatId, $auth),
            '👥 Review Candidates' => $this->listPendingCandidates($chatId, $auth, 1),
            '📊 Today\'s Stats' => $this->showTodayStats($chatId, $auth),
            '🌴 Who\'s Out Today' => $this->checkWhosOut($chatId, $auth, null),
            '📢 Send Announcement' => $this->startAnnouncementWizard($chatId, $auth),
            '👤 Add Employee' => $this->startAddEmployeeWizard($chatId, $auth),
            '📍 Check In' => app(EmployeeHandler::class)->handleText($text, $chatId, $auth),
            '👋 Check Out' => app(EmployeeHandler::class)->handleText($text, $chatId, $auth),
            '🏠 Main Menu' => $this->sendMainMenu($chatId, $auth),
            default => $this->sendMainMenu($chatId, $auth)
        };
    }

    /**
     * Handle callback queries
     */
    public function handleCallback($callback, int $chatId, array $auth): void
    {
        $data = $callback->getData();

        if ($data === 'hr_cancel_wizard') {
            $this->cancelAllWizards($chatId);
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => '❌ Operation cancelled.']);
            $this->sendMainMenu($chatId, $auth);
        } elseif (Str::startsWith($data, 'hr_cand_page_')) {
            $page = (int) Str::after($data, 'hr_cand_page_');
            $this->listPendingCandidates($chatId, $auth, $page);
        } elseif (Str::startsWith($data, 'hr_cand_')) {
            $this->handleCandidateAction($callback, $chatId, $auth, $data);
        } elseif (Str::startsWith($data, 'hr_dept_')) {
            $this->handleDepartmentSelection($chatId, $auth, $data);
        } elseif (Str::startsWith($data, 'hr_emp_dept_')) {
            $this->handleEmployeeDepartmentSelection($chatId, $auth, $data);
        }
    }

    // =========================================================================
    // 💼 JOB POSTING WIZARD
    // =========================================================================

    public function startJobPostingWizard(int $chatId, array $auth): void
    {
        $wizard = [
            'step' => 'title',
            'company_id' => $auth['company_id'],
            'data' => []
        ];

        Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "💼 *New Job Posting Wizard*\n\nLet's create a new vacancy.\n\n1️⃣ Please enter the *Job Title*:",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::cancelButton()
        ]);
    }

    public function continueJobPostingWizard($update, int $chatId, array $auth): void
    {
        $wizard = Cache::get("wizard_job_posting_{$chatId}");

        if (!$wizard) {
            $this->sendMainMenu($chatId, $auth);
            return;
        }

        // Extract input
        $input = [];
        if ($update->has('message')) {
            $input['text'] = $update->getMessage()->getText() ?? '';
        } elseif ($update->has('callback_query')) {
            $input['callback'] = $update->getCallbackQuery()->getData();
        }

        $this->handleJobPostingStep($chatId, $auth, $wizard, $input);
    }

    protected function handleJobPostingStep(int $chatId, array $auth, array $wizard, array $input): void
    {
        $step = $wizard['step'];
        $text = $input['text'] ?? '';
        $callback = $input['callback'] ?? '';

        switch ($step) {
            case 'title':
                $wizard['data']['title'] = $text;
                $wizard['step'] = 'department';
                Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

                // Show department buttons
                $this->showDepartmentSelection($chatId, $auth);
                break;

            case 'department':
                // Handled by callback
                break;

            case 'description':
                $wizard['data']['description'] = $text;
                $wizard['step'] = 'salary';
                Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Description saved.\n\n4️⃣ Enter *Salary Range* (e.g., $500 - $1000 or 5000000 - 10000000 UZS):",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'salary':
                $wizard['data']['salary_range'] = $text;
                $wizard['step'] = 'location';
                Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Salary saved.\n\n5️⃣ Enter *Location* (e.g., Tashkent, Remote):",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'location':
                $wizard['data']['location'] = $text;
                $wizard['step'] = 'deadline';
                Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Location saved.\n\n6️⃣ Enter *Application Deadline* (YYYY-MM-DD, e.g., 2025-02-28):",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'deadline':
                // Validate date format
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $text)) {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "⚠️ Invalid date format. Please use YYYY-MM-DD (e.g., 2025-02-28)."
                    ]);
                    return;
                }

                $wizard['data']['deadline'] = $text;

                // Save job to database
                $this->saveJob($chatId, $auth, $wizard);
                Cache::forget("wizard_job_posting_{$chatId}");
                break;
        }
    }

    protected function showDepartmentSelection(int $chatId, array $auth): void
    {
        $departments = Department::withoutGlobalScopes()
            ->where('company_id', $auth['company_id'])
            ->get();

        if ($departments->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ No departments found! Please add departments in the web panel first."
            ]);
            Cache::forget("wizard_job_posting_{$chatId}");
            $this->sendMainMenu($chatId, $auth);
            return;
        }

        $keyboard = Keyboard::make()->inline();
        foreach ($departments as $dept) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $dept->name,
                    'callback_data' => "hr_dept_{$dept->id}"
                ])
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Title saved.\n\n2️⃣ Select the *Department*:",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    protected function handleDepartmentSelection(int $chatId, array $auth, string $data): void
    {
        $wizard = Cache::get("wizard_job_posting_{$chatId}");

        if (!$wizard || $wizard['step'] !== 'department') {
            return;
        }

        $deptId = Str::after($data, 'hr_dept_');
        $wizard['data']['department_id'] = $deptId;
        $wizard['step'] = 'description';

        Cache::put("wizard_job_posting_{$chatId}", $wizard, now()->addMinutes(30));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Department selected.\n\n3️⃣ Now enter the *Job Description*:",
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function saveJob(int $chatId, array $auth, array $wizard): void
    {
        try {
            $job = Recruitment::create([
                'company_id' => $auth['company_id'],
                'department_id' => $wizard['data']['department_id'],
                'title' => $wizard['data']['title'],
                'description' => $wizard['data']['description'],
                'salary_range' => $wizard['data']['salary_range'],
                'location' => $wizard['data']['location'],
                'deadline' => $wizard['data']['deadline'],
                'status' => 'published',
                'job_type' => 'Full-time',
                'schedule' => 'Standard',
                'working_hours' => '9-6',
                'experience' => 'Not specified',
                'billing_type' => 'Standard'
            ]);

            // Generate Telegram deep link
            $botUsername = config('telegram.bot_username', 'your_bot'); // Add this to config
            $deepLink = "https://t.me/{$botUsername}?start=apply_{$job->id}";

            $message = "🎉 *Job Posted Successfully!*\n\n";
            $message .= "*Title:* {$job->title}\n";
            $message .= "*Department:* " . Department::find($job->department_id)->name . "\n";
            $message .= "*Location:* {$job->location}\n\n";
            $message .= "📱 *Share this link:*\n`{$deepLink}`\n\n";
            $message .= "_Candidates can click this link to apply directly via Telegram._";

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            $this->sendMainMenu($chatId, $auth);
        } catch (\Exception $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Error saving job: " . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 👥 CANDIDATE REVIEW
    // =========================================================================

    public function listPendingCandidates(int $chatId, array $auth, int $page = 1): void
    {
        $perPage = 1;

        $candidates = Candidate::where('company_id', $auth['company_id'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($candidates->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ No pending candidates found.",
                'reply_markup' => TelegramKeyboardBuilder::hrMainMenu()
            ]);
            return;
        }

        $candidate = $candidates->first();

        $msg = "👤 *Candidate Review* ({$candidates->currentPage()}/{$candidates->lastPage()})\n\n";
        $msg .= "*Name:* {$candidate->first_name} {$candidate->last_name}\n";
        $msg .= "*Job:* " . ($candidate->recruitment->title ?? 'General') . "\n";
        $msg .= "*Phone:* {$candidate->phone}\n";
        $msg .= "*Email:* {$candidate->email}\n\n";
        $msg .= "*Cover Letter:*\n" . ($candidate->cover_letter ?? 'N/A');

        $keyboard = Keyboard::make()->inline();

        // Action buttons
        $keyboard->row([
            Keyboard::inlineButton(['text' => '✅ Shortlist', 'callback_data' => "hr_cand_approve_{$candidate->id}"]),
            Keyboard::inlineButton(['text' => '❌ Reject', 'callback_data' => "hr_cand_reject_{$candidate->id}"])
        ]);

        // Resume download button
        if ($candidate->resume_path) {
            $keyboard->row([
                Keyboard::inlineButton(['text' => '📥 Download Resume', 'callback_data' => "hr_cand_resume_{$candidate->id}"])
            ]);
        }

        // Navigation buttons
        $navRow = [];
        if ($candidates->currentPage() > 1) {
            $navRow[] = Keyboard::inlineButton(['text' => '⬅️ Prev', 'callback_data' => "hr_cand_page_" . ($page - 1)]);
        }
        if ($candidates->hasMorePages()) {
            $navRow[] = Keyboard::inlineButton(['text' => 'Next ➡️', 'callback_data' => "hr_cand_page_" . ($page + 1)]);
        }
        if (!empty($navRow)) {
            $keyboard->row($navRow);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    protected function handleCandidateAction($callback, int $chatId, array $auth, string $data): void
    {
        $parts = explode('_', $data);
        if (count($parts) < 4) return;

        $action = $parts[2]; // approve, reject, resume
        $candidateId = $parts[3];

        $candidate = Candidate::where('company_id', $auth['company_id'])
            ->find($candidateId);

        if (!$candidate) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "❌ Candidate not found."]);
            return;
        }

        if ($action === 'approve') {
            $candidate->update(['status' => 'shortlisted']);
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Candidate *{$candidate->first_name} {$candidate->last_name}* shortlisted.",
                'parse_mode' => 'Markdown'
            ]);
            $this->listPendingCandidates($chatId, $auth, 1);
        } elseif ($action === 'reject') {
            $candidate->update(['status' => 'rejected']);
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Candidate *{$candidate->first_name} {$candidate->last_name}* rejected.",
                'parse_mode' => 'Markdown'
            ]);
            $this->listPendingCandidates($chatId, $auth, 1);
        } elseif ($action === 'resume') {
            if ($candidate->resume_path && \Storage::exists($candidate->resume_path)) {
                Telegram::sendDocument([
                    'chat_id' => $chatId,
                    'document' => \Storage::path($candidate->resume_path),
                    'caption' => "Resume: {$candidate->first_name} {$candidate->last_name}"
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "⚠️ Resume file not found on server."
                ]);
            }
        }
    }

    // =========================================================================
    // 📊 STATISTICS
    // =========================================================================

    public function showTodayStats(int $chatId, array $auth): void
    {
        $today = now()->toDateString();
        $companyId = $auth['company_id'];

        // Attendance stats
        $totalEmployees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        $present = Attendance::where('company_id', $companyId)
            ->where('date', $today)
            ->whereNotNull('check_in_time')
            ->count();

        $late = Attendance::where('company_id', $companyId)
            ->where('date', $today)
            ->where('status', 'late')
            ->count();

        $absent = $totalEmployees - $present;

        // Recruitment stats
        $openJobs = Recruitment::where('company_id', $companyId)
            ->where('status', 'published')
            ->count();

        $newCandidates = Candidate::where('company_id', $companyId)
            ->where('status', 'pending')
            ->count();

        $message = "📊 *Today's Stats*\n";
        $message .= "_" . now()->format('l, d M Y') . "_\n\n";
        $message .= "👥 *Workforce:*\n";
        $message .= "• Present: {$present}/{$totalEmployees}\n";
        $message .= "• Late: {$late}\n";
        $message .= "• Absent: {$absent}\n\n";
        $message .= "💼 *Hiring:*\n";
        $message .= "• Open Jobs: {$openJobs}\n";
        $message .= "• New Candidates: {$newCandidates}";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::hrMainMenu()
        ]);
    }

    // =========================================================================
    // 🌴 WHO'S OUT
    // =========================================================================

    public function checkWhosOut(int $chatId, array $auth, ?string $date = null): void
    {
        $targetDate = $date ? \Carbon\Carbon::parse($date) : now();
        $companyId = $auth['company_id'];

        $leaves = TimeOff::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->with('employee')
            ->get();

        if ($leaves->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Everyone is working on " . $targetDate->format('d M Y'),
                'reply_markup' => TelegramKeyboardBuilder::hrMainMenu()
            ]);
            return;
        }

        $message = "🌴 *Who's Out (" . $targetDate->format('d M') . ")*\n\n";
        foreach ($leaves as $leave) {
            $message .= "👤 *{$leave->employee->first_name} {$leave->employee->last_name}*\n";
            $message .= "   Until: {$leave->end_date->format('d M')}\n";
            $message .= "   Reason: {$leave->reason}\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::hrMainMenu()
        ]);
    }

    // =========================================================================
    // 👤 ADD EMPLOYEE WIZARD
    // =========================================================================

    public function startAddEmployeeWizard(int $chatId, array $auth): void
    {
        $wizard = [
            'step' => 'name',
            'company_id' => $auth['company_id'],
            'data' => []
        ];

        Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👤 *Add New Employee Wizard*\n\n1️⃣ Enter Full Name (e.g., John Doe):",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::cancelButton()
        ]);
    }

    public function continueAddEmployeeWizard($update, int $chatId, array $auth): void
    {
        $wizard = Cache::get("wizard_add_employee_{$chatId}");

        if (!$wizard) {
            $this->sendMainMenu($chatId, $auth);
            return;
        }

        $text = $update->has('message') ? ($update->getMessage()->getText() ?? '') : '';
        $callback = $update->has('callback_query') ? $update->getCallbackQuery()->getData() : '';

        $this->handleAddEmployeeStep($chatId, $auth, $wizard, $text, $callback);
    }

    protected function handleAddEmployeeStep(int $chatId, array $auth, array $wizard, string $text, string $callback): void
    {
        $step = $wizard['step'];

        switch ($step) {
            case 'name':
                $parts = explode(' ', trim($text), 2);
                $wizard['data']['first_name'] = $parts[0];
                $wizard['data']['last_name'] = $parts[1] ?? '';
                $wizard['step'] = 'phone';
                Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Name saved.\n\n2️⃣ Enter *Phone Number* (e.g., +998901234567):",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'phone':
                $phone = preg_replace('/[^0-9+]/', '', $text);
                if (strlen($phone) < 9) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ Invalid phone. Try again:"]);
                    return;
                }
                $wizard['data']['phone'] = $phone;
                $wizard['step'] = 'email';
                Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Phone saved.\n\n3️⃣ Enter *Email Address*:",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'email':
                if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ Invalid email. Try again:"]);
                    return;
                }
                $wizard['data']['email'] = $text;
                $wizard['step'] = 'department';
                Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

                // Show departments
                $this->showDepartmentSelectionForEmployee($chatId, $auth);
                break;

            case 'department':
                // Handled by callback
                break;

            case 'job_title':
                $wizard['data']['job_title'] = $text;
                $wizard['step'] = 'salary';
                Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Job Title saved.\n\n6️⃣ Enter *Salary* (numbers only):",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'salary':
                $salary = preg_replace('/[^0-9.]/', '', $text);
                $wizard['data']['salary'] = (float) $salary;

                // Save employee
                $this->saveEmployee($chatId, $auth, $wizard);
                Cache::forget("wizard_add_employee_{$chatId}");
                break;
        }
    }

    protected function showDepartmentSelectionForEmployee(int $chatId, array $auth): void
    {
        $departments = Department::withoutGlobalScopes()
            ->where('company_id', $auth['company_id'])
            ->get();

        $keyboard = Keyboard::make()->inline();
        foreach ($departments as $dept) {
            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $dept->name,
                    'callback_data' => "hr_emp_dept_{$dept->id}"
                ])
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Email saved.\n\n4️⃣ Select *Department*:",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    protected function handleEmployeeDepartmentSelection(int $chatId, array $auth, string $data): void
    {
        $wizard = Cache::get("wizard_add_employee_{$chatId}");

        if (!$wizard || $wizard['step'] !== 'department') {
            return;
        }

        $deptId = Str::after($data, 'hr_emp_dept_');
        $wizard['data']['department_id'] = $deptId;
        $wizard['step'] = 'job_title';

        Cache::put("wizard_add_employee_{$chatId}", $wizard, now()->addMinutes(15));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Department selected.\n\n5️⃣ Enter *Job Title* (e.g., Sales Manager):",
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function saveEmployee(int $chatId, array $auth, array $wizard): void
    {
        try {
            $employee = Employee::create([
                'company_id' => $auth['company_id'],
                'first_name' => $wizard['data']['first_name'],
                'last_name' => $wizard['data']['last_name'],
                'phone_number' => $wizard['data']['phone'],
                'email' => $wizard['data']['email'],
                'department_id' => $wizard['data']['department_id'],
                'job_title' => $wizard['data']['job_title'],
                'salary' => $wizard['data']['salary'],
                'hire_date' => now(),
                'status' => 'active',
            ]);

            $message = "🎉 *Employee Added!*\n\n";
            $message .= "👤 {$employee->first_name} {$employee->last_name}\n";
            $message .= "📧 {$employee->email}\n";
            $message .= "💼 {$employee->job_title}\n\n";
            $message .= "_Employee can now register in the bot using their phone number._";

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            $this->sendMainMenu($chatId, $auth);
        } catch (\Exception $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Error: " . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 📢 ANNOUNCEMENT WIZARD
    // =========================================================================

    public function startAnnouncementWizard(int $chatId, array $auth): void
    {
        $wizard = [
            'step' => 'title',
            'company_id' => $auth['company_id'],
            'data' => []
        ];

        Cache::put("wizard_announcement_{$chatId}", $wizard, now()->addMinutes(15));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📢 *Send Announcement*\n\n1️⃣ Enter announcement *Title*:",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::cancelButton()
        ]);
    }

    public function continueAnnouncementWizard($update, int $chatId, array $auth): void
    {
        $wizard = Cache::get("wizard_announcement_{$chatId}");

        if (!$wizard) {
            $this->sendMainMenu($chatId, $auth);
            return;
        }

        $text = $update->has('message') ? ($update->getMessage()->getText() ?? '') : '';
        $this->handleAnnouncementStep($chatId, $auth, $wizard, $text);
    }

    protected function handleAnnouncementStep(int $chatId, array $auth, array $wizard, string $text): void
    {
        $step = $wizard['step'];

        switch ($step) {
            case 'title':
                $wizard['data']['title'] = $text;
                $wizard['step'] = 'content';
                Cache::put("wizard_announcement_{$chatId}", $wizard, now()->addMinutes(15));

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Title saved.\n\n2️⃣ Enter announcement *Content*:",
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'content':
                $wizard['data']['content'] = $text;

                // Save and broadcast
                $this->saveAndBroadcastAnnouncement($chatId, $auth, $wizard);
                Cache::forget("wizard_announcement_{$chatId}");
                break;
        }
    }

    protected function saveAndBroadcastAnnouncement(int $chatId, array $auth, array $wizard): void
    {
        try {
            $announcement = \App\Models\Announcement::create([
                'company_id' => $auth['company_id'],
                'title' => $wizard['data']['title'],
                'content' => $wizard['data']['content'],
                'audience_type' => 'all',
                'created_by' => $auth['employee']->id,
            ]);

            // Queue sending to all employees
            \App\Jobs\SendAnnouncementToTelegram::dispatch($announcement);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ *Announcement Sent!*\n\nYour announcement is being delivered to all employees.",
                'parse_mode' => 'Markdown',
            ]);

            $this->sendMainMenu($chatId, $auth);
        } catch (\Exception $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Error: " . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    protected function cancelAllWizards(int $chatId): void
    {
        Cache::forget("wizard_job_posting_{$chatId}");
        Cache::forget("wizard_add_employee_{$chatId}");
        Cache::forget("wizard_announcement_{$chatId}");
    }
}
