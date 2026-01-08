<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            // Only set company_id automatically if not super admin
            if (!$user->company_id && app()->bound('tenant') && app('tenant')) {
                $user->company_id = app('tenant')->id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if this user is a super admin.
     * Super admins can access all tenants and bypass tenant scoping.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if this user is an HR manager.
     */
    public function isHR(): bool
    {
        return $this->hasRole('hr');
    }

    /**
     * Check if user can access all companies (super admin only).
     */
    public function canAccessAllCompanies(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage a specific company.
     */
    public function canManageCompany(Company $company): bool
    {
        // Super admin can manage all companies
        if ($this->isAdmin()) {
            return true;
        }

        // Admin/HR can only manage their own company
        return $this->company_id === $company->id && ($this->isAdmin() || $this->isHRManager());
    }

    /**
     * Get the tenant (company) for this user.
     * Returns null for super admins.
     */
    public function getTenant(): ?Company
    {
        if ($this->isAdmin()) {
            return null; // Super admins don't belong to a tenant
        }

        return $this->company;
    }

    /**
     * Check if user should bypass tenant scoping.
     */
    public function shouldBypassTenantScope(): bool
    {
        return $this->isAdmin();
    }
}
