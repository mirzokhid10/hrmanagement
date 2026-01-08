<?php

// ==========================================
// TimeOff.php
// ==========================================

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeOff extends Model
{
    use HasFactory, TenantScoped; // <-- Add trait

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

    // No booted() method needed - trait handles it!

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

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }
}