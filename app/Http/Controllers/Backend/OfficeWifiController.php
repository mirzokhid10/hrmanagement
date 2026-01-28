<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\OfficeLocation;
use App\Models\OfficeWifiNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class OfficeWifiController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $networks = OfficeWifiNetwork::with('officeLocation')
            ->orderBy('is_active', 'desc')
            ->get();

        return view('admin.office-wifi.index', compact('networks'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $locations = OfficeLocation::where('is_active', true)->get();

        $companies = collect();
        if ($user->isAdmin()) {
            $companies = Company::pluck('name', 'id');
        }

        return view('admin.office-wifi.create', compact('locations', 'companies'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'office_location_id' => 'nullable|exists:office_locations,id',
            'network_name' => 'required|string|max:255', // Changed to required as it's a primary identifier
            'ip_range' => 'required|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ];

        // If the user is a super admin, allow them to select a company_id
        if ($user->isAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules);

            if ($user->isAdmin() && $request->has('company_id')) {
                $validated['company_id'] = $request->input('company_id');
            }

            OfficeWifiNetwork::create($validated);

            notify()->success('WiFi network created successfully!');
            return redirect()->route('admin.office-wifi.index');
        } catch (ValidationException $e) {
            // Laravel's default handling for validation errors (redirect back with errors) is usually sufficient.
            throw $e;
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to create WiFi networks.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while creating the WiFi network. Please try again.');
            return redirect()->back();
        }
    }

    public function edit(OfficeWifiNetwork $officeWifi)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $locations = OfficeLocation::where('is_active', true)->get();
        // The TenantScope will ensure only locations for the current tenant are fetched for non-admins.

        $companies = collect();
        if ($user->isAdmin()) {
            $companies = Company::pluck('name', 'id');
        }

        return view('admin.office-wifi.edit', compact('officeWifi', 'locations', 'companies'));
    }

    public function update(Request $request, OfficeWifiNetwork $officeWifi)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'office_location_id' => 'nullable|exists:office_locations,id',
            'network_name' => 'required|string|max:255', // Changed to required
            'ip_range' => 'required|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ];

        if ($user->isAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules);

            if ($user->isAdmin() && $request->has('company_id')) {
                $officeWifi->company_id = $request->input('company_id');
            }

            $officeWifi->update($validated);

            notify()->success('WiFi network updated successfully!');
            return redirect()->route('admin.office-wifi.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to update this WiFi network.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while updating the WiFi network. Please try again.');
            return redirect()->back();
        }
    }

    public function destroy(OfficeWifiNetwork $officeWifi)
    {

        try {
            $officeWifi->delete();

            notify()->success('WiFi network deleted successfully!');
            return redirect()->route('admin.office-wifi.index');
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to delete this WiFi network.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while deleting the WiFi network. Please try again.');
            return redirect()->back();
        }
    }
}
