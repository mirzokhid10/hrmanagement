<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Recruitment;
use App\Models\Department; // Don't forget this
use App\Models\TimeOff;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Str;
use App\Models\Attendance;

class TelegramAdminService
{
    // =========================================================================
    // 📢 JOB POSTING WIZARD (/postjob)
    // =========================================================================

    public function startJobWizard($chatId, Employee $admin)
    {
        // 1. Init Session
        $session = [
            'step' => 'title',
            'company_id' => $admin->company_id,
            'data' => []
        ];

        Cache::put("admin_job_wizard_{$chatId}", $session, now()->addMinutes(30));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "💼 **New Job Posting Wizard**\n\nLet's create a new vacancy.\n\n1️⃣ Please enter the **Job Title**:",
            'parse_mode' => 'Markdown',
            'reply_markup' => Keyboard::make()->inline()->row([
                Keyboard::inlineButton(['text' => '❌ Cancel', 'callback_data' => 'admin_cancel_wizard'])
            ])
        ]);
    }

    public function handleJobWizard($chatId, $text)
    {
        $session = Cache::get("admin_job_wizard_{$chatId}");
        if (!$session) return false;

        $step = $session['step'];

        switch ($step) {
            case 'title':
                $session['data']['title'] = $text;

                // Ask for Department (Show Buttons)
                $departments = Department::withoutGlobalScopes()
                    ->where('company_id', $session['company_id'])
                    ->get();
                $keyboard = Keyboard::make()->inline();
                foreach ($departments as $dept) {
                    $keyboard->row([Keyboard::inlineButton(['text' => $dept->name, 'callback_data' => 'dept_' . $dept->id])]);
                }

                // If no departments, ask to type ID or skip (dangerous, better to force selection)
                if ($departments->isEmpty()) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ No departments found! Please add departments in the web panel first."]);
                    Cache::forget("admin_job_wizard_{$chatId}");
                    return true;
                }

                $session['step'] = 'department_select'; // Special step waiting for callback
                $msg = "✅ Title saved.\n\n2️⃣ Select the **Department**:";
                $replyMarkup = $keyboard;
                break;

            // Note: 'department_select' is handled in handleCallback, not here in text handler

            case 'description':
                $session['data']['description'] = $text;
                $session['step'] = 'salary';
                $msg = "✅ Description saved.\n\n4️⃣ Enter **Salary Range** (e.g., $500 - $1000):";
                break;

            case 'salary':
                $session['data']['salary_range'] = $text;
                $session['step'] = 'location';
                $msg = "✅ Salary saved.\n\n5️⃣ Enter **Location** (e.g., Tashkent, Remote):";
                break;

            case 'location':
                $session['data']['location'] = $text;
                $session['step'] = 'deadline';
                $msg = "✅ Location saved.\n\n6️⃣ Enter **Deadline** (YYYY-MM-DD):";
                break;

            case 'deadline':
                // Basic Date Validation
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $text)) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ Invalid Date Format. Please use YYYY-MM-DD (e.g., 2024-12-31)."]);
                    return true; // Don't advance step
                }

                $session['data']['deadline'] = $text;

                // FINAL SAVE
                try {
                    $job = Recruitment::create([
                        'company_id' => $session['company_id'],
                        'department_id' => $session['data']['department_id'],
                        'title' => $session['data']['title'],
                        'description' => $session['data']['description'],
                        'salary_range' => $session['data']['salary_range'],
                        'location' => $session['data']['location'],
                        'deadline' => $session['data']['deadline'],
                        'status' => 'published',
                        'job_type' => 'Full-time', // Defaults
                        'schedule' => 'Standard',
                        'working_hours' => '9-6',
                        'experience' => 'Not specified',
                        'billing_type' => 'Standard'
                    ]);

                    Cache::forget("admin_job_wizard_{$chatId}");

                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🎉 <b>Job Posted Successfully!</b>\n\n" .
                            "<b>Title:</b> " . htmlspecialchars($job->title) . "\n" .
                            "<b>Link:</b> " . $job->telegram_apply_link,
                        'parse_mode' => 'HTML' // <--- CHANGE THIS
                    ]);
                } catch (\Exception $e) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "❌ Error saving job: " . $e->getMessage()]);
                }
                return true;
        }

        if (isset($msg)) {
            Cache::put("admin_job_wizard_{$chatId}", $session, now()->addMinutes(30));
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_markup' => $replyMarkup ?? null
            ]);
        }

        return true;
    }

    // New Helper to handle the Department Button Click
    public function handleWizardCallback($chatId, $data)
    {
        $session = Cache::get("admin_job_wizard_{$chatId}");
        if (!$session || $session['step'] !== 'department_select') return;

        if (Str::startsWith($data, 'dept_')) {
            $deptId = Str::after($data, 'dept_');
            $session['data']['department_id'] = $deptId;
            $session['step'] = 'description';

            Cache::put("admin_job_wizard_{$chatId}", $session, now()->addMinutes(30));

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Department selected.\n\n3️⃣ Now enter the **Job Description**:"
            ]);
        }
    }

    // =========================================================================
    // 👥 CANDIDATE REVIEW (/candidates)
    // =========================================================================

    public function listPendingCandidates($chatId, Employee $admin, $page = 1)
    {
        $perPage = 1; // Show 1 at a time for focus

        $candidates = Candidate::where('company_id', $admin->company_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($candidates->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ No pending candidates found."
            ]);
            return;
        }

        $candidate = $candidates->first();

        // Build Message
        $msg = "👤 **Candidate Review** ({$candidates->currentPage()}/{$candidates->lastPage()})\n\n" .
            "**Name:** {$candidate->full_name}\n" .
            "**Job:** " . ($candidate->recruitment->title ?? 'General') . "\n" .
            "**Phone:** {$candidate->phone}\n" .
            "**Email:** {$candidate->email}\n\n" .
            "**Cover Letter:**\n" . ($candidate->cover_letter ?? 'N/A');

        // Build Keyboard
        $keyboard = Keyboard::make()->inline();

        // Action Buttons
        $keyboard->row([
            Keyboard::inlineButton(['text' => '✅ Shortlist', 'callback_data' => "admin_cand_approve_{$candidate->id}"]),
            Keyboard::inlineButton(['text' => '❌ Reject', 'callback_data' => "admin_cand_reject_{$candidate->id}"])
        ]);

        // Download Resume Button
        if ($candidate->resume_path) {
            $keyboard->row([
                Keyboard::inlineButton(['text' => '📥 Download Resume', 'callback_data' => "admin_cand_resume_{$candidate->id}"])
            ]);
        }

        // Navigation Buttons
        $navRow = [];
        if ($candidates->currentPage() > 1) {
            $navRow[] = Keyboard::inlineButton(['text' => '⬅️ Prev', 'callback_data' => "admin_cand_page_" . ($page - 1)]);
        }
        if ($candidates->hasMorePages()) {
            $navRow[] = Keyboard::inlineButton(['text' => 'Next ➡️', 'callback_data' => "admin_cand_page_" . ($page + 1)]);
        }
        if (!empty($navRow)) $keyboard->row($navRow);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    public function handleCandidateAction($chatId, $action, $candidateId)
    {
        $candidate = Candidate::find($candidateId);
        if (!$candidate) return "Candidate not found.";

        if ($action === 'approve') {
            $candidate->update(['status' => 'shortlisted']);
            return "✅ Candidate **{$candidate->full_name}** shortlisted.";
        } elseif ($action === 'reject') {
            $candidate->update(['status' => 'rejected']);
            return "❌ Candidate **{$candidate->full_name}** rejected.";
        } elseif ($action === 'resume') {
            // Send the file
            if (Storage::exists($candidate->resume_path)) {
                Telegram::sendDocument([
                    'chat_id' => $chatId,
                    'document' => Storage::path($candidate->resume_path),
                    'caption' => "Resume: {$candidate->full_name}"
                ]);
                return null; // No text response needed
            }
            return "⚠️ Resume file not found on server.";
        }
    }

    // =========================================================================
    // 🔍 EMPLOYEE LOOKUP (/employee name)
    // =========================================================================

    public function lookupEmployee($chatId, Employee $admin, $query)
    {
        $results = Employee::where('company_id', $admin->company_id)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('job_title', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🔍 No employees found matching '{$query}'."]);
            return;
        }

        foreach ($results as $emp) {
            $statusEmoji = $emp->status === 'active' ? '🟢' : '🔴';
            $msg = "{$statusEmoji} **{$emp->full_name}**\n" .
                "💼 {$emp->job_title}\n" .
                "📞 {$emp->phone_number}\n" .
                "📧 {$emp->email}\n";

            // Add Attendance status if available (Optional)
            // $msg .= "📍 Last seen: ...";

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    // =========================================================================
    // 🌴 WHO'S OUT (/whosout)
    // =========================================================================

    public function checkWhosOut($chatId, Employee $admin, $date = null)
    {
        $targetDate = $date ? \Carbon\Carbon::parse($date) : now();

        $leaves = TimeOff::where('company_id', $admin->company_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->with('employee')
            ->get();

        if ($leaves->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Everyone is working on " . $targetDate->format('d M Y')
            ]);
            return;
        }

        $msg = "🌴 **Who's Out (" . $targetDate->format('d M') . ")**\n\n";
        foreach ($leaves as $leave) {
            $msg .= "👤 **{$leave->employee->full_name}**\n";
            $msg .= "   Until: {$leave->end_date->format('d M')}\n";
            $msg .= "   Reason: {$leave->reason}\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown'
        ]);
    }

    // =========================================================================
    // 🌴 NOTIFICATION SYSTEM FOR NEW HH.ru APPLICATIONS
    // =========================================================================

    public function notifyAdminsOfNewCandidate(Candidate $candidate)
    {
        // Find Admins/HRs in the same company
        $admins = Employee::where('company_id', $candidate->company_id)
            ->whereNotNull('telegram_chat_id')
            ->whereHas('user', function ($q) {
                $q->where('role', 'admin')->orWhere('role', 'hr'); // Pseudo-code for role check
            })
            ->get();

        $msg = "🔔 **New Application Received**\n\n" .
            "👤 **{$candidate->full_name}**\n" .
            "💼 " . ($candidate->recruitment->title ?? 'General Application') . "\n" .
            "source: " . ucfirst($candidate->source);

        foreach ($admins as $admin) {
            Telegram::sendMessage([
                'chat_id' => $admin->telegram_chat_id,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_markup' => Keyboard::make()->inline()->row([
                    Keyboard::inlineButton(['text' => '👀 Review Now', 'callback_data' => "admin_cand_page_1"])
                ])
            ]);
        }
    }

    // =========================================================================
    // 🌴 SHOW STATISTICS FOR HR MANAGERS
    // =========================================================================

    public function showStats($chatId, Employee $admin)
    {
        $today = now()->toDateString();

        // 1. Attendance Stats
        $present = Attendance::where('company_id', $admin->company_id)
            ->where('date', $today)
            ->count();

        $late = Attendance::where('company_id', $admin->company_id)
            ->where('date', $today)
            ->where('status', 'late')
            ->count();

        $totalEmployees = Employee::where('company_id', $admin->company_id)
            ->where('status', 'active')
            ->count();

        // 2. Recruitment Stats
        $openJobs = Recruitment::where('company_id', $admin->company_id)
            ->where('status', 'published')
            ->count();

        $newCandidates = Candidate::where('company_id', $admin->company_id)
            ->where('status', 'pending')
            ->count();

        $msg = "📊 **Daily Stats (" . now()->format('d M') . ")**\n\n" .
            "👥 **Workforce:**\n" .
            "• Present: {$present}/{$totalEmployees}\n" .
            "• Late: {$late}\n\n" .
            "💼 **Hiring:**\n" .
            "• Open Jobs: {$openJobs}\n" .
            "• New Candidates: {$newCandidates}";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown'
        ]);
    }

    // =========================================================================
    // 🌴 START ADDING EMPLOYEE VIA TELEGRAM BOT
    // =========================================================================

    public function startAddEmployeeWizard($chatId, Employee $admin)
    {
        $session = [
            'step' => 'name',
            'company_id' => $admin->company_id,
            'data' => []
        ];
        Cache::put("admin_add_emp_{$chatId}", $session, now()->addMinutes(15));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👤 **Add New Employee Wizard**\n\n1️⃣ Enter Full Name (e.g. John Doe):",
            'reply_markup' => Keyboard::make()->inline()->row([
                Keyboard::inlineButton(['text' => '❌ Cancel', 'callback_data' => 'admin_cancel_wizard'])
            ])
        ]);
    }


    public function handleAddEmployeeWizard($chatId, $text)
    {
        $session = Cache::get("admin_add_emp_{$chatId}");
        if (!$session) return false;

        switch ($session['step']) {
            // STEP 1: User just entered Name
            case 'name':
                $parts = explode(' ', trim($text), 2);
                $session['data']['first_name'] = $parts[0];
                $session['data']['last_name'] = $parts[1] ?? '';

                $session['step'] = 'phone'; // Next step is Phone
                $msg = "✅ Name saved.\n\n2️⃣ Enter **Phone Number** (e.g. 998901234567):";
                break;

            // STEP 2: User just entered Phone
            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $text);
                if (strlen($phone) < 9) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ Invalid phone. Try again:"]);
                    return true;
                }
                $session['data']['phone'] = $phone;

                $session['step'] = 'email'; // Next step is Email
                $msg = "✅ Phone saved.\n\n3️⃣ Enter **Email Address** (e.g. john@company.com):";
                break;

            // STEP 3: User just entered Email
            case 'email':
                if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "⚠️ Invalid Email. Try again:"]);
                    return true;
                }
                $session['data']['email'] = $text;

                // Show Departments
                $departments = Department::withoutGlobalScopes()
                    ->where('company_id', $session['company_id'])
                    ->get();

                $keyboard = Keyboard::make()->inline();
                foreach ($departments as $dept) {
                    $keyboard->row([Keyboard::inlineButton(['text' => $dept->name, 'callback_data' => 'emp_dept_' . $dept->id])]);
                }

                $session['step'] = 'dept_select'; // Wait for callback
                $msg = "✅ Email saved.\n\n4️⃣ Select **Department**:";
                $replyMarkup = $keyboard;
                break;

            // STEP 4 is handled by handleAddEmployeeCallback (Department selection)

            // STEP 5: User just entered Job Title
            case 'job_title':
                $session['data']['job_title'] = $text;
                $session['step'] = 'salary';
                $msg = "✅ Job Title saved.\n\n6️⃣ Enter **Salary** (Numbers only):";
                break;

            // STEP 6: User just entered Salary -> SAVE
            case 'salary':
                $salary = preg_replace('/[^0-9.]/', '', $text);
                $session['data']['salary'] = (float) $salary;

                try {
                    $emp = Employee::create([
                        'company_id' => $session['company_id'],
                        'first_name' => $session['data']['first_name'],
                        'last_name' => $session['data']['last_name'],
                        'phone_number' => $session['data']['phone'],
                        'email' => $session['data']['email'], // Email is now here
                        'department_id' => $session['data']['department_id'],
                        'job_title' => $session['data']['job_title'],
                        'salary' => $session['data']['salary'],
                        'hire_date' => now(),
                        'status' => 'active',
                        'address' => null,
                    ]);

                    Cache::forget("admin_add_emp_{$chatId}");

                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "🎉 **Employee Added!**\n\n👤 {$emp->first_name} {$emp->last_name}",
                        'parse_mode' => 'Markdown'
                    ]);
                } catch (\Exception $e) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "❌ Error: " . $e->getMessage()]);
                }
                return true;
        }

        // Save session & Send Message
        Cache::put("admin_add_emp_{$chatId}", $session, now()->addMinutes(15));

        if (isset($msg)) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_markup' => $replyMarkup ?? null
            ]);
        }
        return true;
    }

    public function handleAddEmployeeCallback($chatId, $data)
    {
        $session = Cache::get("admin_add_emp_{$chatId}");
        if (!$session || $session['step'] !== 'dept_select') return;

        if (Str::startsWith($data, 'emp_dept_')) {
            $deptId = Str::after($data, 'emp_dept_');
            $session['data']['department_id'] = $deptId;
            $session['step'] = 'job_title';

            Cache::put("admin_add_emp_{$chatId}", $session, now()->addMinutes(15));

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Department selected.\n\n4️⃣ Enter **Job Title** (e.g. Sales Manager):"
            ]);
        }
    }

    // =========================================================================
    // 📋 PENDING REVIEWS (/reviews)
    // =========================================================================

    public function showPendingReviews($chatId, Employee $admin)
    {
        // Find employees on Probation
        $probationers = Employee::where('company_id', $admin->company_id)
            ->where('status', 'Probation') // Ensure 'Probation' matches your DB enum (case sensitive)
            ->orderBy('hire_date', 'asc')
            ->get();

        if ($probationers->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ No pending probation reviews found."
            ]);
            return;
        }

        $msg = "📋 **Pending Probation Reviews**\n\n";

        foreach ($probationers as $emp) {
            // Calculate Probation End (e.g., 3 months after hire)
            $probationEnd = $emp->hire_date->copy()->addMonths(3);
            $daysLeft = now()->diffInDays($probationEnd, false);

            $icon = $daysLeft < 0 ? '🔴' : ($daysLeft < 7 ? '🟠' : '🟢');
            $status = $daysLeft < 0 ? "Overdue by " . abs((int)$daysLeft) . " days" : "Ends in " . (int)$daysLeft . " days";

            $msg .= "{$icon} **{$emp->first_name} {$emp->last_name}**\n";
            $msg .= "   Job: {$emp->job_title}\n";
            $msg .= "   {$status} ({$probationEnd->format('d M')})\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown'
        ]);
    }
}
