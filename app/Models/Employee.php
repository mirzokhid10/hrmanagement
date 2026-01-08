<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'first_name',
        'last_name',
        'image',
        'email',
        'phone_number',
        'address',
        'date_of_birth',
        'hire_date',
        'job_title',
        'department_id',
        'salary',
        'status',
        'reports_to',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Only apply tenant scope if there's a current tenant
        // This allows super admins to bypass the scope
        static::addGlobalScope(new TenantScope);

        // Automatically set company_id when creating
        static::creating(function (Employee $employee) {
            if ($employee->company_id) {
                return; // Already set, don't override
            }

            $tenant = tenant();
            if ($tenant) {
                $employee->company_id = $tenant->id;
            }
        });
    }

    /**
     * Get the company that owns the employee.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user account for this employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reports_to');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'reports_to');
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    public function timeOffBalances(): HasMany
    {
        return $this->hasMany(TimeOffBalance::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    /**
     * Scope to get employees across all tenants (for super admins)
     */
    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope to get employees for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where('company_id', $companyId);
    }
}
