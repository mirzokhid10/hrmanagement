<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTask extends Model
{

    use TenantScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'title',
        'content',
        'requires_upload',
        'status',
        'priority',
        'is_completed',
        'completed_at',
        'start_date',
        'due_date',
        'description'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'requires_upload' => 'boolean',
        'completed_at' => 'datetime',
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
