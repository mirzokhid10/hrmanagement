<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'recruitment_id'); // or 'vacancy_id' depending on your migration
    }
}
