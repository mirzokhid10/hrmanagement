<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\OfficeLocation;
use Illuminate\Support\Facades\Auth;

class OfficeLocationController extends Controller
{
    public function index()
    {
        // Authorize viewing the list of office locations
        // Pass the class name as the second argument when checking general permissions (not specific instance)
        /** @var \App\Models\User $user */
        // $this->authorize('viewAny', OfficeLocation::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = OfficeLocation::query()->with('company');

        if (!$user->isAdmin()) {
            // If not a super admin, filter by the user's company_id
            // Note: The policy `viewAny` already checked if they *can* view,
            // this part *filters* what they *do* view.
            $query->where('company_id', $user->company_id); // Using $user->company_id for consistency with policy
        }

        $locations = $query->orderBy('is_primary', 'desc')
            ->orderBy('name')
            ->get();

        return view('admin.office-location.index', compact('locations'));
    }

    public function create()
    {
        // Authorize creating an office location
        // $this->authorize('create', OfficeLocation::class);

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
        // Authorize storing an office location
        // $this->authorize('create', OfficeLocation::class);

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

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_primary'] = $request->has('is_primary');

        if ($user->isAdmin()) {
            $companyId = $validated['company_id'];
        } else {
            $companyId = $user->company_id; // HR manager's company_id
        }
        $validated['company_id'] = $companyId;

        if ($validated['is_primary']) {
            OfficeLocation::where('company_id', $validated['company_id'])
                ->update(['is_primary' => false]);
        }

        OfficeLocation::create($validated);

        notify()->success('Office location created successfully!');
        return redirect()->route('admin.office-location.index');
    }

    public function edit(OfficeLocation $officeLocation)
    {
        // Authorize updating this specific office location
        // $this->authorize('update', $officeLocation); // Policy receives $user and $officeLocation

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
        // Authorize updating this specific office location
        // $this->authorize('update', $officeLocation); // Policy receives $user and $officeLocation

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

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_primary'] = $request->has('is_primary');

        if ($validated['is_primary']) {
            OfficeLocation::where('company_id', $officeLocation->company_id)
                ->where('id', '!=', $officeLocation->id)
                ->update(['is_primary' => false]);
        }

        $officeLocation->update($validated);

        notify()->success('Office location updated successfully!');
        return redirect()->route('admin.office-location.index');
    }

    public function destroy(OfficeLocation $officeLocation)
    {
        // Authorize deleting this specific office location
        // $this->authorize('delete', $officeLocation); // Policy receives $user and $officeLocation

        $officeLocation->delete();

        notify()->success('Office location deleted successfully!');
        return redirect()->route('admin.office-location.index');
    }
}
