<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameSession extends Model
{
    protected $fillable = [
        'status',
        'started_by',
        'counts',
        'note',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'counts' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}