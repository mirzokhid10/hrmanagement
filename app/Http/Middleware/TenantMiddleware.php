<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = null;
        $fullHost = $request->getHost();
        $configuredBaseDomain = config('app.url_base_domain', 'localhost');

        $hostWithoutPort = explode(':', $fullHost)[0];
        $subdomain = null;
        $isBaseDomainRequest = false;

        // Determine subdomain
        if ($hostWithoutPort === $configuredBaseDomain) {
            $isBaseDomainRequest = true;
        } elseif (Str::startsWith($hostWithoutPort, 'www.') && Str::after($hostWithoutPort, 'www.') === $configuredBaseDomain) {
            $isBaseDomainRequest = true;
        } elseif (Str::endsWith($hostWithoutPort, '.' . $configuredBaseDomain)) {
            $potentialSubdomain = Str::before($hostWithoutPort, '.' . $configuredBaseDomain);
            if ($potentialSubdomain !== 'www') {
                $subdomain = $potentialSubdomain;
            } else {
                $isBaseDomainRequest = true;
            }
        } else {
            $isBaseDomainRequest = true;
        }

        // PERFORMANCE: Cache company lookups for 1 hour
        $cacheKey = 'tenant_' . ($subdomain ?? 'base_domain');

        $company = Cache::remember($cacheKey, 3600, function () use ($isBaseDomainRequest, $subdomain) {
            if ($isBaseDomainRequest) {
                return Company::whereNull('subdomain')->first();
            } elseif ($subdomain) {
                return Company::where('subdomain', $subdomain)->first();
            }
            return null;
        });

        if ($company) {
            App::instance('tenant', $company);
            // Don't log on every request - too slow
            // Log::info('Tenant bound', ['company_id' => $company->id]);
        } else {
            if ($subdomain) {
                Log::warning('Company not found for subdomain', ['subdomain' => $subdomain]);
                abort(404, 'Company not found for this subdomain.');
            }

            if (App::bound('tenant')) {
                App::offsetUnset('tenant');
            }
        }

        return $next($request);
    }
}
