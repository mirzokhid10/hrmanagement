<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if ($model->company_id) {
                return;
            }

            $tenant = static::getCurrentTenant();
            if ($tenant) {
                $model->company_id = $tenant->id;
            }
        });
    }

    protected static function getCurrentTenant(): ?Company
    {
        // ✅ FIX: Pure custom logic, no Spatie dependency

        // 1. Try static property on Company model
        if (method_exists(Company::class, 'current')) {
            return Company::current();
        }

        // 2. Fallback to Service Container
        if (app()->has('tenant')) {
            return app('tenant');
        }

        return null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
