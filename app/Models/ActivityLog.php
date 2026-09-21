<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'email',
        'action',
        'object',
        'description',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function record(?string $email, string $action, ?string $object = null, ?string $description = null): self
    {
        return self::create([
            'email' => $email,
            'action' => $action,
            'object' => $object,
            'description' => $description,
            'ip_address' => Request::ip(),
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
