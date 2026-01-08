<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Company Model - Represents a tenant in the system
 *
 * Each company is a separate tenant with isolated data.
 * We use a custom tenant implementation instead of Spatie's Tenant contract
 * for maximum flexibility.
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the owner (User) of the company.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the employees for the company.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the departments for the company.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the time off types for the company.
     */
    public function timeOffTypes(): HasMany
    {
        return $this->hasMany(TimeOffType::class);
    }

    /**
     * Make this company the current tenant.
     * This method is called by TenantMiddleware.
     */
    public function makeCurrent(): self
    {
        // Bind to Laravel's service container
        app()->instance('tenant', $this);
        app()->instance('currentTenant', $this);

        // Store in static property for quick access
        static::$currentTenant = $this;

        return $this;
    }

    /**
     * Forget the current tenant.
     */
    public static function forgetCurrent(): void
    {
        app()->forgetInstance('tenant');
        app()->forgetInstance('currentTenant');
        static::$currentTenant = null;
    }

    /**
     * Get the current tenant.
     *
     * @return static|null
     */
    public static function current(): ?self
    {
        // Try static property first (fastest)
        if (static::$currentTenant !== null) {
            return static::$currentTenant;
        }

        // Fall back to service container
        return app()->has('tenant') ? app('tenant') : null;
    }

    /**
     * Check if this tenant is current.
     */
    public function isCurrent(): bool
    {
        return optional(static::current())->id === $this->id;
    }

    /**
     * Static property to hold current tenant (for performance)
     */
    protected static ?self $currentTenant = null;

    /**
     * Check if a subdomain is reserved and cannot be used.
     */
    public static function isSubdomainReserved(string $subdomain): bool
    {
        $reserved = config('onboard.reserved_subdomains', [
            'www',
            'admin',
            'api',
            'app',
            'mail',
            'ftp',
            'blog',
            'shop',
            'support',
            'help',
            'dev',
            'staging',
            'test'
        ]);

        return in_array(strtolower($subdomain), $reserved);
    }

    /**
     * Check if a subdomain is available for registration.
     */
    public static function isSubdomainAvailable(string $subdomain): bool
    {
        if (static::isSubdomainReserved($subdomain)) {
            return false;
        }

        return !static::where('subdomain', $subdomain)->exists();
    }

    /**
     * Generate a slug from the company name.
     */
    public static function generateSlug(string $name): string
    {
        return \Illuminate\Support\Str::slug($name);
    }

    protected static function booted(): void
    {
        // Company model is not tenant-scoped itself
        // It DEFINES tenants, so no TenantScope here

        // Auto-generate slug from name if not provided
        static::creating(function (Company $company) {
            if (empty($company->slug) && !empty($company->name)) {
                $company->slug = static::generateSlug($company->name);
            }

            // Ensure subdomain is lowercase
            if (!empty($company->subdomain)) {
                $company->subdomain = strtolower($company->subdomain);
            }
        });

        static::updating(function (Company $company) {
            // Ensure subdomain is lowercase
            if (!empty($company->subdomain)) {
                $company->subdomain = strtolower($company->subdomain);
            }
        });
    }
}
