<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BotSetting;

class BotSettingsController extends Controller
{
    public function index()
    {
        $company = tenant();
        if (!$company) {
            abort(404, 'Company not found');
        }

        $settings = BotSetting::getForCompany($company->id);

        return view('admin.bot-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $company = tenant();
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            'attendance_enabled' => 'boolean',
            'location_verification_required' => 'boolean',
            'wifi_verification_required' => 'boolean',
            'check_in_start_time' => 'required|date_format:H:i',
            'check_in_end_time' => 'required|date_format:H:i',
            'check_out_start_time' => 'required|date_format:H:i',
            'check_out_end_time' => 'required|date_format:H:i',
            'late_threshold_minutes' => 'required|integer|min:0|max:120',
            'send_daily_reminders' => 'boolean',
            'reminder_time' => 'nullable|date_format:H:i',
            'notify_managers_on_late' => 'boolean',
            'notify_managers_on_absent' => 'boolean',
            'welcome_message' => 'nullable|string|max:1000',
            'timezone' => 'required|string',
            'language' => 'required|in:en,ru,uz',
        ]);

        $settings = BotSetting::getForCompany($company->id);
        $settings->update($validated);

        notify()->success('Bot settings updated successfully!');
        return redirect()->route('admin.bot-settings.index');
    }
}
