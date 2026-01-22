<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruitment extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'company_id',
        'title',
        'department_id',
        'job_type',
        'salary_range',
        'experience',
        'schedule',
        'working_hours',
        'location',
        'deadline',
        'description',
        'key_skills',
        'hh_vacancy_id',
        'hh_professional_role_id',
        'billing_type',
        'hh_url',
        'status'
    ];

    // Automatically convert JSON to Array when accessing
    protected $casts = [
        'key_skills' => 'array',
        'deadline' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'recruitment_id'); // or 'vacancy_id' depending on your migration
    }

    public function getTelegramApplyLinkAttribute()
    {


        $botUsername = config('services.telegram.bot_username');

        // Fallback if config is missing (for safety)
        if (!$botUsername) {
            notify()->error('No username is found');
            return back();
        }

        // Returns: https://t.me/MyUzbekHrBot?start=apply_5
        return "https://t.me/{$botUsername}?start=apply_{$this->id}";
    }
}
