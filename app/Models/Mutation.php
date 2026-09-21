<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutation extends Model
{
    protected $fillable = [
        'document_number',
        'type',
        'item_id',
        'batch',
        'expired_at',
        'quantity',
        'rack',
        'pic',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expired_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
