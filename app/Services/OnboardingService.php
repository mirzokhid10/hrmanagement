<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Services\Interfaces\OnboardingServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Scopes\TenantScope;

class OnboardingService implements OnboardingServiceInterface
{

    public function getPaginatedTasks(?int $companyId, int $perPage = 10): LengthAwarePaginator
    {
        $query = EmployeeTask::with(['employee']);
        $user = Auth::user();
        /** @var \App\Models\User $user */
        if ($user->isAdmin()) {
            if ($companyId === null) {
                $query->withoutGlobalScope(TenantScope::class);
            } else {
                $query->where('company_id', $companyId);
            }
        } else {
            $query->where('company_id', $user->company_id);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createTask(array $data): EmployeeTask
    {
        // We need to derive the company_id from the selected employee
        // This ensures data consistency
        $employee = Employee::withoutGlobalScope(TenantScope::class)->find($data['employee_id']);

        if (!$employee) {
            throw new \Exception("Employee not found.");
        }

        return DB::transaction(function () use ($data, $employee) {
            return EmployeeTask::create([
                'company_id' => $employee->company_id, // Inherit from employee
                'employee_id' => $employee->id,
                'title' => $data['title'],
                'content' => $data['description'] ?? '', // Mapping description to content column
                'priority' => $data['priority'],
                'status' => $data['status'],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'requires_upload' => false, // Default
                'is_completed' => $data['status'] === 'completed',
            ]);
        });
    }

    public function updateTask(EmployeeTask $task, array $data): EmployeeTask
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update([
                'title' => $data['title'],
                'content' => $data['description'] ?? '',
                'priority' => $data['priority'],
                'status' => $data['status'],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'is_completed' => $data['status'] === 'completed',
            ]);
            return $task;
        });
    }

    public function deleteTask(EmployeeTask $task): bool
    {
        return $task->delete();
    }
}
