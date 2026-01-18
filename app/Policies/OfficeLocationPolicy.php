<?php

namespace App\Policies;

use App\Models\OfficeLocation;
use App\Models\User; // Assuming your User model is in App\Models
use Illuminate\Auth\Access\Response;

class OfficeLocationPolicy
{
    /**
     * Determine whether the user can view any models.
     * Super Admin: Can view all.
     * HR Manager: Can view office locations belonging to their company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     * (Typically, if they can viewAny, they can view a specific one,
     * but we'll add the company check for robustness).
     * Super Admin: Can view any.
     * HR Manager: Can view office locations belonging to their company.
     */
    public function view(User $user, OfficeLocation $officeLocation): bool
    {
        return $user->isAdmin() || ($user->company_id === $officeLocation->company_id);
    }

    /**
     * Determine whether the user can create models.
     * Super Admin: Can create for any company.
     * HR Manager: Can create for their own company.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     * Super Admin: Can update any.
     * HR Manager: Can update office locations belonging to their company.
     */
    public function update(User $user, OfficeLocation $officeLocation): bool
    {
        return $user->isAdmin() || ($user->company_id === $officeLocation->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     * Super Admin: Can delete any.
     * HR Manager: Can delete office locations belonging to their company.
     */
    public function delete(User $user, OfficeLocation $officeLocation): bool
    {
        return $user->isAdmin() || ($user->company_id === $officeLocation->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     * (If you use soft deletes)
     */
    public function restore(User $user, OfficeLocation $officeLocation): bool
    {
        return $user->isAdmin() || ($user->company_id === $officeLocation->company_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * (If you use soft deletes)
     */
    public function forceDelete(User $user, OfficeLocation $officeLocation): bool
    {
        return $user->isAdmin() || ($user->company_id === $officeLocation->company_id);
    }
}
