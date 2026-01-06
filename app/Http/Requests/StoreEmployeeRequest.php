<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validationCompanyId = null;

        // Determine the company ID for validation scope
        if ($user->isAdmin()) {
            // Admin must provide company_id. If not, the 'required' rule for company_id will catch it.
            $validationCompanyId = $this->input('company_id');
        } else {
            // Non-admin (HR) is always tied to their own company
            $validationCompanyId = $user->company_id;
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'job_title' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', 'string', Rule::in(['Active', 'Inactive', 'Probation', 'Terminated'])],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'salary' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($user->isAdmin()) {
            // Admin must explicitly select a company
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        // Apply company-scoped rules only if a valid company_id is available for validation
        if ($validationCompanyId) {
            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:255',
                // Check uniqueness only within the specified company
                Rule::unique('employees', 'email')->where(function ($query) use ($validationCompanyId) {
                    return $query->where('company_id', $validationCompanyId);
                }),
            ];
            $rules['department_id'] = [
                'required',
                'exists:departments,id',
                // Ensure department belongs to the specified company
                Rule::exists('departments', 'id')->where(function ($query) use ($validationCompanyId) {
                    $query->where('company_id', $validationCompanyId);
                }),
            ];
        } else {
            // Fallback if no company context is available (e.g., admin hasn't selected a company yet)
            // These rules will ensure a validation error if the context is missing.
            $rules['email'][] = 'required';
            $rules['email'][] = Rule::in([])->message('A company context is required for email validation. Please select a company first.');
            $rules['department_id'][] = 'required';
            $rules['department_id'][] = Rule::in([])->message('A company context is required for department validation. Please select a company first.');
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'As an admin, you must specify a company for the new employee.',
            'company_id.exists' => 'The selected company is invalid.',
            'department_id.exists' => 'The selected department is invalid or does not belong to the chosen company.',
            'email.unique' => 'An employee with this email address already exists in the chosen company.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
            'email.in' => 'A company context is required for email validation. Please select a company first.',
            'department_id.in' => 'A company context is required for department validation. Please select a company first.',
        ];
    }
}
