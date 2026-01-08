<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantScoped;

class Department extends Model
{
    use HasFactory, TenantScoped;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];

    /**
     * Get the company that owns the department.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employees for the department.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
