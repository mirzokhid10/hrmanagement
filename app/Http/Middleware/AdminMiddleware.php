<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user is super admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            notify()->error('Access denied. Admin privileges required.');
            abort(403, 'Access denied. Admin privileges required.');
            return redirect()->route('login');
        }

        // For super admins, we don't set a tenant context
        // This allows them to see data across all tenants

        return $next($request);
    }
}
