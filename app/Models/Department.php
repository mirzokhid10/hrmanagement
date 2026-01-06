<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {

        static::addGlobalScope('company', function (Builder $builder) {
            if (app()->bound('tenant') && app('tenant') instanceof Company) {
                $builder->where('company_id', app('tenant')->id);
            }
        });


        static::creating(function ($department) {
            if (app()->bound('tenant') && app('tenant') instanceof Company) {
                $department->company_id = app('tenant')->id;
            }
        });
    }
}