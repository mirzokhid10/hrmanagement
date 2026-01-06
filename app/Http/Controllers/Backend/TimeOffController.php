<?php

namespace App\Http\Controllers\Backend;

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
use Illuminate\Support\Facades\Log; // For logging errors
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
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Determine the company_id for filtering.
        // If the user is an admin, pass null to the service, which means the TenantScope will bypass.
        // Otherwise, pass the user's company_id for regular tenant-scoped filtering.
        $companyIdForQuery = $user->isAdmin() ? null : $user->company_id;

        $filters = $request->only(['status', 'employee_id']);
        $timeOffs = $this->timeOffService->getPaginatedTimeOffs($companyIdForQuery, 10, $filters);

        $statuses = ['All', 'Pending', 'Approved', 'Rejected', 'Cancelled'];

        // For dropdowns (employees and time off types), these are typically scoped to the *current user's company*
        // even if the admin is viewing all data. If an admin should filter by employees from *any* company
        // in these dropdowns, those service methods would also need a nullable companyId parameter.
        // For now, let's keep dropdowns scoped to the admin's own company as a default.
        $employeesForDropdown = $this->timeOffService->getCompanyEmployees($user->company_id)
            ->mapWithKeys(function ($employee) {
                return [$employee->id => ($employee->full_name . ' (' . ($employee->job_title ?? 'N/A Job Title') . ')')];
            })->sort();

        $timeOffTypesForDropdown = $this->timeOffService->getTimeOffTypes($user->company_id);

        return view('admin.time-offs.index', compact('timeOffs', 'statuses', 'employeesForDropdown', 'timeOffTypesForDropdown'));
    }

    /**
     * Show the form for creating a new time-off request (by admin/HR on behalf of employee).
     * Since creation is via modal, this method can redirect or return an empty response.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.time-offs.index');
    }

    /**
     * Store a newly created time-off request.
     */
    public function store(StoreTimeOffRequest $request): RedirectResponse
    {
        try {
            $this->timeOffService->createTimeOff(
                $request->validated(),
                Auth::user()->company_id,
                Auth::id() // User who is creating the request
            );
            notify()->success('Time-off request created successfully!');
            return redirect()->route('admin.time-offs.index');
        } catch (ValidationException $e) {
            Log::error('Validation failed for time-off store: ' . $e->getMessage(), ['errors' => $e->errors(), 'input' => $request->all()]);
            notify()->error('Validation failed. Please check your input.');
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('add_time_off_modal_open_on_error', true); // Flash flag to reopen ADD modal
        } catch (\Exception $e) {
            Log::error('Failed to create time-off request: ' . $e->getMessage(), ['exception' => $e]);
            notify()->error('Failed to create time-off request. Please try again.');
            return redirect()->back()->withInput();
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
     * Returns JSON for AJAX requests to populate modal.
     */
    public function edit(TimeOff $timeOff): JsonResponse|View
    {
        // For AJAX requests (to populate the modal)
        if (request()->ajax() || request()->wantsJson()) {
            $data = $timeOff->load(['employee', 'type'])->toArray();
            $data['start_date'] = $timeOff->start_date->format('Y-m-d');
            $data['end_date'] = $timeOff->end_date->format('Y-m-d');
            return response()->json($data);
        }

        // Fallback for non-AJAX requests (if you had a dedicated edit page)
        $companyId = Auth::user()->company_id;
        $timeOffTypes = $this->timeOffService->getTimeOffTypes($companyId);
        $employees = $this->timeOffService->getCompanyEmployees($companyId)
            ->mapWithKeys(function ($employee) {
                return [$employee->id => $employee->name . ' (' . ($employee->job_title ?? 'N/A Position') . ')'];
            })->sort();

        return view('admin.time-offs.edit', compact('timeOff', 'timeOffTypes', 'employees'));
    }

    /**
     * Update the specified time-off request.
     */
    public function update(UpdateTimeOffRequest $request, TimeOff $timeOff): RedirectResponse
    {
        try {
            $this->timeOffService->updateTimeOff(
                $timeOff,
                $request->validated(),
                Auth::id() // User who is updating the request
            );
            notify()->success('Time-off request updated successfully!');
            return redirect()->route('admin.time-offs.index');
        } catch (ValidationException $e) {
            Log::error('Validation failed for time-off update (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['errors' => $e->errors(), 'input' => $request->all()]);
            notify()->error('Validation failed. Please check your input.');
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('edit_time_off_modal_open_on_error', true) // Flash flag to reopen EDIT modal
                ->with('edit_time_off_id_on_error', $timeOff->id); // Flash ID to re-fetch data
        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument for time-off update (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            notify()->error($e->getMessage());
            return redirect()->back()->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update time-off request (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            notify()->error('Failed to update time-off request. Please try again.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified time-off request.
     */
    public function destroy(TimeOff $timeOff): RedirectResponse
    {
        try {
            $this->timeOffService->deleteTimeOff($timeOff);
            notify()->success('Time-off request deleted successfully!');
            return redirect()->route('admin.time-offs.index');
        } catch (\Exception $e) {
            Log::error('Failed to delete time-off request (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            notify()->error('Failed to delete time-off request. Please try again.');
            return redirect()->back();
        }
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
     * Update the status of the specified time-off request (e.g., cancel).
     */
    public function updateStatus(Request $request, TimeOff $timeOff): JsonResponse
    {
        // Basic validation for the new status
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

            // Use the TimeOffService to handle the status change
            // The updateTimeOff method in your service can handle this robustly,
            // including balance adjustments if status changes to/from 'Approved'.
            $updatedTimeOff = $this->timeOffService->updateTimeOff(
                $timeOff,
                ['status' => $newStatus, 'rejection_reason' => $rejectionReason],
                $approverId
            );

            // If the service's updateTimeOff doesn't set approver_id/approved_at for non-approve/reject directly,
            // you might need explicit calls or ensure your service handles all status changes.
            // For simplicity, if updateTimeOff is robust, this should be enough.

            notify()->success("Time-off request status changed to {$newStatus} successfully!");
            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'new_status' => $updatedTimeOff->status]);
        } catch (ValidationException $e) {
            Log::error('Validation failed for time-off status update (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['errors' => $e->errors(), 'input' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'Validation failed: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid argument for time-off status update (ID: ' . $timeOff->id . '): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('An error occurred while updating time-off status (ID: ' . $timeOff->id . '): ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}
