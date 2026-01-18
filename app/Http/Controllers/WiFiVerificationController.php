<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Services\TelegramAttendanceService;

class WiFiVerificationController extends Controller
{
    protected TelegramAttendanceService $attendanceService;

    public function __construct(TelegramAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show WiFi verification page
     */
    public function show(Attendance $attendance)
    {
        // Get user's IP address
        $ipAddress = request()->ip();

        return view('telegram.verify-wifi', [
            'attendance' => $attendance,
            'ipAddress' => $ipAddress,
        ]);
    }

    /**
     * Process WiFi verification
     */
    public function verify(Request $request, Attendance $attendance)
    {
        $ipAddress = $request->ip();

        $result = $this->attendanceService->processWifiVerification(
            $attendance->id,
            $ipAddress
        );

        if ($result['verified']) {
            return view('telegram.verify-wifi-success', [
                'message' => $result['message'],
                'attendance' => $attendance,
            ]);
        } else {
            return view('telegram.verify-wifi-failed', [
                'message' => $result['message'],
                'attendance' => $attendance,
            ]);
        }
    }
}
