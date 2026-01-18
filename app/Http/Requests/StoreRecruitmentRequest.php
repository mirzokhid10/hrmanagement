<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRecruitmentRequest extends FormRequest
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
        $validationCompanyId = $user->isAdmin() ? $this->input('company_id') : $user->company_id;


        $rules = [
            'title'            => ['required', 'string', 'max:255'],
            'department_id'    => [
                'required',
                'exists:departments,id',
                // Custom Rule: Check if department belongs to the selected company
                \Illuminate\Validation\Rule::exists('departments', 'id')->where(function ($query) use ($validationCompanyId) {
                    return $query->where('company_id', $validationCompanyId);
                }),
            ],
            'job_type'         => ['required', 'string'],
            'experience'       => ['required', 'string'],
            'schedule'         => ['required', 'string'],
            'working_hours'    => ['required', 'string'],
            'location'         => ['required', 'string'],
            'salary_range'     => ['nullable', 'string'],
            'deadline'         => ['nullable', 'date'],
            'description' => ['required', 'string', 'min:200'], // ✅ Enforce 200 chars
            'key_skills_input' => ['nullable', 'string'], // Comma separated string
            'hh_professional_role_id' => ['nullable', 'string'],
        ];

        // Admin must select a company
        /** @var \App\Models\User $user */

        if ($user->isAdmin()) {
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Admin / Company Context
            'company_id.required' => 'As an admin, you must select a company to create a job vacancy.',
            'company_id.exists'   => 'The selected company does not exist.',

            // Department Logic
            'department_id.required' => 'Please select a department.',
            'department_id.exists'   => 'The selected department is invalid or does not belong to the chosen company.',

            // HH.ru Specific Requirements
            'description.required' => 'A job description is required.',
            'description.min'      => 'The job description must be at least 200 characters long to meet HH.ru quality standards.',
            'billing_type' => ['nullable', 'string'],
            // General Fields
            'title.required'         => 'Please enter a job title.',
            'job_type.required'      => 'Please select a job type (e.g., Full-time).',
            'experience.required'    => 'Please select the required experience level.',
            'schedule.required'      => 'Please select a work schedule.',
            'working_hours.required' => 'Please specify the working hours.',
            'location.required'      => 'Please specify the location (e.g., Tashkent).',
        ];
    }
}
