<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Services\TelegramCandidateService;
use App\Services\TelegramAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; // <--- IMPORTANT
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        TelegramCandidateService $candidateService,
        TelegramAdminService $adminService
    ) {
        // 🔍 DEBUG: Log that we received a request
        Log::info('Telegram Webhook Received', $request->all());

        try {
            $update = Telegram::getWebhookUpdate();

            // 1. Safety Check
            if (!$update->has('message') && !$update->has('callback_query')) {
                return response('OK');
            }

            // 2. Identify User
            if ($update->has('callback_query')) {
                $chatId = $update->getCallbackQuery()->getMessage()->getChat()->getId();
            } else {
                $chatId = $update->getMessage()->getChat()->getId();
            }

            // 3. Atomic Block
            $lock = Cache::lock("bot_processing_{$chatId}", 5);
            if (!$lock->get()) return response('OK');

            try {
                // Find Logged in Employee
                $employee = Employee::withoutGlobalScopes()
                    ->where('telegram_chat_id', $chatId)
                    ->first();


                // Check Admin Status
                if ($employee) {
                    // 1. Manually fetch Department Name (Ignoring Tenant Scope)
                    $deptName = null;
                    if ($employee->department_id) {
                        $deptName = \App\Models\Department::withoutGlobalScopes()
                            ->where('id', $employee->department_id)
                            ->value('name'); // Directly get the name string
                    }

                    // 2. Check User Admin Status
                    $userIsAdmin = false;
                    if ($employee->user_id) {
                        // Load User manually to be safe
                        $user = \App\Models\User::find($employee->user_id);
                        $userIsAdmin = $user ? $user->isAdmin() : false;
                    }

                    // 4. Final Decision
                    $isAdmin = in_array($deptName, ['HR', 'Human Resources']) || $userIsAdmin;
                }

                // ====================================================
                // 🖱 CALLBACKS
                // ====================================================
                if ($update->has('callback_query')) {
                    $callback = $update->getCallbackQuery();
                    $data = $callback->getData();
                    Telegram::answerCallbackQuery(['callback_query_id' => $callback->getId()]);

                    // Admin Callbacks
                    if (Str::startsWith($data, 'admin_')) {
                        if (!$isAdmin) return response('OK'); // Silent fail for non-admins

                        if ($data === 'admin_cancel_wizard') {
                            Cache::forget("admin_job_wizard_{$chatId}");
                            Telegram::sendMessage(['chat_id' => $chatId, 'text' => '❌ Job posting cancelled.']);
                        } elseif (Str::startsWith($data, 'admin_cand_page_')) {
                            $page = (int) Str::after($data, 'admin_cand_page_');
                            $adminService->listPendingCandidates($chatId, $employee, $page);
                        } elseif (Str::startsWith($data, 'admin_cand_')) {
                            $parts = explode('_', $data);
                            if (count($parts) >= 4) {
                                $action = $parts[2];
                                $id = $parts[3];
                                $res = $adminService->handleCandidateAction($chatId, $action, $id);
                                if ($res) Telegram::sendMessage(['chat_id' => $chatId, 'text' => $res, 'parse_mode' => 'Markdown']);
                                if ($action !== 'resume') $adminService->listPendingCandidates($chatId, $employee);
                            }
                        }
                    } elseif (Str::startsWith($data, 'emp_dept_')) {
                        $adminService->handleAddEmployeeCallback($chatId, $data);
                    }

                    // Candidate Callbacks
                    if (Str::startsWith($data, 'lang_')) $candidateService->handleLanguageSelection($chatId, Str::after($data, 'lang_'));
                    if (Str::startsWith($data, 'select_company_')) $candidateService->listJobsForCompany($chatId, Str::after($data, 'select_company_'), Cache::get("user_lang_{$chatId}", 'en'));
                    if (Str::startsWith($data, 'select_job_')) $candidateService->showJobPreview($chatId, Str::after($data, 'select_job_'), Cache::get("user_lang_{$chatId}", 'en'));
                    if (Str::startsWith($data, 'start_form_')) {
                        $msg = $candidateService->startApplicationForm($chatId, Str::after($data, 'start_form_'));
                        Telegram::sendMessage(['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'Markdown']);
                    }
                    if ($data === 'skip_cover') $candidateService->handleConversation(['text' => 'skip_cover', 'is_callback' => true], $chatId);
                }

                // ====================================================
                // 📩 TEXT MESSAGES
                // ====================================================
                elseif ($update->has('message')) {
                    $messageObj = $update->getMessage();
                    $text = $messageObj->getText() ?? '';

                    $messageArray = [
                        'text' => $text,
                        'contact' => $messageObj->has('contact') ? $messageObj->getContact()->toArray() : null,
                        'document' => $messageObj->has('document') ? $messageObj->getDocument()->toArray() : null,
                    ];

                    // 1. ADMIN COMMANDS (Priority 1)
                    if ($isAdmin) {

                        if ($text === '/stats') {
                            $adminService->showStats($chatId, $employee);
                            return response('OK');
                        }
                        if ($text === '/addemployee') {
                            $adminService->startAddEmployeeWizard($chatId, $employee);
                            return response('OK');
                        }
                        if ($text === '/reviews') {
                            $adminService->showPendingReviews($chatId, $employee);
                            return response('OK');
                        }
                        if (Cache::has("admin_add_emp_{$chatId}")) {
                            $adminService->handleAddEmployeeWizard($chatId, $text);
                            return response('OK');
                        }

                        if (Cache::has("admin_job_wizard_{$chatId}")) {
                            $adminService->handleJobWizard($chatId, $text);
                            return response('OK');
                        }
                        if ($text === '/postjob') {
                            $adminService->startJobWizard($chatId, $employee);
                            return response('OK');
                        }
                        if ($text === '/candidates') {
                            $adminService->listPendingCandidates($chatId, $employee);
                            return response('OK');
                        }
                        if (Str::startsWith($text, '/employee ')) {
                            $query = Str::after($text, '/employee ');
                            $adminService->lookupEmployee($chatId, $employee, $query);
                            return response('OK');
                        }
                        if (Str::startsWith($text, '/whosout')) {
                            $date = Str::after($text, '/whosout ');
                            $date = trim($date) === '' || $date === '/whosout' ? null : $date;
                            $adminService->checkWhosOut($chatId, $employee, $date);
                            return response('OK');
                        }
                    }

                    // 2. CANDIDATE & REGULAR LOGIC
                    if (Str::startsWith($text, '/start ') && strlen($text) > 7) {
                        $candidateService->handleStartCommand($chatId, $text);
                    } elseif (Cache::has("candidate_session_{$chatId}")) {
                        $candidateService->handleConversation($messageArray, $chatId);
                    } elseif ($employee) {
                        $this->handleEmployeeLogic($employee, $text, $messageObj, $chatId);
                    } elseif ($messageObj->has('contact')) {
                        // Registration Logic
                        $contact = $messageObj->getContact();
                        if ($contact->getUserId() !== $chatId) {
                            Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🚫 Send your own contact."]);
                        } else {
                            $phone = preg_replace('/[^0-9]/', '', $contact->getPhoneNumber());
                            $emp = Employee::withoutGlobalScopes()
                                ->whereRaw("REPLACE(phone_number, '+', '') LIKE '%" . substr($phone, -9) . "'")
                                ->first();

                            if ($emp) {
                                $emp->update([
                                    'telegram_chat_id' => $chatId,
                                    'telegram_username' => $messageObj->getChat()->getUsername()
                                ]);
                                Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🎉 Connected as {$emp->first_name}!"]);
                                $this->sendMainMenu($chatId, "Menu:");
                            } else {
                                Telegram::sendMessage(['chat_id' => $chatId, 'text' => "🚫 Number not found in database."]);
                            }
                        }
                    } else {
                        // Fallback
                        $this->sendStartWithRegister($chatId);
                    }
                }
            } finally {
                $lock->release();
            }
        } catch (\Throwable $e) { // Catch Throwable to catch ALL errors including syntax
            // 📝 LOG ERROR TO SERVER
            Log::error('Telegram Fatal Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // 🚨 SEND ERROR TO YOU (DEVELOPER)
            try {
                $debugChatId = '632710453'; // Your ID
                $errorMsg = "⚠️ **Backend Error**\n\n" .
                    "📄 " . basename($e->getFile()) . ":" . $e->getLine() . "\n" .
                    "❌ " . $e->getMessage();

                Telegram::sendMessage([
                    'chat_id' => $debugChatId,
                    'text' => substr($errorMsg, 0, 4000),
                    'parse_mode' => 'Markdown'
                ]);
            } catch (\Exception $ex) {
                Log::error('Could not send Telegram error report.');
            }
        }

        return response('OK');
    }

    // =========================================================================
    // 🟢 HELPER METHODS (Must be inside class)
    // =========================================================================

    protected function sendStartWithRegister($chatId)
    {
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => '📱 Telefon raqamni yuborish (Login)',
                    'request_contact' => true
                ])
            ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Assalomu Alaykum! \n\nAgar siz xodim bo'lsangiz, **Login** tugmasini bosing.\nAgar ish qidirayotgan bo'lsangiz /start buyrug'ini yuboring.",
            'reply_markup' => $keyboard
        ]);
    }

    protected function handleEmployeeLogic($employee, $text, $messageObj, $chatId)
    {
        if ($text === '📍 Check In') {
            $this->askForLocation($chatId, 'check_in');
            return;
        }
        if ($text === '👋 Check Out') {
            $this->askForLocation($chatId, 'check_out');
            return;
        }
        if ($messageObj->has('location')) {
            $this->handleLocationReceived($chatId, $messageObj->getLocation());
            return;
        }
        $this->sendMainMenu($chatId, "Welcome back, {$employee->first_name}.");
    }

    protected function sendMainMenu($chatId, $message)
    {
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setPersistent(true)
            ->row([
                Keyboard::button(['text' => '📍 Check In']),
                Keyboard::button(['text' => '👋 Check Out']),
            ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $keyboard
        ]);
    }

    protected function askForLocation($chatId, $action)
    {
        Cache::put("attendance_action_{$chatId}", $action, 300);
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => '📍 Send Current Location',
                    'request_location' => true
                ])
            ])
            ->row([
                Keyboard::button(['text' => '🔙 Cancel'])
            ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Please confirm your location to " . str_replace('_', ' ', $action) . ".",
            'reply_markup' => $keyboard
        ]);
    }

    protected function handleLocationReceived($chatId, $location)
    {
        $action = Cache::get("attendance_action_{$chatId}");
        if (!$action) {
            $this->sendMainMenu($chatId, "⚠️ Session expired. Please click Check In/Out again.");
            return;
        }

        $employee = Employee::where('telegram_chat_id', $chatId)->first();
        if (!$employee) return;

        // Find nearest office
        $offices = OfficeLocation::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->get();

        $userLat = $location->getLatitude();
        $userLon = $location->getLongitude();
        $allowedOffice = null;

        foreach ($offices as $office) {
            $distance = $this->calculateDistance($userLat, $userLon, $office->latitude, $office->longitude);
            if ($distance <= $office->radius_meters) {
                $allowedOffice = $office;
                break;
            }
        }

        if (!$allowedOffice) {
            $this->sendMainMenu($chatId, "❌ **Access Denied**\n\nYou are not within the office range.");
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ **Success!**\n\nAction: " . strtoupper(str_replace('_', ' ', $action)) . "\nOffice: {$allowedOffice->name}\nTime: " . now()->format('H:i'),
        ]);

        Cache::forget("attendance_action_{$chatId}");
        $this->sendMainMenu($chatId, "Attendance recorded.");
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
