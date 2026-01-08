<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTimeOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Super admins, admins, and HR can update time-off requests
        return $user && ($user->isAdmin() || $user->hasAnyRole(['admin', 'hr']));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // For super admins, skip company validation
        // For regular users, validate against their company
        if ($user->isAdmin()) {
            return [
                'employee_id' => [
                    'required',
                    'integer',
                    'exists:employees,id',
                ],
                'time_off_type_id' => [
                    'required',
                    'integer',
                    'exists:time_off_types,id',
                ],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'reason' => ['nullable', 'string', 'max:500'],
                'status' => [
                    'required',
                    Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled']),
                ],
                'rejection_reason' => [
                    Rule::requiredIf($this->input('status') === 'Rejected'),
                    'nullable',
                    'string',
                    'max:500'
                ],
                'total_days' => ['required', 'numeric', 'min:0.5'],
            ];
        } else {
            $companyId = $user->company_id;

            return [
                'employee_id' => [
                    'required',
                    'integer',
                    Rule::exists('employees', 'id')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'time_off_type_id' => [
                    'required',
                    'integer',
                    Rule::exists('time_off_types', 'id')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'reason' => ['nullable', 'string', 'max:500'],
                'status' => [
                    'required',
                    Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled']),
                ],
                'rejection_reason' => [
                    Rule::requiredIf($this->input('status') === 'Rejected'),
                    'nullable',
                    'string',
                    'max:500'
                ],
                'total_days' => ['required', 'numeric', 'min:0.5'],
            ];
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $totalDays = null;

        if ($startDate && $endDate) {
            try {
                $dtStartDate = new \DateTime($startDate);
                $dtEndDate = new \DateTime($endDate);
                $totalDays = $dtStartDate->diff($dtEndDate)->days + 1;
            } catch (\Exception $e) {
                // Validation will catch this
            }
        }

        $this->merge(['total_days' => $totalDays]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee is invalid or does not belong to the selected company.',
            'time_off_type_id.required' => 'Please select a time off type.',
            'time_off_type_id.exists' => 'The selected time off type is invalid or does not belong to the selected company.',
            'start_date.required' => 'The start date is required.',
            'end_date.required' => 'The end date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'reason.max' => 'The reason cannot exceed 500 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The selected status is invalid.',
            'rejection_reason.required_if' => 'A rejection reason is required when the status is "Rejected".',
            'rejection_reason.max' => 'The rejection reason cannot exceed 500 characters.',
        ];
    }
}
