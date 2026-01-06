<?php

namespace App\Scopes;

use App\Models\User; // Still need User model for isAdmin check
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. CRITICAL: Skip scope entirely during authentication processes
        if ($this->isAuthenticationProcess()) {
            return;
        }

        // IMPORTANT: If the model being queried is the User model itself,
        // we explicitly do NOT apply this TenantScope.
        // The User model is the source of truth for the tenant context,
        // and applying this scope to it causes recursion/circular dependency.
        if ($model instanceof User) {
            return; // Do not apply this TenantScope to the User model.
        }

        // 2. If a user is authenticated
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            // Auth::user() is now safe to call because the User model no longer has this global scope.
            $user = Auth::user();

            // If the authenticated user is an admin, they see everything.
            if ($user->isAdmin()) {
                return; // Admin bypass: do not apply any scope filter
            }

            // For non-admin users (like HR managers), apply the scope to their company_id
            if ($user->company_id) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
                return; // Scope applied for non-admin user
            }
        }

        // 3. Fallback for guests (no authenticated user) with a bound tenant
        // This is for public routes where a tenant (e.g., via subdomain) is identified.
        if (App::bound('tenant') && App::get('tenant')) {
            $builder->where($model->getTable() . '.company_id', app('tenant')->id);
            return; // Scope applied for guest with bound tenant
        }

        // 4. Default: If no user, no admin, and no tenant bound, no scope is applied.
        // This means queries will return all records. Ensure routes requiring tenant context
        // or authentication are protected by middleware.
    }

    /**
     * Check if we're in the middle of authentication process
     */
    protected function isAuthenticationProcess(): bool
    {
        $routeName = request()->route()?->getName();
        $requestPath = request()->path();

        $authRouteNames = [
            'login',
            'login.post',
            'logout',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'register',
            'verification.notice',
            'verification.verify',
            'verification.send',
        ];

        if ($routeName && in_array($routeName, $authRouteNames)) {
            return true;
        }

        if (Str::startsWith($requestPath, 'login') || Str::startsWith($requestPath, 'register') || Str::startsWith($requestPath, 'password')) {
            return true;
        }

        return false;
    }
}
