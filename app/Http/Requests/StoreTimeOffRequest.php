<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTimeOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Only authenticated users (admins/HR) can create time off requests on behalf of others
        return $user && $user->hasAnyRole(['admin', 'hr']); // Assuming Spatie roles
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'employee_id' => [
                'required',
                'integer',
                // Ensure the employee belongs to the current company
                Rule::exists('employees', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'time_off_type_id' => [
                'required',
                'integer',
                // Ensure the time off type belongs to the current company
                Rule::exists('time_off_types', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::in(['Pending'])],
            'total_days' => ['required', 'numeric', 'min:0.5'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Calculate total_days here.
     */
    protected function prepareForValidation(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $totalDays = null; // Initialize to null

        if ($startDate && $endDate) {
            try {
                $dtStartDate = new \DateTime($startDate);
                $dtEndDate = new \DateTime($endDate);
                // Calculate total days including start and end date
                $totalDays = $dtStartDate->diff($dtEndDate)->days + 1;
            } catch (\Exception $e) {
                // If date parsing fails, totalDays remains null.
                // The 'required' and 'date' rules will catch this.
            }
        }
        // Always merge total_days, even if null. The 'required' rule will then catch it.
        $this->merge([
            'total_days' => $totalDays,
            'status' => 'Pending', // Ensure status is always pending for new requests
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee is invalid or does not belong to your company.',
            'time_off_type_id.required' => 'Please select a time off type.',
            'time_off_type_id.exists' => 'The selected time off type is invalid or does not belong to your company.',
            'start_date.required' => 'The start date is required.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'end_date.required' => 'The end date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'reason.max' => 'The reason cannot exceed 500 characters.',
        ];
    }
}
