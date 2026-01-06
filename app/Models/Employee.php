<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'first_name',
        'last_name',
        'image',
        'email',
        'phone_number',
        'address',
        'date_of_birth',
        'hire_date',
        'job_title',
        'department_id',
        'salary',
        'status',
        'reports_to',
    ];



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Apply global scope to filter employees by the current tenant's company_id
        static::addGlobalScope(new TenantScope);

        static::creating(function (Employee $employee) {
            if (App::bound('tenant') && App::get('tenant') instanceof Company) {
                $employee->company_id = App::get('tenant')->id;
            }
        });
    }

    /**
     * Get the company that owns the employee.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo // <-- NEW RELATIONSHIP: An employee *can* have a user account
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class); // Ensure Department model exists if used
    }

    public function manager(): BelongsTo // <-- NEW RELATIONSHIP: Who this employee reports to
    {
        return $this->belongsTo(Employee::class, 'reports_to');
    }

    public function directReports(): HasMany // <-- NEW RELATIONSHIP: Employees who report to this employee
    {
        return $this->hasMany(Employee::class, 'reports_to');
    }


    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    public function timeOffBalances(): HasMany
    {
        return $this->hasMany(TimeOffBalance::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }
}
