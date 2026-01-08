<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class TimeOffBalance extends Model
{
    /** @use HasFactory<\Database\Factories\TimeOffBalanceFactory> */
    use HasFactory, TenantScoped; // <-- Add trait

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
