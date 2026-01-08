<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\Department; // Import Department model
use App\Models\Employee;
use App\Services\Interfaces\EmployeeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    protected EmployeeServiceInterface $employeeService;

    public function __construct(EmployeeServiceInterface $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the resource.
     * Shows a paginated list of employees.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companyIdForQuery = $user->isAdmin() ? null : $user->company_id; // null for admin to see all, or specific company for HR
        $searchTerm = $request->query('search');
        $perPage = 8;

        $employees = $this->employeeService->getPaginatedEmployees($companyIdForQuery, $perPage, $searchTerm);

        $departments = $user->company_id ? Department::where('company_id', $user->company_id)->get() : collect();
        $companies = $user->isAdmin() ? Company::all() : collect();

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.employee.partials._employee_list', compact('employees', 'departments', 'searchTerm', 'companies'));
        }

        return view('admin.employee.index', compact('employees', 'departments', 'searchTerm', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $companies = $user->isAdmin() ? Company::all() : collect([$user->company]);

        $departments = $user->isAdmin() ? Department::all() : ($user->company_id ? Department::where('company_id', $user->company_id)->get() : collect());

        return view('admin.employee.create', compact('departments', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();
        $image = $request->file('image');

        // Determine the company ID for the service call
        $companyIdForService = null;
        if ($user->isAdmin() && isset($data['company_id'])) {
            $companyIdForService = (int) $data['company_id'];
        } elseif (!$user->isAdmin()) {
            $companyIdForService = $user->company_id;
        }

        try {
            $this->employeeService->createEmployee($data, $image, $companyIdForService);
            notify()->success('Employee created successfully.');
            return redirect()->route('admin.employee.index');
        } catch (ValidationException $e) {
            // This catches validation errors that might be thrown directly from the service
            // (e.g., if you added custom validation logic there).
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            notify()->error('Failed to create employee. Please try again.');
            Log::error('Error creating employee: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee) // Route Model Binding (scoped by TenantScope)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Ensure the employee belongs to the current user's company, or the user is an admin.
        // The TenantScope on the Employee model already handles this for non-admins,
        // but an explicit check adds clarity and robust error handling.
        if (!$user->isAdmin() && $employee->company_id !== $user->company_id) {
            notify()->error('Unauthorized access to employee record.');
            return redirect()->route('admin.employee.index');
        }

        $departments = Department::where('company_id', $employee->company_id)->get();
        $companies = $user->isAdmin() ? Company::all() : collect([$employee->company]); // Pass all for admin, only employee's company for HR

        return view('admin.employee.edit', compact('employee', 'departments', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee) // Use UpdateEmployeeRequest
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Only allow update if employee belongs to current tenant OR logged-in user is admin
        if (!$user->isAdmin() && $employee->company_id !== $user->company_id) {
            notify()->error('Unauthorized action on employee record.');
            return redirect()->route('admin.employee.index');
        }

        try {
            $this->employeeService->updateEmployee(
                $employee,
                $request->validated(),
                $request->file('image'),
                $request->boolean('remove_image')
            );
            notify()->success('Employee record has been updated successfully');
            return redirect()->route('admin.employee.index');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput(); // Keep input and errors for repopulating the form
        } catch (\Exception $e) {
            notify()->error('Failed to update the record, please try again');
            Log::error('Error updating employee: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee) // Route Model Binding (scoped by TenantScope)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Only allow delete if employee belongs to current tenant OR logged-in user is admin
        if (!$user->isAdmin() && $employee->company_id !== $user->company_id) {
            notify()->error('Unauthorized action on employee record.');
            return redirect()->route('admin.employee.index');
        }

        try {
            $this->employeeService->deleteEmployee($employee);
            notify()->success('Employee deleted successfully!');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Failed to delete employee. Please try again.');
            Log::error('Error deleting employee: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back();
        }
    }

    /**
     * Fetch departments based on selected company (for dynamic dropdowns via AJAX).
     */
    public function getDepartmentsByCompany(Company $company): JsonResponse
    {
        // Ensure we get departments for this specific company
        // We use withoutTenantScope() to bypass global scopes if you have them applied
        $departments = $company->departments()
            ->withoutTenantScope()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }
}
