<?php

namespace App\Services\Interfaces;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EmployeeServiceInterface
{
    /**
     * Get a paginated list of employees.
     * Add nullable $companyId for admin bypass.
     */
    public function getPaginatedEmployees(?int $companyId, int $perPage = 8, ?string $searchTerm = null): LengthAwarePaginator;

    /**
     * Get all departments.
     * Departments are typically company-specific, so keep $companyId required.
     */
    public function getDepartments(int $companyId): Collection;

    /**
     * Find an employee by ID.
     * No change needed here, the TenantScope on Employee model will handle the admin bypass.
     */
    public function findEmployee(string $id): ?Employee;

    /**
     * Create a new employee and their associated user account.
     * If an admin creates an employee for a different company, $data should include 'company_id'.
     * For now, assume creation is always for the currently bound tenant.
     */
    public function createEmployee(array $data, ?UploadedFile $image = null, ?int $companyId = null): Employee;

    /**
     * Update an existing employee and their associated user account.
     * No nullable companyId here, as the `Employee` model itself has `company_id`.
     */
    public function updateEmployee(Employee $employee, array $data, ?UploadedFile $image = null, bool $removeImage = false): Employee;

    /**
     * Delete an employee and their associated user account, including their profile image.
     */
    public function deleteEmployee(Employee $employee): bool;
}
