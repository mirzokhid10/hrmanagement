<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::getWebhookUpdate();

        if (!$update->has('message')) {
            return response('OK');
        }

        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $username = $message->getChat()->getUsername();
        $text = $message->getText();

        // 1. Handle /start
        if ($text === '/start') {
            $this->handleStartCommand($chatId);
            return response('OK');
        }

        // 2. Handle Contact Sharing (Registration)
        if ($message->has('contact')) {
            $this->handleContactSharing($chatId, $message->getContact());
            return response('OK');
        }

        // 3. Handle Menu Buttons (Check In / Check Out)
        if ($text === '📍 Check In') {
            $this->askForLocation($chatId, 'check_in');
            return response('OK');
        }

        if ($text === '👋 Check Out') {
            $this->askForLocation($chatId, 'check_out');
            return response('OK');
        }

        // 4. Handle Location Data (The Verification)
        if ($message->has('location')) {
            $this->handleLocationReceived($chatId, $message->getLocation());
            return response('OK');
        }

        return response('OK');
    }

    // -------------------------------------------------------------------------
    // 🟢 PHASE 1: REGISTRATION
    // -------------------------------------------------------------------------

    protected function handleStartCommand($chatId)
    {
        $employee = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('telegram_chat_id', $chatId)
            ->first();

        if ($employee) {
            $this->sendMainMenu($chatId, "Welcome back, {$employee->first_name}! Ready for work?");
        } else {
            // Button to share contact
            $keyboard = Keyboard::make()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->row([
                    Keyboard::button([
                        'text' => '📱 Share My Phone Number',
                        'request_contact' => true
                    ])
                ]);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "Assalomu Alaykum! \n\nPlease click the button below to verify your identity.",
                'reply_markup' => $keyboard
            ]);
        }
    }

    protected function handleContactSharing($chatId, $contact)
    {
        // Normalize phone: remove + and spaces
        $normalizedPhone = preg_replace('/[^0-9]/', '', $contact->getPhoneNumber());

        $employee = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->whereRaw("REGEXP_REPLACE(phone_number, '[^0-9]', '') = ?", [$normalizedPhone])
            ->first();

        if ($employee) {
            $employee->update(['telegram_chat_id' => $chatId]);
            $employee->update(['telegram_username' => $chatId]);
            $this->sendMainMenu($chatId, "✅ Verified! Welcome, {$employee->first_name}.");
        } else {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Phone number not found. Please contact HR.",
                'reply_markup' => Keyboard::remove()
            ]);
        }
    }

    protected function sendMainMenu($chatId, $message)
    {
        // The Permanent Menu Buttons
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setPersistent(true) // Always visible
            ->row([
                Keyboard::button(['text' => '📍 Check In']),
            ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $keyboard
        ]);
    }

    // -------------------------------------------------------------------------
    // 📍 PHASE 2: ATTENDANCE LOGIC
    // -------------------------------------------------------------------------

    protected function askForLocation($chatId, $action)
    {
        // Save the intended action (check_in or check_out) in Cache for 5 minutes
        Cache::put("attendance_action_{$chatId}", $action, 300);

        // Ask user to send location button
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => '📍 Send Current Location',
                    'request_location' => true // This triggers the location popup
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
        // 1. Retrieve the action (Check In or Out)
        $action = Cache::get("attendance_action_{$chatId}");

        if (!$action) {
            $this->sendMainMenu($chatId, "⚠️ Session expired. Please click Check In/Out again.");
            return;
        }

        // 2. Find the Employee
        $employee = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (!$employee) return;

        // 3. Find the nearest Office Location for THIS company
        // We look for any active office in the employee's company
        $offices = OfficeLocation::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('company_id', $employee->company_id)
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

        // 4. Validate Distance
        if (!$allowedOffice) {
            $this->sendMainMenu($chatId, "❌ **Access Denied**\n\nYou are not within the office range.\nDistance checked against " . $offices->count() . " locations.");
            return;
        }

        // 5. Save Attendance
        // Note: In a real app, you'd check if they already checked in today.
        // For Day 5 MVP, we just log it.

        // You might want to create a full Attendance model record here.
        // For now, let's log to the attendance_logs table you showed me.

        // If it's a Check In, we create a new Attendance record
        // If Check Out, we update the last one.
        // (Simplified for this snippet - assuming we just want to log success for now)

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ **Success!**\n\nAction: " . strtoupper(str_replace('_', ' ', $action)) . "\nOffice: {$allowedOffice->name}\nTime: " . now()->format('H:i'),
        ]);

        // Clear cache and show menu
        Cache::forget("attendance_action_{$chatId}");
        $this->sendMainMenu($chatId, "What would you like to do next?");
    }

    // Helper: Haversine Formula to calculate distance in meters
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
