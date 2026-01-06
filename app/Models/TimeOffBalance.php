<?php

namespace App\Models;

use App\Scopes\TenantScope; // Assuming you apply TenantScope
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class TimeOffBalance extends Model
{
    /** @use HasFactory<\Database\Factories\TimeOffBalanceFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employee_id',
        'time_off_type_id',
        'year',
        'allocated_days',
        'days_taken',
    ];

    protected $casts = [
        'year' => 'integer',
        'allocated_days' => 'decimal:1',
        'days_taken' => 'decimal:1',
    ];

    /**
     * The "booted" method of the model.
     * Apply TenantScope automatically.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (TimeOffBalance $timeOffBalance) {
            if (App::bound('tenant') && App::get('tenant') instanceof Company) {
                $timeOffBalance->company_id = App::get('tenant')->id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TimeOffType::class, 'time_off_type_id');
    }

    // Accessor for remaining days
    public function getDaysRemainingAttribute(): float
    {
        return $this->allocated_days - $this->days_taken;
    }
}
