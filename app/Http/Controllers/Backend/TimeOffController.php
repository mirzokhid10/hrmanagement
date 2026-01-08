<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\TimeOffDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeOffRequest;
use App\Http\Requests\UpdateTimeOffRequest;
use App\Models\TimeOff;
use App\Services\Interfaces\TimeOffServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TimeOffController extends Controller
{
    protected TimeOffServiceInterface $timeOffService;

    public function __construct(TimeOffServiceInterface $timeOffService)
    {
        $this->timeOffService = $timeOffService;
    }

    /**
     * Display a listing of the time-off requests for the current company.
     */
    public function index(TimeOffDataTable $dataTable)
    {
        $companyId = Auth::user()->company_id;

        $stats = [
            'pending' => TimeOff::where('company_id', $companyId)->where('status', 'Pending')->count(),
            'total' => TimeOff::where('company_id', $companyId)->count(),
            'approved' => TimeOff::where('company_id', $companyId)->where('status', 'Approved')->count(),
            'rejected' => TimeOff::where('company_id', $companyId)->where('status', 'Rejected')->count(),
        ];

        return $dataTable->render('admin.time-offs.index', compact('stats'));
    }

    /**
     * Show the form for creating a new time-off request.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companyId = $user->company_id;

        $companies = [];
        $departments = [];
        $employees = [];
        $types = [];

        // Super Admin - can see all companies
        if ($user->isAdmin()) {
            $companies = \App\Models\Company::query()
                ->withoutGlobalScopes()
                ->where('is_active', true)
                ->pluck('name', 'id');
        }
        // Regular User (Admin/HR/Employee)
        else {
            $departments = \App\Models\Department::where('company_id', $companyId)->pluck('name', 'id');

            $employees = \App\Models\Employee::where('company_id', $companyId)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->id => $item->first_name . ' ' . $item->last_name];
                });

            $types = \App\Models\TimeOffType::where('company_id', $companyId)->get();
        }

        return view('admin.time-offs.create', compact('companies', 'departments', 'employees', 'types'));
    }

    /**
     * Store a newly created time-off request.
     */
    public function store(StoreTimeOffRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companyId = null;

        // Check if company_id was provided in the form (super admin selection)
        if ($request->has('company_id') && $request->company_id) {
            // Super admin selected a company from dropdown
            $companyId = $request->company_id;
        } elseif ($user->isAdmin() || $user->hasRole('admin')) {
            // Super admin but no company selected - get from employee
            if ($request->has('employee_id')) {
                $employee = \App\Models\Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
                    ->find($request->employee_id);

                if ($employee) {
                    $companyId = $employee->company_id;
                }
            }
        } else {
            // Regular user - use their company_id
            $companyId = $user->company_id;
        }

        // Final validation - company_id must be set
        if (!$companyId) {

            notify()->error('Unable to determine company for this request. Please select a company.');
            return back()->withInput();
        }

        try {
            $this->timeOffService->createTimeOff($request->validated(), $companyId, Auth::id());
            notify()->success('Leave request created successfully.');
            return redirect()->route('admin.time-offs.index');
        } catch (\Exception $e) {
            notify()->error('Failed to create leave request: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Display the specified time-off request.
     */
    public function show(TimeOff $timeOff): View
    {
        $timeOff->load(['employee.user.company', 'type', 'approver.user']);
        $balance = $timeOff->employee->timeOffBalances
            ->where('time_off_type_id', $timeOff->time_off_type_id)
            ->where('year', $timeOff->start_date->year)
            ->first();

        return view('admin.time_offs.show', compact('timeOff', 'balance'));
    }

    /**
     * Show the form for editing the specified time-off request.
     */
    public function edit(TimeOff $timeOff)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Security Check: Ensure user can only edit time-offs for their own company
        if (!$user->isAdmin() && $user->company_id !== $timeOff->company_id) {
            notify()->error('You do not have permission to edit this leave request.');
            return redirect()->route('admin.time-offs.index');
        }

        // 2. Determine target company for data fetching
        $targetCompanyId = $timeOff->company_id;

        $companies = [];
        $departments = [];
        $employees = [];
        $types = [];

        // 3. Data Fetching Strategy
        if ($user->isAdmin()) {
            // === SCENARIO A: SUPER ADMIN ===

            // Fetch ALL companies for the company dropdown
            $companies = \App\Models\Company::query()
                ->withoutGlobalScopes() // Bypass tenant scope
                ->where('is_active', true)
                ->pluck('name', 'id');


            $departments = \App\Models\Department::query()
                ->withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $targetCompanyId)
                ->pluck('name', 'id');

            $employees = \App\Models\Employee::query()
                ->withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $targetCompanyId)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->id => $item->first_name . ' ' . $item->last_name];
                });

            $types = \App\Models\TimeOffType::query()
                ->withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $targetCompanyId)
                ->get();
        } else {
            // === SCENARIO B: REGULAR USER (Standard Tenant Scope) ===

            // Standard queries automatically apply the TenantScope based on Auth user
            $departments = \App\Models\Department::where('company_id', $targetCompanyId)->pluck('name', 'id');

            $employees = \App\Models\Employee::where('company_id', $targetCompanyId)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->id => $item->first_name . ' ' . $item->last_name];
                });

            $types = \App\Models\TimeOffType::where('company_id', $targetCompanyId)->get();
        }

        return view('admin.time-offs.edit', compact('timeOff', 'companies', 'departments', 'employees', 'types'));
    }

    public function update(UpdateTimeOffRequest $request, TimeOff $timeOff)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Authorization Check (Redundant if handled in Request, but good for safety)
        if (!$user->isAdmin() && $user->company_id !== $timeOff->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Handle Company ID Logic for Super Admins
        // If Super Admin changes the company in the dropdown, we need to respect that.
        // If not provided (or regular user), we keep the existing company_id.
        $data = $request->validated();

        if ($user->isAdmin()) {
            // If the form submitted a company_id, use it.
            // Otherwise, keep the original record's company_id.
            if (!isset($data['company_id'])) {
                $data['company_id'] = $timeOff->company_id;
            }
        } else {
            // Regular users cannot change the company
            $data['company_id'] = $user->company_id;
        }

        try {
            $this->timeOffService->updateTimeOff($timeOff, $data, Auth::id());

            notify()->success('Leave request updated successfully.');
            return redirect()->route('admin.time-offs.index');
        } catch (\Exception $e) {
            Log::error('Failed to update time off: ' . $e->getMessage());
            notify()->error('Failed to update leave request: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy(TimeOff $timeOff)
    {
        $this->timeOffService->deleteTimeOff($timeOff);
        return redirect()->route('admin.time-offs.index')->with('success', 'Leave request deleted successfully.');
    }

    /**
     * Approve the specified time-off request.
     */
    public function approve(Request $request, TimeOff $timeOff): RedirectResponse
    {
        try {
            $this->timeOffService->approveTimeOff($timeOff, Auth::id());
            notify()->success('Time-off request approved successfully and balance updated.');
            return redirect()->route('admin.time-offs.index');
        } catch (\InvalidArgumentException $e) {
            Log::warning('Attempt to approve non-pending time-off (ID: ' . $timeOff->id . '): ' . $e->getMessage());
            notify()->error($e->getMessage());
            return back();
        } catch (\Exception $e) {
            Log::error('An error occurred while approving time-off request (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            notify()->error('An error occurred while approving the request. Please try again.');
            return back();
        }
    }

    /**
     * Reject the specified time-off request.
     */
    public function reject(Request $request, TimeOff $timeOff): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->timeOffService->rejectTimeOff($timeOff, Auth::id(), $request->input('rejection_reason'));
            notify()->success('Time-off request rejected successfully.');
            return redirect()->route('admin.time-offs.index');
        } catch (\InvalidArgumentException $e) {
            Log::warning('Attempt to reject non-pending time-off (ID: ' . $timeOff->id . '): ' . $e->getMessage());
            notify()->error($e->getMessage());
            return back();
        } catch (\Exception $e) {
            Log::error('An error occurred while rejecting time-off request (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            notify()->error('An error occurred while rejecting the request. Please try again.');
            return back();
        }
    }

    /**
     * Update the status of the specified time-off request.
     */
    public function updateStatus(Request $request, TimeOff $timeOff): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled'])],
            'rejection_reason' => [
                Rule::requiredIf($request->input('status') === 'Rejected'),
                'nullable',
                'string',
                'max:500'
            ],
        ]);

        try {
            $newStatus = $request->input('status');
            $rejectionReason = $request->input('rejection_reason');
            $approverId = Auth::id();

            $updatedTimeOff = $this->timeOffService->updateTimeOff(
                $timeOff,
                ['status' => $newStatus, 'rejection_reason' => $rejectionReason],
                $approverId
            );

            notify()->success("Time-off request status changed to {$newStatus} successfully!");
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'new_status' => $updatedTimeOff->status
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed for time-off status update (ID: ' . $timeOff->id . '): ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid argument for time-off status update (ID: ' . $timeOff->id . '): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('An error occurred while updating time-off status (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    /**
     * AJAX: Get departments for a specific company.
     * Used when super admin selects a company in the time-off form.
     */
    public function getDepartments($companyId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Super admins can access any company, regular users only their own
        if (!$user->isAdmin() && $user->company_id != $companyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = \App\Models\Department::query();

        // CRITICAL FIX: Super admins need to bypass tenant scope to see other companies' data
        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        $departments = $query->where('company_id', $companyId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        Log::info('getDepartments called', [
            'user_id' => $user->id,
            'is_super_admin' => $user->isAdmin(),
            'requested_company_id' => $companyId,
            'departments_count' => $departments->count()
        ]);

        return response()->json($departments);
    }

    /**
     * AJAX: Get employees and time-off types based on company and/or department.
     * Used when super admin selects a company or when anyone selects a department.
     */
    public function getEmployees(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companyId = $request->company_id;
        $departmentId = $request->department_id;

        // Security check for non-super admins
        if (!$user->isAdmin() && $user->company_id != $companyId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 1. Get Employees
        $employeeQuery = \App\Models\Employee::query();

        // CRITICAL FIX: Super admins bypass tenant scope
        if ($user->isAdmin()) {
            $employeeQuery->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        if ($companyId) {
            $employeeQuery->where('company_id', $companyId);
        }

        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
        }

        $employees = $employeeQuery->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name
                ];
            });

        // 2. Get Time Off Types
        $typeQuery = \App\Models\TimeOffType::query();

        // CRITICAL FIX: Super admins bypass tenant scope for time-off types too
        if ($user->isAdmin()) {
            $typeQuery->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        $types = $typeQuery->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        Log::info('getEmployees called', [
            'user_id' => $user->id,
            'is_super_admin' => $user->isAdmin(),
            'requested_company_id' => $companyId,
            'department_id' => $departmentId,
            'employees_count' => $employees->count(),
            'types_count' => $types->count()
        ]);

        return response()->json([
            'employees' => $employees,
            'types' => $types
        ]);
    }
}
