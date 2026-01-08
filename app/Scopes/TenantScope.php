<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Get current tenant from Laravel's service container
        $tenant = tenant();

        // Only apply scope if there's a current tenant
        // This allows super admins to query without tenant filter
        if ($tenant && $tenant->id) {
            $builder->where($model->getTable() . '.company_id', $tenant->id);
        }
    }

    /**
     * Extend the query builder with helpful methods
     */
    public function extend(Builder $builder): void
    {
        // Add withoutTenantScope method
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // Add forTenant method
        $builder->macro('forTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                ->where($builder->getModel()->getTable() . '.company_id', $tenantId);
        });

        // Add allTenants method
        $builder->macro('allTenants', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
