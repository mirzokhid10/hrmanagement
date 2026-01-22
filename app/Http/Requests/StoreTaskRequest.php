<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['new', 'in_progress', 'pending', 'completed'])],
        ];

        if ($user->isAdmin()) {
            // Admin must select a company first to filter employees
            // But ultimately, we just need a valid employee_id.
            // The UI handles the filtering, backend validates the employee exists.
            $rules['company_id'] = ['required', 'exists:companies,id'];
            $rules['employee_id'] = [
                'required',
                'exists:employees,id',
                // Ensure employee belongs to the selected company
                Rule::exists('employees', 'id')->where('company_id', $this->input('company_id'))
            ];
        } else {
            // HR can only assign to their own employees
            $rules['employee_id'] = [
                'required',
                'exists:employees,id',
                Rule::exists('employees', 'id')->where('company_id', $user->company_id)
            ];
        }

        return $rules;
    }
}
