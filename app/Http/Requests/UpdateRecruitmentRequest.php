<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRecruitmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Use Route Model Binding to get the recruitment being updated
        $recruitment = $this->route('recruitment');

        // Admin can update any. HR can only update their own company's recruitments.
        return $user->isAdmin() || ($recruitment && $recruitment->company_id === $user->company_id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the existing recruitment instance
        $recruitment = $this->route('recruitment');

        // The company ID is fixed to the recruitment's existing company
        // We do not typically allow moving a job from one company to another during update
        $validationCompanyId = $recruitment->company_id;

        return [
            'title'            => ['required', 'string', 'max:255'],

            'department_id'    => [
                'required',
                'exists:departments,id',
                // Ensure the new department still belongs to the SAME company
                Rule::exists('departments', 'id')->where(function ($query) use ($validationCompanyId) {
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
            'description'      => ['required', 'string'],
            'key_skills_input' => ['nullable', 'string'],
            'status'           => ['required', Rule::in(['published', 'draft', 'closed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.exists' => 'The selected department is invalid or does not belong to the company associated with this job.',
        ];
    }
}
