<?php

// ==========================================
// TimeOffType.php
// ==========================================

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeOffType extends Model
{
    use HasFactory, TenantScoped; // <-- Add trait

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_paid',
        'default_days_per_year',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'default_days_per_year' => 'integer',
    ];

    // No booted() method needed - trait handles it!

    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(TimeOffBalance::class);
    }
}
