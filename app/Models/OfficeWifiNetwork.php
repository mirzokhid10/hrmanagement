<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeWifiNetwork extends Model
{
    protected $fillable = [
        'company_id',
        'office_location_id',
        'network_name',
        'ip_range',
        'is_active',
        'description',
    ];

    protected $casts = [
        // 'ip_range' => 'decimal:7',  <-- DELETE THIS LINE! IP is a string.
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->company_id) && tenant()) {
                $model->company_id = tenant()->id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    /**
     * Check if an IP address is within the configured range
     */
    public function isIpInRange(string $ip): bool
    {
        if (!$this->ip_range) {
            return false;
        }

        // Support CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($this->ip_range, '/') !== false) {
            return $this->ipInCIDR($ip, $this->ip_range);
        }

        // Support single IP address
        return $ip === $this->ip_range;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInCIDR(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);

        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = ~((1 << (32 - $mask)) - 1);

        return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
    }
}
