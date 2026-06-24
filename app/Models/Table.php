<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Table extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'qr_token', 'capacity', 'status', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($table) {
            if (! $table->qr_token) {
                $table->qr_token = Str::random(32);
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getQrUrlAttribute(): string
    {
        return route('order.show', $this->qr_token);
    }
}
