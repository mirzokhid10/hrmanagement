<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDocumentRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:contract,id_card,policy,other,resume,offer_letter'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,png,doc,docx', 'max:10240'],
            'expiry_date' => ['nullable', 'date'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ];

        /** @var \App\Models\User $user */
        $user = $this->user();

        // If user is Admin (no company_id) and it's a Policy (no employee_id), require company_id
        if ($user->isAdmin() && !$this->has('employee_id')) {
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        return $rules;
    }
}
