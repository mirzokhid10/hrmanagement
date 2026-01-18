<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use App\Models\OfficeWifiNetwork;
use Illuminate\Http\Request;

class OfficeWifiController extends Controller
{
    public function index()
    {
        $networks = OfficeWifiNetwork::where('company_id', tenant()->id)
            ->with('officeLocation')
            ->orderBy('is_active', 'desc')
            ->get();

        return view('admin.office-wifi.index', compact('networks'));
    }

    public function create()
    {
        $locations = OfficeLocation::where('company_id', tenant()->id)
            ->where('is_active', true)
            ->get();

        return view('admin.office-wifi.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_location_id' => 'nullable|exists:office_locations,id',
            'network_name' => 'nullable|string|max:255',
            'ip_range' => 'required|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['company_id'] = tenant()->id;

        OfficeWifiNetwork::create($validated);

        notify()->success('WiFi network created successfully!');
        return redirect()->route('admin.office-wifi.index');
    }

    public function edit(OfficeWifiNetwork $officeWifi)
    {
        $locations = OfficeLocation::where('company_id', tenant()->id)
            ->where('is_active', true)
            ->get();

        return view('admin.office-wifi.edit', compact('officeWifi', 'locations'));
    }

    public function update(Request $request, OfficeWifiNetwork $officeWifi)
    {
        $validated = $request->validate([
            'office_location_id' => 'nullable|exists:office_locations,id',
            'network_name' => 'nullable|string|max:255',
            'ip_range' => 'required|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $officeWifi->update($validated);

        notify()->success('WiFi network updated successfully!');
        return redirect()->route('admin.office-wifi.index');
    }

    public function destroy(OfficeWifiNetwork $officeWifi)
    {
        $officeWifi->delete();

        notify()->success('WiFi network deleted successfully!');
        return redirect()->route('admin.office-wifi.index');
    }
}
