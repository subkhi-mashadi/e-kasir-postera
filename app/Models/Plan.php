<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly', 'trial_days',
        'max_branches', 'max_users', 'max_products',
        'feature_qr_ordering', 'feature_advanced_reports', 'feature_multi_device',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_monthly'            => 'decimal:2',
        'price_yearly'             => 'decimal:2',
        'feature_qr_ordering'      => 'boolean',
        'feature_advanced_reports' => 'boolean',
        'feature_multi_device'     => 'boolean',
        'is_active'                => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
