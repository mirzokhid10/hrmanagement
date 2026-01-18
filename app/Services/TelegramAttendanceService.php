<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\BotSetting;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\OfficeWifiNetwork;
use App\Models\TelegramUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramAttendanceService
{
    /**
     * Process check-in with location verification
     */
    public function processCheckIn(
        Employee $telegramUser,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $ipAddress = null
    ): array {
        $employee = $telegramUser->employee;
        if (!$employee) {
            return [
                'success' => false,
                'message' => '❌ Employee profile not found. Please contact HR.',
            ];
        }

        $company = $employee->company;
        $settings = BotSetting::getForCompany($company->id);

        // Check if attendance is enabled
        if (!$settings->attendance_enabled) {
            return [
                'success' => false,
                'message' => '❌ Attendance system is currently disabled.',
            ];
        }

        // Check if already checked in today
        $today = Carbon::now($settings->timezone)->toDateString();
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return [
                'success' => false,
                'message' => "✅ You already checked in today at {$existingAttendance->check_in_time->format('H:i')}",
            ];
        }

        // Verify location if required
        $locationVerified = false;
        $verificationMessage = [];

        if ($settings->location_verification_required) {
            if (!$latitude || !$longitude) {
                return [
                    'success' => false,
                    'message' => '📍 Please share your location to check in.',
                    'requires_location' => true,
                ];
            }

            $locationResult = $this->verifyLocation($company->id, $latitude, $longitude);
            $locationVerified = $locationResult['verified'];
            $verificationMessage[] = $locationResult['message'];

            if (!$locationVerified) {
                $this->logAttempt($employee, 'check_in_attempt', 'failed', $locationResult['message'], [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);

                return [
                    'success' => false,
                    'message' => $locationResult['message'],
                ];
            }
        }

        // Create or update attendance record
        $attendance = Attendance::updateOrCreate(
            [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
                'date' => $today,
            ],
            [
                'check_in_time' => Carbon::now($settings->timezone),
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'location_verified' => $locationVerified,
                'status' => $this->determineStatus($settings, Carbon::now($settings->timezone)),
            ]
        );

        // Store IP for WiFi verification if required
        if ($ipAddress) {
            $attendance->check_in_ip_address = $ipAddress;
            $attendance->save();
        }

        $this->logAttempt($employee, 'check_in_attempt', 'success', 'Check-in successful', [
            'attendance_id' => $attendance->id,
            'location_verified' => $locationVerified,
        ]);

        $checkInTime = $attendance->check_in_time->format('H:i');
        $statusEmoji = $attendance->status === 'late' ? '⚠️' : '✅';

        $message = "{$statusEmoji} Check-in successful!\n";
        $message .= "🕐 Time: {$checkInTime}\n";
        $message .= "📅 Date: " . Carbon::parse($today)->format('d M Y') . "\n";

        if ($attendance->status === 'late') {
            $message .= "⚠️ Status: Late\n";
        }

        // If WiFi verification is required, return with verification link
        if ($settings->wifi_verification_required && !$attendance->wifi_verified) {
            return [
                'success' => true,
                'message' => $message,
                'requires_wifi' => true,
                'attendance_id' => $attendance->id,
            ];
        }

        return [
            'success' => true,
            'message' => $message . "\n" . implode("\n", $verificationMessage),
        ];
    }

    /**
     * Process WiFi verification
     */
    public function processWifiVerification(int $attendanceId, string $ipAddress): array
    {
        $attendance = Attendance::find($attendanceId);
        if (!$attendance) {
            return [
                'success' => false,
                'message' => '❌ Attendance record not found.',
            ];
        }

        $company = $attendance->employee->company;
        $wifiResult = $this->verifyWifi($company->id, $ipAddress);

        $attendance->update([
            'check_in_ip_address' => $ipAddress,
            'wifi_verified' => $wifiResult['verified'],
        ]);

        $this->logAttempt(
            $attendance->employee,
            'wifi_verification',
            $wifiResult['verified'] ? 'success' : 'failed',
            $wifiResult['message'],
            ['attendance_id' => $attendanceId, 'ip_address' => $ipAddress]
        );

        return $wifiResult;
    }

    /**
     * Process check-out
     */
    public function processCheckOut(
        Employee $telegramUser,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        $employee = $telegramUser->employee;
        if (!$employee) {
            return [
                'success' => false,
                'message' => '❌ Employee profile not found.',
            ];
        }

        $company = $employee->company;
        $settings = BotSetting::getForCompany($company->id);
        $today = Carbon::now($settings->timezone)->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return [
                'success' => false,
                'message' => '❌ You haven\'t checked in today.',
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'success' => false,
                'message' => "✅ You already checked out at {$attendance->check_out_time->format('H:i')}",
            ];
        }

        // Update check-out
        $attendance->update([
            'check_out_time' => Carbon::now($settings->timezone),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ]);

        $this->logAttempt($employee, 'check_out_attempt', 'success', 'Check-out successful', [
            'attendance_id' => $attendance->id,
        ]);

        $duration = $attendance->work_duration;
        $message = "👋 Check-out successful!\n";
        $message .= "🕐 Time: " . $attendance->check_out_time->format('H:i') . "\n";
        $message .= "⏱️ Work duration: {$duration}\n";
        $message .= "Have a great evening!";

        return [
            'success' => true,
            'message' => $message,
        ];
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
                'message' => '❌ No office locations configured. Please contact your HR manager.',
            ];
        }

        foreach ($offices as $office) {
            $distance = $office->calculateDistance($lat, $lon);

            if ($office->isWithinRadius($lat, $lon)) {
                return [
                    'verified' => true,
                    'message' => "📍 Location verified: {$office->name}",
                    'office' => $office,
                    'distance' => round($distance, 2),
                ];
            }
        }

        // Find nearest office for feedback
        $nearest = $offices->sortBy(function ($office) use ($lat, $lon) {
            return $office->calculateDistance($lat, $lon);
        })->first();

        $distance = $nearest->calculateDistance($lat, $lon);

        return [
            'verified' => false,
            'message' => "❌ You are not at the office location.\n" .
                "📍 Nearest office: {$nearest->name}\n" .
                "📏 Distance: " . round($distance, 2) . "m away\n" .
                "✅ Required: within {$nearest->radius_meters}m",
        ];
    }

    /**
     * Verify if IP is within office WiFi network
     */
    protected function verifyWifi(int $companyId, string $ipAddress): array
    {
        $networks = OfficeWifiNetwork::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        if ($networks->isEmpty()) {
            return [
                'verified' => false,
                'message' => '❌ No WiFi networks configured.',
            ];
        }

        foreach ($networks as $network) {
            if ($network->isIpInRange($ipAddress)) {
                $networkName = $network->network_name ?: 'Office WiFi';
                return [
                    'verified' => true,
                    'message' => "📶 WiFi verified: {$networkName}\n✅ Check-in complete!",
                    'network' => $network,
                ];
            }
        }

        return [
            'verified' => false,
            'message' => "❌ You are not connected to office WiFi.\n" .
                "IP: {$ipAddress}\n" .
                "Please connect to office WiFi and try again.",
        ];
    }

    /**
     * Determine attendance status based on check-in time
     */
    protected function determineStatus(BotSetting $settings, Carbon $checkInTime): string
    {
        $allowedTime = Carbon::parse($settings->check_in_end_time);
        $lateTime = $allowedTime->copy()->addMinutes($settings->late_threshold_minutes);

        if ($checkInTime->lessThanOrEqualTo($allowedTime)) {
            return 'present';
        } elseif ($checkInTime->lessThanOrEqualTo($lateTime)) {
            return 'late';
        }

        return 'late';
    }

    /**
     * Log attendance attempt
     */
    protected function logAttempt(
        Employee $employee,
        string $action,
        string $status,
        string $message,
        array $metadata = []
    ): void {
        AttendanceLog::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get today's attendance summary
     */
    public function getTodaySummary(int $companyId): array
    {
        $today = Carbon::today();

        $totalEmployees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        $attendances = Attendance::where('company_id', $companyId)
            ->where('date', $today)
            ->get();

        $present = $attendances->whereNotNull('check_in_time')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $totalEmployees - $present;

        return [
            'total_employees' => $totalEmployees,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attendance_rate' => $totalEmployees > 0 ? round(($present / $totalEmployees) * 100, 1) : 0,
        ];
    }
}
