<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledJob extends Model
{
    protected $fillable = [
        'name',
        'command',
        'cron',
        'is_active',
        'without_overlapping',
        'run_in_background',
        'description',
        'last_run_at',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'without_overlapping' => 'boolean',
        'run_in_background'   => 'boolean',
        'last_run_at'         => 'datetime',
    ];
}
