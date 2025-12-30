<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // --- 1. Identify the Tenant ---

        // For local development, you might not have a subdomain.
        // We can check the environment or host and provide a default company for local testing.
        if (App::environment('local', 'testing') && $request->getHost() === 'localhost') {
            // In local development, you might want to hardcode a company ID or fetch the first one
            $company = Company::first(); // Or Company::find(1);
            if (!$company) {
                // If no company exists yet, allow initial setup (e.g., Super Admin registration)
                return $next($request);
            }
        } else {
            // For production or when a subdomain is expected
            // Extract subdomain from the host (e.g., 'acme' from 'acme.yourapp.com')
            $hostParts = explode('.', $request->getHost());
            $subdomain = $hostParts[0];

            if (count($hostParts) < 2) {
                // Not a subdomain (e.g., yourapp.com without a subdomain).
                // This might be your main marketing site or a super admin portal.
                // For now, we'll just allow the request to pass without a tenant context.
                // You'll need specific routes for non-tenant-specific pages.
                return $next($request);
            }

            $company = Company::where('subdomain', $subdomain)->first();

            if (!$company) {
                // No company found for this subdomain.
                // Redirect to a landing page, a "company not found" page, or abort.
                // For a SaaS app, you usually don't want to show a generic 404 for this.
                // For now, we'll abort, but consider a redirect.
                abort(404, 'Company not found for this subdomain.');
            }
        }

        // --- 2. Set the current tenant globally for this request ---
        // This makes the Company model instance available throughout the application
        // using `App::get('tenant')` or `app('tenant')`.
        App::instance('tenant', $company);

        return $next($request);
    }
}
