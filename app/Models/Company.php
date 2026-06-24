<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'phone', 'address', 'logo',
        'currency', 'timezone', 'tax_rate', 'tax_inclusive',
        'receipt_settings', 'is_active',
    ];

    protected $casts = [
        'tax_rate'         => 'decimal:2',
        'tax_inclusive'    => 'boolean',
        'receipt_settings' => 'array',
        'is_active'        => 'boolean',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
