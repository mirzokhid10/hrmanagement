<?php

namespace App\Services\Interfaces;

use App\Models\EmployeeTask;
use Illuminate\Pagination\LengthAwarePaginator;

interface OnboardingServiceInterface
{
    public function getPaginatedTasks(?int $companyId, int $perPage = 10): LengthAwarePaginator;
    public function createTask(array $data): EmployeeTask;
    public function updateTask(EmployeeTask $task, array $data): EmployeeTask;
    public function deleteTask(EmployeeTask $task): bool;
}
