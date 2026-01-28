<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\OfficeWifiNetwork;
use App\Services\Telegram\Helpers\TelegramKeyboardBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;

class EmployeeHandler
{
    /**
     * Send main menu for employees
     */
    public function sendMainMenu(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];
        $greeting = "👋 Welcome back, {$employee->first_name}!";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $greeting,
            'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
        ]);
    }

    /**
     * Handle text button presses
     */
    public function handleText(string $text, int $chatId, array $auth): void
    {
        match ($text) {
            '📍 Check In' => $this->startCheckIn($chatId, $auth),
            '👋 Check Out' => $this->startCheckOut($chatId, $auth),
            '📊 My Stats' => $this->showMyStats($chatId, $auth),
            '🌴 My Leaves' => $this->showMyLeaves($chatId, $auth),
            '🏠 Main Menu', '❌ Cancel' => $this->sendMainMenu($chatId, $auth),
            default => $this->sendMainMenu($chatId, $auth)
        };
    }

    /**
     * Handle callback queries
     */
    public function handleCallback($callback, int $chatId, array $auth): void
    {
        $data = $callback->getData();

        if ($data === 'emp_verify_wifi') {
            $this->sendWifiVerificationLink($chatId, $auth);
        }
    }

    /**
     * Start check-in process
     */
    protected function startCheckIn(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];
        $today = now()->toDateString();

        // Check if already checked in
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in_time) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ You already checked in today at " . $existing->check_in_time->format('H:i'),
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            return;
        }

        // Store action in cache
        Cache::put("attendance_action_{$chatId}", 'check_in', 300);

        // Request location
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📍 *Check In*\n\nPlease share your current location to verify you're at the office.",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::requestLocation()
        ]);
    }

    /**
     * Start check-out process
     */
    protected function startCheckOut(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];
        $today = now()->toDateString();

        // Check if checked in today
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ You haven't checked in today. Please check in first.",
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            return;
        }

        if ($attendance->check_out_time) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ You already checked out today at " . $attendance->check_out_time->format('H:i'),
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            return;
        }

        // Store action in cache
        Cache::put("attendance_action_{$chatId}", 'check_out', 300);

        // Request location
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 *Check Out*\n\nPlease share your location to complete check-out.",
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::requestLocation()
        ]);
    }

    /**
     * Handle location received from user
     */
    public function handleLocation($location, int $chatId, array $auth): void
    {
        $action = Cache::get("attendance_action_{$chatId}");

        if (!$action) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Session expired. Please click Check In or Check Out again.",
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            return;
        }

        $employee = $auth['employee'];
        $userLat = $location->getLatitude();
        $userLon = $location->getLongitude();

        // Verify location
        $locationResult = $this->verifyLocation($employee->company_id, $userLat, $userLon);

        if (!$locationResult['verified']) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $locationResult['message'],
                'parse_mode' => 'Markdown',
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            Cache::forget("attendance_action_{$chatId}");
            return;
        }

        // Process attendance based on action
        if ($action === 'check_in') {
            $this->processCheckIn($chatId, $auth, $userLat, $userLon, $locationResult);
        } else {
            $this->processCheckOut($chatId, $auth, $userLat, $userLon);
        }
    }

    /**
     * Process check-in
     */
    protected function processCheckIn(int $chatId, array $auth, float $lat, float $lon, array $locationResult): void
    {
        $employee = $auth['employee'];
        $today = now()->toDateString();
        $checkInTime = now();

        // Determine status (late or on-time)
        $expectedTime = now()->setTime(9, 0); // 9:00 AM default
        $lateThreshold = $expectedTime->copy()->addMinutes(15); // 9:15 AM

        $status = $checkInTime->greaterThan($lateThreshold) ? 'late' : 'present';

        // Create attendance record
        $attendance = Attendance::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'date' => $today,
            'check_in_time' => $checkInTime,
            'check_in_lat' => $lat,
            'check_in_lon' => $lon,
            'is_location_verified' => true,
            'status' => $status,
        ]);

        // Build success message
        $statusEmoji = $status === 'late' ? '⚠️' : '✅';
        $message = "{$statusEmoji} *Check-In Successful!*\n\n";
        $message .= "🕐 Time: " . $checkInTime->format('H:i') . "\n";
        $message .= "📅 Date: " . $checkInTime->format('d M Y') . "\n";
        $message .= "📍 Location: {$locationResult['office']->name}\n";

        if ($status === 'late') {
            $message .= "\n⚠️ *Status: Late Arrival*";
        }

        // Check if WiFi verification is required
        $wifiNetworks = OfficeWifiNetwork::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->exists();

        if ($wifiNetworks) {
            $message .= "\n\n📶 *Next Step:* Please verify your WiFi connection.";

            // Generate verification link (ngrok URL)
            $verificationUrl = config('app.url') . "/telegram/verify-wifi/{$attendance->id}";

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => \Telegram\Bot\Keyboard\Keyboard::make()->inline()->row([
                    \Telegram\Bot\Keyboard\Keyboard::inlineButton([
                        'text' => '📶 Verify WiFi Connection',
                        'url' => $verificationUrl
                    ])
                ])
            ]);
        } else {
            // No WiFi verification needed
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
        }

        Cache::forget("attendance_action_{$chatId}");
    }

    /**
     * Process check-out
     */
    protected function processCheckOut(int $chatId, array $auth, float $lat, float $lon): void
    {
        $employee = $auth['employee'];
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No check-in record found for today.",
                'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
            ]);
            return;
        }

        // Update check-out
        $attendance->update([
            'check_out_time' => now(),
            'check_out_lat' => $lat,
            'check_out_lon' => $lon,
        ]);

        // Calculate work hours
        $duration = $attendance->check_in_time->diffInMinutes($attendance->check_out_time);
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        $attendance->update(['work_hours' => round($duration / 60, 2)]);

        $message = "👋 *Check-Out Successful!*\n\n";
        $message .= "🕐 Time: " . now()->format('H:i') . "\n";
        $message .= "⏱️ Work Duration: {$hours}h {$minutes}m\n\n";
        $message .= "Have a great evening! 🌙";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
        ]);

        Cache::forget("attendance_action_{$chatId}");
    }

    /**
     * Verify if location is within office radius
     */
    protected function verifyLocation(int $companyId, float $lat, float $lon): array
    {
        $offices = OfficeLocation::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        if ($offices->isEmpty()) {
            return [
                'verified' => false,
                'message' => '❌ *No Office Locations Configured*\n\nPlease contact your HR manager to set up office locations.',
            ];
        }

        foreach ($offices as $office) {
            $distance = $this->calculateDistance($lat, $lon, $office->latitude, $office->longitude);

            if ($distance <= $office->radius_meters) {
                return [
                    'verified' => true,
                    'message' => "✅ Location verified: {$office->name}",
                    'office' => $office,
                    'distance' => round($distance, 2),
                ];
            }
        }

        // Find nearest office for feedback
        $nearest = $offices->sortBy(function ($office) use ($lat, $lon) {
            return $this->calculateDistance($lat, $lon, $office->latitude, $office->longitude);
        })->first();

        $distance = $this->calculateDistance($lat, $lon, $nearest->latitude, $nearest->longitude);

        return [
            'verified' => false,
            'message' => "❌ *You are not at the office location.*\n\n" .
                "📍 Nearest office: *{$nearest->name}*\n" .
                "📏 Distance: " . round($distance) . " meters away\n" .
                "✅ Required: within {$nearest->radius_meters}m",
        ];
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Show employee's attendance statistics
     */
    protected function showMyStats(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];
        $currentMonth = now()->format('Y-m');

        $attendances = Attendance::where('employee_id', $employee->id)
            ->where('date', 'like', $currentMonth . '%')
            ->get();

        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $totalDays = $attendances->count();

        $message = "📊 *Your Attendance Stats*\n";
        $message .= "_Month: " . now()->format('F Y') . "_\n\n";
        $message .= "✅ Present Days: {$present}\n";
        $message .= "⚠️ Late Days: {$late}\n";
        $message .= "📅 Total Days: {$totalDays}\n";

        if ($totalDays > 0) {
            $rate = round(($present / $totalDays) * 100, 1);
            $message .= "\n🎯 Attendance Rate: {$rate}%";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
        ]);
    }

    /**
     * Show employee's leave balance
     */
    protected function showMyLeaves(int $chatId, array $auth): void
    {
        $employee = $auth['employee'];

        // This is a placeholder - implement based on your TimeOff model
        $message = "🌴 *Your Leave Balance*\n\n";
        $message .= "📅 Annual Leave: 15 days remaining\n";
        $message .= "🏥 Sick Leave: 10 days remaining\n";
        $message .= "\n_Use the web app to request time off._";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => TelegramKeyboardBuilder::employeeMainMenu()
        ]);
    }

    /**
     * Send WiFi verification link
     */
    protected function sendWifiVerificationLink(int $chatId, array $auth): void
    {
        $today = now()->toDateString();
        $employee = $auth['employee'];

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No attendance record found for today."
            ]);
            return;
        }

        $verificationUrl = config('app.url') . "/telegram/verify-wifi/{$attendance->id}";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📶 Click the button below to verify your WiFi connection:",
            'reply_markup' => \Telegram\Bot\Keyboard\Keyboard::make()->inline()->row([
                \Telegram\Bot\Keyboard\Keyboard::inlineButton([
                    'text' => '📶 Verify WiFi Now',
                    'url' => $verificationUrl
                ])
            ])
        ]);
    }
}
