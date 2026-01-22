<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingTemplate extends Model
{
    use TenantScoped;

    protected $fillable = ['company_id', 'title'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
