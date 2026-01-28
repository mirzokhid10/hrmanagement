<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\OfficeLocation;
use App\Models\OfficeWifiNetwork;
use Illuminate\Support\Facades\Auth;

class AttendanceSettingsController extends Controller
{
    public function index(Request $request)
    {
        /** @var App\Models\User */
        $user = Auth::user();
        $isAdmin = $user->isAdmin(); // Check if Super Admin

        // 1. Determine Target Company
        // If Admin AND has 'company_id' in URL -> Use that.
        // Else -> Use User's Company.
        $targetCompanyId = ($isAdmin && $request->has('company_id'))
            ? $request->company_id
            : $user->company_id;

        // 2. Fetch Data for that Company
        $locations = OfficeLocation::where('company_id', $targetCompanyId)->get();
        $wifis = OfficeWifiNetwork::where('company_id', $targetCompanyId)->get();

        // 3. For Dropdown (Admin Only)
        $companies = $isAdmin ? Company::all() : collect();

        $currentIp = $request->ip();

        return view('admin.attendance.settings.index', compact(
            'locations',
            'wifis',
            'currentIp',
            'companies',
            'targetCompanyId',
            'isAdmin'
        ));
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10|max:10000',
            'company_id' => 'required|exists:companies,id', // Validate Company ID
        ]);

        OfficeLocation::create([
            'company_id' => $request->company_id, // Use passed ID
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters,
            'address' => $request->address,
            'is_active' => true,
        ]);

        return back()->with('success', 'Office location added successfully.');
    }

    public function storeWifi(Request $request)
    {
        $request->validate([
            'network_name' => 'required|string|max:255',
            'ip_range' => 'required|string',
            'company_id' => 'required|exists:companies,id', // Validate Company ID
        ]);

        OfficeWifiNetwork::create([
            'company_id' => $request->company_id, // Use passed ID
            'network_name' => $request->network_name,
            'ip_range' => $request->ip_range,
            'is_active' => true,
        ]);

        return back()->with('success', 'WiFi network added successfully.');
    }

    // ... destroy methods remain similar, just ensure permission check ...
    public function destroyLocation($id)
    {
        $loc = OfficeLocation::findOrFail($id);
        // Add security check here if needed
        $loc->delete();
        return back();
    }

    public function destroyWifi($id)
    {
        $wifi = OfficeWifiNetwork::findOrFail($id);
        $wifi->delete();
        return back();
    }
}
