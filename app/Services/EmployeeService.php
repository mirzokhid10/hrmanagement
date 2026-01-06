<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User; // Still need User model for login accounts
use App\Services\Interfaces\EmployeeServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeService implements EmployeeServiceInterface
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function getCurrentTenant(): Company
    {
        if (!$this->app->bound('tenant') || !$this->app->get('tenant') instanceof Company) {
            throw new \RuntimeException("No tenant is bound to the application. Ensure TenantMiddleware is active and successfully identified a company.");
        }
        return $this->app->get('tenant');
    }

    public function getPaginatedEmployees(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $currentCompany = $this->getCurrentTenant();

        $query = Employee::with(['department'])
            ->where('company_id', $currentCompany->id); // Explicitly scope, though global scope helps

        // Apply search filter if a search term is provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('job_title', 'like', '%' . $searchTerm . '%');

                // Search in department name
                $q->orWhereHas('department', function ($dq) use ($searchTerm) {
                    $dq->where('name', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        return $query->paginate($perPage);
    }

    public function getDepartments(): Collection
    {
        $currentCompany = $this->getCurrentTenant();
        return Department::where('company_id', $currentCompany->id)->get();
    }

    public function findEmployee(string $id): ?Employee
    {
        // Employee model has global scope, so where('company_id', ...) is technically redundant but harmless.
        return Employee::with(['department'])->find($id);
    }

    public function createEmployee(array $data, ?UploadedFile $image = null): Employee
    {
        $currentCompany = $this->getCurrentTenant();

        return DB::transaction(function () use ($data, $image, $currentCompany) {
            // 1. Create the User account for the new employee (for login)
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(10)), // Auto-generate a temporary password
                'company_id' => $currentCompany->id, // Associate user with the current tenant for login
                'email_verified_at' => now(), // Assume verified for new internal employees
            ]);

            // 2. Handle profile image upload
            // We use the newly created Employee ID for filename (or a temp ID if not yet created).
            // A common approach is to store the image after the employee record is created,
            // or use a temporary unique identifier for the filename.
            // For now, let's use a UUID or similar to name the image, then update employee.
            $profileImagePath = $this->handleProfileImageUpload(Str::uuid(), $image); // Use UUID for initial filename

            // 3. Create the Employee profile
            $employee = Employee::create([
                'company_id' => $currentCompany->id,
                'department_id' => $data['department_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'job_title' => $data['job_title'],
                'hire_date' => $data['hire_date'],
                'status' => $data['status'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'image' => $profileImagePath, // Store image path
                'salary' => $data['salary'] ?? null,
            ]);

            // If image was uploaded, rename it using the actual employee ID
            if ($profileImagePath && $image) {
                $oldPath = $profileImagePath;
                $newFileName = $employee->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $directory = 'employees/' . $currentCompany->slug;
                $newPath = $image->storeAs($directory, $newFileName, 'public'); // Store again with new name

                // Delete the old file if it's different and exists
                if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $employee->update(['image' => $newPath]); // Update employee with final image path
            }
            return $employee;
        });
    }

    public function updateEmployee(Employee $employee, array $data, ?UploadedFile $image = null, bool $removeImage = false): Employee
    {
        $currentCompany = $this->getCurrentTenant();

        if ($employee->company_id !== $currentCompany->id) {
            throw new \Exception("Employee does not belong to the current company.");
        }

        return DB::transaction(function () use ($employee, $data, $image, $removeImage, $currentCompany) {
            // Update User account (login credentials) - find by email and company_id
            $user = User::where('email', $employee->email)
                ->where('company_id', $currentCompany->id)
                ->first();

            if ($user) {
                $user->update([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                ]);
            } else {
                \Log::warning("Employee ID: {$employee->id} (Email: {$employee->email}) has no associated User login account. Creating one.");
                User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(10)), // Generate a new password
                    'company_id' => $currentCompany->id,
                    'email_verified_at' => now(),
                ]);
            }

            // Handle profile image update/removal
            $profileImagePath = $employee->image;
            if ($removeImage && $profileImagePath) {
                Storage::disk('public')->delete($profileImagePath);
                $profileImagePath = null;
            } elseif ($image) {
                if ($profileImagePath) {
                    Storage::disk('public')->delete($profileImagePath); // Delete old image
                }
                $profileImagePath = $this->handleProfileImageUpload($employee->id, $image); // Use employee ID
            }

            // Update Employee profile
            $employee->update([
                'department_id' => $data['department_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'job_title' => $data['job_title'],
                'hire_date' => $data['hire_date'],
                'status' => $data['status'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'image' => $profileImagePath,
                'salary' => $data['salary'] ?? null,
            ]);

            return $employee;
        });
    }

    public function deleteEmployee(Employee $employee): bool
    {
        $currentCompany = $this->getCurrentTenant();

        if ($employee->company_id !== $currentCompany->id) {
            throw new \Exception("Employee does not belong to the current company.");
        }

        return DB::transaction(function () use ($employee) {
            // Delete profile image if exists
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }

            // Delete the associated User login account
            User::where('email', $employee->email)
                ->where('company_id', $employee->company_id)
                ->delete();

            return $employee->delete();
        });
    }


    /**
     * Helper method to handle profile image upload.
     * Uses an identifier (e.g., employee ID, UUID) for filename.
     */
    protected function handleProfileImageUpload(string $identifier, ?UploadedFile $image): ?string
    {
        if (!$image) {
            return null;
        }

        $currentCompany = $this->getCurrentTenant();
        $directory = 'employees/' . $currentCompany->slug;
        $fileName = $identifier . '_' . time() . '.' . $image->getClientOriginalExtension();
        return $image->storeAs($directory, $fileName, 'public');
    }
}
