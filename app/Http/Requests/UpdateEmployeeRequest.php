<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employee = $this->route('employee'); // Get the employee from route model binding

        // Admin can update any employee. Non-admin can only update employees within their company.
        return $user->isAdmin() || ($employee && $employee->company_id === $user->company_id);
    }

    public function rules(): array
    {
        $employee = $this->route('employee'); // Get the employee being updated

        // The company ID for validation scope is always the employee's company_id
        $validationCompanyId = $employee->company_id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Check uniqueness only within the employee's company, ignoring the current employee
                Rule::unique('employees', 'email')->where(function ($query) use ($validationCompanyId) {
                    return $query->where('company_id', $validationCompanyId);
                })->ignore($employee->id), // Ignore by employee ID
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'department_id' => [
                'required',
                'exists:departments,id',
                // Ensure department belongs to the employee's company
                Rule::exists('departments', 'id')->where(function ($query) use ($validationCompanyId) {
                    $query->where('company_id', $validationCompanyId);
                }),
            ],
            'job_title' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', 'string', Rule::in(['Active', 'Inactive', 'Probation', 'Terminated'])],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'remove_image' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.exists' => 'The selected department is invalid or does not belong to the employee\'s company.',
            'email.unique' => 'An employee with this email address already exists in the employee\'s company.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
        ];
    }
}
