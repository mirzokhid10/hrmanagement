<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_lat',
        'check_in_lon',
        'check_out_lat',
        'check_out_lon',
        'is_location_verified',
        'is_wifi_verified',
        'check_in_ip',
        'status',
        'late_minutes',
        'work_hours',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'date' => 'date',
        'is_location_verified' => 'boolean',
        'is_wifi_verified' => 'boolean',
    ];
}
