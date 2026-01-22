<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    protected $fillable = ['meeting_id', 'owner_id', 'task', 'is_completed', 'due_date'];

    protected $casts = ['is_completed' => 'boolean', 'due_date' => 'date'];
}
