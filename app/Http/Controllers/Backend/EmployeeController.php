<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee; // Ensure Employee model is imported
use App\Services\Interfaces\EmployeeServiceInterface;
use App\Scopes\TenantScope;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // Keep for explicit error handling
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    protected EmployeeServiceInterface $employeeService;

    public function __construct(EmployeeServiceInterface $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the resource.
     * Uses a simple paginated view, similar to how you might use a DataTable.
     */
    public function index(Request $request)
    {

        $searchTerm = $request->input('search'); // Get the search term
        $employees = $this->employeeService->getPaginatedEmployees(10, $searchTerm);
        $departments = $this->employeeService->getDepartments();

        // If it's an AJAX request, return only the rendered employee list partial
        if ($request->ajax()) {
            return view('admin.employee.partials._employee_list', compact('employees'))->render();
        }

        // For a regular request, return the full index view
        return view('admin.employee.index', compact('employees', 'departments', 'searchTerm'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {

        try {
            $this->employeeService->createEmployee(
                $request->validated(),
                $request->file('image')
            );
            notify()->success('Employee added successfully!');
            return redirect()->route('admin.employee.index');
        } catch (ValidationException $e) {
            notify()->error('Something went wrong, please try again:)');
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            notify()->error('Something went wrong, please try again:)');
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee) // Route Model Binding (scoped by TenantScope)
    {
        if (request()->ajax() || request()->wantsJson()) {
            $employee->load('department');
            return response()->json($employee);
        }

        $departments = $this->employeeService->getDepartments();
        return view('admin.employee.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee) // Use UpdateEmployeeRequest
    {
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
                ->withInput()
                ->with('edit_modal_open', true)
                ->with('edit_employee_id_on_error', $employee->id);
        } catch (\Exception $e) {
            notify()->error('Failed to updated the record, please try again');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee) // Route Model Binding (scoped by TenantScope)
    {
        try {
            $this->employeeService->deleteEmployee($employee);
            notify()->success('Employee deleted successfully!'); // Use notify()
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Failed to delete employee. Please try again.');
            return redirect()->back();
        }
    }
}
