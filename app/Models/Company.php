<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->hasMany(Employee::class); // Assuming an Employee model exists
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    protected static function booted(): void
    {
        // IMPORTANT: DO NOT ADD TenantScope here.
        // The Company model itself is the entity that defines the tenant.
        // It should not be tenant-scoped by its own mechanism.

        // If you had other global scopes or creating callbacks for Company,
        // they would go here. For example, if you wanted to auto-set owner_id:
        // static::creating(function (Company $company) {
        //     if (Auth::check()) {
        //         $company->user_id = Auth::id();
        //     }
        // });
    }
}
