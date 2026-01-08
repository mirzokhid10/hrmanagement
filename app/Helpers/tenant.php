<?php

use App\Models\Company;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant (Company).
     *
     * @return Company|null
     */
    function tenant(): ?Company
    {
        return Company::current();
    }
}

if (!function_exists('tenant_id')) {
    /**
     * Get the current tenant's ID.
     *
     * @return int|null
     */
    function tenant_id(): ?int
    {
        $tenant = tenant();
        return $tenant ? $tenant->id : null;
    }
}

if (!function_exists('tenant_name')) {
    /**
     * Get the current tenant's name.
     *
     * @return string|null
     */
    function tenant_name(): ?string
    {
        $tenant = tenant();
        return $tenant ? $tenant->name : null;
    }
}

if (!function_exists('tenant_subdomain')) {
    /**
     * Get the current tenant's subdomain.
     *
     * @return string|null
     */
    function tenant_subdomain(): ?string
    {
        $tenant = tenant();
        return $tenant ? $tenant->subdomain : null;
    }
}

if (!function_exists('tenant_url')) {
    /**
     * Generate a URL for the current tenant.
     *
     * @param string $path
     * @return string
     */
    function tenant_url(string $path = ''): string
    {
        $subdomain = tenant_subdomain();
        $baseDomain = config('app.url_base_domain', 'localhost');
        $protocol = config('app.env') === 'production' ? 'https' : 'http';

        if ($subdomain) {
            $url = "{$protocol}://{$subdomain}.{$baseDomain}";
        } else {
            $url = "{$protocol}://{$baseDomain}";
        }

        return $path ? rtrim($url, '/') . '/' . ltrim($path, '/') : $url;
    }
}

if (!function_exists('is_subdomain_request')) {
    /**
     * Check if the current request is on a subdomain.
     *
     * @return bool
     */
    function is_subdomain_request(): bool
    {
        return tenant() !== null && tenant_subdomain() !== null;
    }
}

if (!function_exists('subdomain_from_url')) {
    /**
     * Extract subdomain from a URL or host.
     *
     * @param string $url
     * @return string|null
     */
    function subdomain_from_url(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        $baseDomain = config('app.url_base_domain', 'localhost');

        if (str_ends_with($host, ".{$baseDomain}")) {
            $subdomain = str_replace(".{$baseDomain}", '', $host);
            return $subdomain !== 'www' ? $subdomain : null;
        }

        return null;
    }
}

if (!function_exists('current_company')) {
    /**
     * Alias for tenant() - Get the current company.
     *
     * @return Company|null
     */
    function current_company(): ?Company
    {
        return tenant();
    }
}
