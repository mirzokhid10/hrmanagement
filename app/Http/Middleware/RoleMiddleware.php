<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        /** @var User $user */
        $user = Auth::user();

        // Split roles by | to allow multiple roles
        $roles = explode('|', $role);

        // Check if user has any of the required roles (Spatie Permission method)
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized. You do not have the necessary role to access this page.');
        }

        return $next($request);
    }
}
