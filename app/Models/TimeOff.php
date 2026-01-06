<?php

namespace App\Models;

use App\Scopes\TenantScope; // Assuming you apply TenantScope
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class TimeOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employee_id',
        'time_off_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approver_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_days' => 'decimal:1',
    ];

    /**
     * The "booted" method of the model.
     * Apply TenantScope automatically.
     */

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Scopes\TenantScope);

        static::creating(function (TimeOff $timeOff) {
            if (App::bound('tenant') && App::get('tenant') instanceof Company) {
                $timeOff->company_id = App::get('tenant')->id;
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Helper to check if the time off is pending
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    // Helper to check if the time off is approved
    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    // Helper to check if the time off is rejected
    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }
}
