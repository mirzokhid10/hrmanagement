<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeInsight extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'type',
        'score',
        'risk_level',
        'factors',
        'ai_analysis_uz',
        'ai_analysis_ru',
    ];

    protected $casts = [
        'factors' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
