<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Services\Interfaces\OnboardingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    protected OnboardingServiceInterface $onboardingService;

    public function __construct(OnboardingServiceInterface $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Display the Kanban Board (Task Management).
     */
    public function index(Request $request)
    {
        // Reuse the logic we wrote previously for the Kanban board
        // Or if you want a list view, use the service pagination
        // For now, let's keep the Kanban logic inside this controller method for simplicity

        /** @var \App\Models\USer */

        $user = Auth::user();
        $tasks = EmployeeTask::with('employee')->orderBy('due_date', 'asc')->get();

        $groupedTasks = [
            'new' => $tasks->where('status', 'new'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'pending' => $tasks->where('status', 'pending'),
            'completed' => $tasks->where('status', 'completed'),
        ];

        // For the "Quick Add" modal on the index page
        $companies = $user->isAdmin() ? Company::all() : collect();
        $employees = $user->company_id
            ? Employee::where('company_id', $user->company_id)->get()
            : collect();

        return view('admin.onboarding.index', compact('groupedTasks', 'employees', 'companies'));
    }

    /**
     * Show the form for creating a new task (Full Page).
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companies = $user->isAdmin() ? Company::all() : collect();

        // If HR, load employees immediately. If Admin, wait for AJAX selection.
        $employees = $user->company_id
            ? Employee::where('company_id', $user->company_id)->get()
            : collect();

        return view('admin.onboarding.create', compact('companies', 'employees'));
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request)
    {
        try {
            $this->onboardingService->createTask($request->validated());
            notify()->success('Task assigned successfully.');
            return redirect()->route('admin.onboarding.index');
        } catch (\Exception $e) {
            notify()->error('Failed to create task.');
            Log::error('Task creation error: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing.
     */
    public function edit(EmployeeTask $onboarding) // using 'onboarding' param name from resource route
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check
        if (!$user->isAdmin() && $onboarding->company_id !== $user->company_id) {
            abort(403);
        }

        return view('admin.onboarding.edit', compact('onboarding'));
    }

    /**
     * Update the task.
     */
    public function update(UpdateTaskRequest $request, EmployeeTask $onboarding)
    {
        try {
            $this->onboardingService->updateTask($onboarding, $request->validated());
            notify()->success('Task updated successfully.');
            return redirect()->route('admin.onboarding.index');
        } catch (\Exception $e) {
            notify()->error('Failed to update task.');
            return back()->withInput();
        }
    }

    /**
     * Delete the task.
     */
    public function destroy(EmployeeTask $onboarding)
    {
        try {
            $this->onboardingService->deleteTask($onboarding);
            notify()->success('Task deleted.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Failed to delete task.');
            return redirect()->back();
        }
    }

    /**
     * AJAX: Get employees for a specific company (For Admin Dropdown).
     */
    public function getEmployeesByCompany(Company $company): JsonResponse
    {
        $employees = $company->employees()
            ->withoutTenantScope()
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->full_name
                ];
            });

        return response()->json($employees);
    }

    public function updateStatus(Request $request, EmployeeTask $onboarding)
    {
        // Validate input
        $request->validate([
            'status' => 'required|in:new,in_progress,pending,completed'
        ]);

        // Update status
        $onboarding->update([
            'status' => $request->status,
            // If status is completed, mark is_completed as true
            'is_completed' => $request->status === 'completed'
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }
}
