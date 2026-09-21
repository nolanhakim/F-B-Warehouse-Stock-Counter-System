<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'category',
        'unit_buy',
        'unit_count',
        'ratio',
        'safety_stock',
        'rack',
        'is_active',
    ];

    protected $casts = [
        'safety_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }
}