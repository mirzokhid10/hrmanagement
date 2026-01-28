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

class OfficeLocationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // This will be filtered by TenantScope for non-admins automatically
        $locations = OfficeLocation::with('company')->get();
        $wifis = OfficeWifiNetwork::with('company', 'officeLocation')->get();

        $companies = collect();
        if ($user->isAdmin()) {
            $companies = Company::pluck('name', 'id');
        }

        // Get current IP (example, replace with actual logic to get user's public IP)
        $currentIp = request()->ip();

        return view('admin.attendance.settings', compact('locations', 'wifis', 'companies', 'currentIp'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companies = collect();
        if ($user->isAdmin()) {
            $companies = Company::pluck('name', 'id');
        }

        return view('admin.office-location.create', compact('companies'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ];

        // If the user is a super admin, allow them to select a company_id
        if ($user->isAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules);

            // Set boolean fields based on checkbox presence
            $validated['is_active'] = $request->has('is_active');
            $validated['is_primary'] = $request->has('is_primary');

            // If user is admin and provided company_id, use it.
            // Otherwise, the model's 'creating' event will handle setting tenant()->id.
            if ($user->isAdmin() && $request->has('company_id')) {
                $companyId = $validated['company_id'];
            } else {
                // For non-admins, company_id is not expected in the request.
                // The model's booted method (static::creating) will automatically set model->company_id = tenant()->id;
                $companyId = tenant()->id; // Fallback, though model event should handle it if not explicitly set
            }
            $validated['company_id'] = $companyId;

            // Handle primary status: if new location is primary, set others to false for the same company
            if ($validated['is_primary']) {
                OfficeLocation::where('company_id', $validated['company_id'])
                    ->update(['is_primary' => false]);
            }

            OfficeLocation::create($validated);

            notify()->success('Office location created successfully!');
            return redirect()->route('admin.office-location.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to create office locations.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while creating the office location. Please try again.');
            return redirect()->back();
        }
    }

    public function edit(OfficeLocation $officeLocation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companies = collect();
        if ($user->isAdmin()) {
            $companies = Company::pluck('name', 'id');
        }

        return view('admin.office-location.edit', compact('officeLocation', 'companies'));
    }

    public function update(Request $request, OfficeLocation $officeLocation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min="10"|max="1000"',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ];

        // If the user is a super admin, allow them to update company_id (careful with this)
        // If you don't want admins to change company_id after creation, remove this rule.
        if ($user->isAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        try {
            $validated = $request->validate($rules);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_primary'] = $request->has('is_primary');

            // If user is admin and provided company_id, update it.
            if ($user->isAdmin() && $request->has('company_id')) {
                $officeLocation->company_id = $request->input('company_id');
            }

            // Handle primary status: if this location is primary, set others to false for the same company
            if ($validated['is_primary']) {
                OfficeLocation::where('company_id', $officeLocation->company_id)
                    ->where('id', '!=', $officeLocation->id)
                    ->update(['is_primary' => false]);
            }

            $officeLocation->update($validated);

            notify()->success('Office location updated successfully!');
            return redirect()->route('admin.office-location.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to update this office location.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while updating the office location. Please try again.');
            return redirect()->back();
        }
    }

    public function destroy(OfficeLocation $officeLocation)
    {

        try {
            $officeLocation->delete();

            notify()->success('Office location deleted successfully!');
            return redirect()->route('admin.office-location.index');
        } catch (AuthorizationException $e) {
            notify()->error($e->getMessage() ?: 'You are not authorized to delete this office location.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('An unexpected error occurred while deleting the office location. Please try again.');
            return redirect()->back();
        }
    }
}
