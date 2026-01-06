<?php

namespace App\Services\Interfaces;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EmployeeServiceInterface
{
    /**
     * Get a paginated list of employees for the current tenant.
     */
    public function getPaginatedEmployees(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all departments for the current tenant.
     */
    public function getDepartments(): Collection;

    /**
     * Find an employee by ID for the current tenant.
     */
    public function findEmployee(string $id): ?Employee;

    /**
     * Create a new employee and their associated user account.
     */
    public function createEmployee(array $data, ?UploadedFile $image = null): Employee;

    /**
     * Update an existing employee and their associated user account.
     */
    public function updateEmployee(Employee $employee, array $data, ?UploadedFile $image = null, bool $removeImage = false): Employee;

    /**
     * Delete an employee and their associated user account, including their profile image.
     */
    public function deleteEmployee(Employee $employee): bool;
}
