<?php

namespace App\Models;

use App\Scopes\TenantScope; // Assuming you apply TenantScope
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class TimeOffType extends Model
{
    /** @use HasFactory<\Database\Factories\TimeOffTypeFactory> */
    use HasFactory;

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

    /**
     * The "booted" method of the model.
     * Apply TenantScope automatically.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (TimeOffType $timeOffType) {
            if (App::bound('tenant') && App::get('tenant') instanceof Company) {
                $timeOffType->company_id = App::get('tenant')->id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(TimeOffBalance::class);
    }
}
