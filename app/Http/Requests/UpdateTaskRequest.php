<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $this->route('onboarding'); // Route parameter name

        // Admin or HR of the same company
        return $user->isAdmin() || ($task && $task->company_id === $user->company_id);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['new', 'in_progress', 'pending', 'completed'])],
            // Usually we don't change the assigned employee during an update,
            // but if you want to allow it, add employee_id validation here similar to StoreRequest
        ];
    }
}
