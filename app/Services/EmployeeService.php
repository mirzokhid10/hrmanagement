<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Interfaces\EmployeeServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Scopes\TenantScope; // Ensure this is imported for withoutGlobalScope

class EmployeeService implements EmployeeServiceInterface
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Helper to safely get the current bound tenant.
     * Returns null if no tenant is bound.
     */
    protected function getBoundTenant(): ?Company
    {
        return $this->app->bound('tenant') && $this->app->get('tenant') instanceof Company
            ? $this->app->get('tenant')
            : null;
    }

    /**
     * Get a paginated list of employees.
     * @param ?int $companyId If null, TenantScope will apply (or bypass for admin).
     */
    public function getPaginatedEmployees(?int $companyId, int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = Employee::with(['department']);

        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        if ($loggedInUser->isAdmin()) {
            // Admins can potentially see employees from all companies.
            // If $companyId is null, remove the global TenantScope to see all.
            // If $companyId is provided, filter specifically by that company.
            if ($companyId === null) {
                $query->withoutGlobalScope(TenantScope::class);
            } else {
                $query->where('company_id', $companyId);
            }
        } else {
            // Non-admins (HR) always see only their company's employees.
            // The global TenantScope should already handle this if a tenant is bound.
            // If for some reason a tenant isn't bound, fall back to the user's company_id.
            $query->where('company_id', $loggedInUser->company_id);
        }

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('job_title', 'like', '%' . $searchTerm . '%');

                $q->orWhereHas('department', function ($dq) use ($searchTerm) {
                    $dq->where('name', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all departments for a specific company.
     * @param int $companyId The ID of the company to retrieve departments for.
     */
    public function getDepartments(int $companyId): Collection
    {
        return Department::where('company_id', $companyId)->get();
    }

    /**
     * Find an employee by ID.
     * TenantScope handles admin bypass/regular user scoping.
     */
    public function findEmployee(string $id): ?Employee
    {
        // The Employee model's TenantScope will automatically filter by the bound tenant's company_id
        // unless the current user is an admin and the scope is bypassed (e.g., using withoutGlobalScope).
        return Employee::with(['department'])->find($id);
    }

    /**
     * Create a new employee (without creating a User login account by default).
     * @param array $data Employee data.
     * @param ?UploadedFile $image Profile image.
     * @param ?int $companyId Optional company ID to create the employee for (for admins).
     */
    public function createEmployee(array $data, ?UploadedFile $image = null, ?int $companyId = null): Employee
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        $targetCompanyId = null;

        if ($loggedInUser->isAdmin()) {
            // Admin must provide company_id in data or through $companyId parameter
            $targetCompanyId = $companyId ?? ($data['company_id'] ?? null);
            if (!$targetCompanyId) {
                throw new \InvalidArgumentException("Admin must specify a company ID for employee creation.");
            }
        } else {
            // Non-admin (HR) can only create employees for their own company
            $targetCompanyId = $loggedInUser->company_id;
        }

        $targetCompany = Company::find($targetCompanyId);
        if (!$targetCompany) {
            throw new \InvalidArgumentException("Target company (ID: {$targetCompanyId}) not found.");
        }

        return DB::transaction(function () use ($data, $image, $targetCompany, $targetCompanyId) {
            $profileImagePath = null;
            if ($image) {
                // Use a temporary UUID for the file name initially.
                // We'll rename it after the employee is created with their actual ID.
                $tempIdentifier = Str::uuid()->toString();
                $profileImagePath = $this->handleProfileImageUpload($tempIdentifier, $image, $targetCompany);
            }

            $employee = Employee::create(array_merge($data, [
                'company_id' => $targetCompanyId,
                'user_id' => null, // Explicitly set to null as employees don't have login accounts by default
                'image' => $profileImagePath,
            ]));

            // If an image was uploaded with a temporary UUID, rename it using the actual employee ID
            if ($profileImagePath && $image && Str::contains($profileImagePath, $tempIdentifier)) {
                $oldPath = $profileImagePath;
                $newFileName = $employee->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $directory = 'employees/' . $targetCompany->slug;
                $newPath = Storage::disk('public')->putFileAs($directory, $image, $newFileName);

                if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $employee->update(['image' => $newPath]);
            }
            return $employee;
        });
    }

    /**
     * Update an existing employee.
     */
    public function updateEmployee(Employee $employee, array $data, ?UploadedFile $image = null, bool $removeImage = false): Employee
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        // Security check: Only allow update if employee belongs to current tenant OR logged-in user is admin
        // This check is duplicated from the controller for robustness, though the controller should handle authorization.
        if (!$loggedInUser->isAdmin() && $employee->company_id !== $loggedInUser->company_id) {
            throw new \Exception("Unauthorized: Employee does not belong to your company context and you are not an admin.");
        }

        return DB::transaction(function () use ($employee, $data, $image, $removeImage) {
            $profileImagePath = $employee->image;

            if ($removeImage && $profileImagePath) {
                Storage::disk('public')->delete($profileImagePath);
                $profileImagePath = null;
            } elseif ($image) {
                if ($profileImagePath) {
                    Storage::disk('public')->delete($profileImagePath); // Delete old image
                }
                $profileImagePath = $this->handleProfileImageUpload($employee->id, $image, $employee->company);
            }

            $employee->update(array_merge($data, [
                'image' => $profileImagePath,
            ]));

            return $employee;
        });
    }

    /**
     * Delete an employee.
     */
    public function deleteEmployee(Employee $employee): bool
    {
        /** @var \App\Models\User $loggedInUser */
        $loggedInUser = Auth::user();

        // Security check: Only allow delete if employee belongs to current tenant OR logged-in user is admin
        if (!$loggedInUser->isAdmin() && $employee->company_id !== $loggedInUser->company_id) {
            throw new \Exception("Unauthorized: Employee does not belong to your company context and you are not an admin.");
        }

        return DB::transaction(function () use ($employee) {
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }
            return $employee->delete();
        });
    }

    /**
     * Helper method to handle profile image upload.
     * Requires the Company object for directory path.
     * @param string|int $identifier Unique identifier for the file (e.g., employee ID, UUID).
     * @param ?UploadedFile $image The uploaded file.
     * @param Company $company The company context for storing the image.
     * @return ?string The path to the stored image, or null if no image.
     */
    protected function handleProfileImageUpload(string|int $identifier, ?UploadedFile $image, Company $company): ?string
    {
        if (!$image) {
            return null;
        }

        $directory = 'employees/' . $company->slug;
        $fileName = $identifier . '_' . time() . '.' . $image->getClientOriginalExtension();
        return $image->storeAs($directory, $fileName, 'public');
    }
}
