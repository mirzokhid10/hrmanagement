<?php

namespace App\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {

        // CRITICAL: Skip scope entirely during login/authentication
        // This prevents queries from running TenantScope before user is loaded
        if ($this->isAuthenticationProcess()) {
            return;
        }

        // For User model - special handling to prevent infinite loops
        if ($model instanceof User) {
            // Only apply if user is already authenticated
            if (Auth::hasUser()) {
                $user = Auth::user();

                /** @var \App\Models\User $user */
                // Admins see everything
                if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                    return;
                }

                // Regular users see only their company
                if ($user && $user->company_id) {
                    $builder->where($model->getTable() . '.company_id', $user->company_id);
                }
            }
            return;
        }

        // For all other models
        if (Auth::check()) {
            $user = Auth::user();

            /** @var \App\Models\User $user */
            // Admins see everything
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return;
            }

            // Regular users see only their company data
            if ($user->company_id) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
            }
            return;
        }

        // Fall back to tenant binding (for public routes with tenant context)
        if (app()->bound('tenant') && app('tenant')) {
            $builder->where($model->getTable() . '.company_id', app('tenant')->id);
        }
    }

    /**
     * Check if we're in the middle of authentication process
     */
    protected function isAuthenticationProcess(): bool
    {
        // Check if we're on login routes
        $routeName = request()->route()?->getName();

        $authRoutes = [
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

        return in_array($routeName, $authRoutes);
    }
}
