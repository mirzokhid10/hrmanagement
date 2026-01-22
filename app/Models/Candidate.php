<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'company_id',
        'recruitment_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo_path',
        'resume_path',
        'cover_letter',
        'status',
        'interview_scheduled_at',
        'hh_candidate_id',
        'hh_resume_id',
        'telegram_chat_id',
        'source'
    ];

    protected $casts = [
        'interview_scheduled_at' => 'datetime',
    ];

    /**
     * Get the Recruitment (Job) that this candidate applied for.
     */
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    // Helper to get full name
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
