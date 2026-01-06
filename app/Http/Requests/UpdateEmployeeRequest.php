<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $tenantId = app('tenant')->id;
        $employee = $this->route('employee'); // Get the employee being updated via Route Model Binding

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Email must be unique for users within the tenant, ignoring current employee's email
                Rule::unique('users', 'email')->where(function ($query) use ($tenantId) {
                    return $query->where('company_id', $tenantId);
                })->ignore($employee->email, 'email'), // Ignore by email since no user_id on employee

                // Email must be unique for employee profiles within the tenant, ignoring current employee
                Rule::unique('employees', 'email')->where(function ($query) use ($tenantId) {
                    return $query->where('company_id', $tenantId);
                })->ignore($employee->id), // Ignore by employee ID
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'department_id' => [
                'required',
                'exists:departments,id',
                Rule::exists('departments', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('company_id', $tenantId);
                }),
            ],
            'job_title' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['Active', 'Inactive', 'Probation', 'Terminated'])], // Using 'status'
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'remove_image' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.exists' => 'The selected department is invalid or does not belong to your company.',
            'email.unique' => 'An employee with this email address already exists in your company.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
        ];
    }
}
