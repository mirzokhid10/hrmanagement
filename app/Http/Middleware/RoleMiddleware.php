<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'Unauthorized. You do not have the necessary role to access this page.');
        }

        // If your User model has a hasRole() method:
        // if (!Auth::user()->hasRole($role)) {
        //     abort(403, 'Unauthorized. You do not have the necessary role to access this page.');
        // }

        return $next($request);
    }
}
