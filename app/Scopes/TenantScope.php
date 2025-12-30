<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

class TenantScope implements Scope
{
    /**
     * Create a new class instance.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (App::bound('tenant') && App::get('tenant')) {
            $builder->where('company_id', App::get('tenant')->id);
        }
    }
}
