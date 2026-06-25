<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Company extends Model
{
    protected static function booted(): void
    {
        static::created(function (Company $company) {
            $plan = \App\Models\Plan::where('slug', 'starter')->where('is_active', true)->first();
            if ($plan) {
                (new \App\Services\SubscriptionService())->createTrial($company, $plan);
            }
        });
    }

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

    public function plan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    public function canAddBranch(): bool
    {
        $max = $this->plan()?->max_branches ?? 1;
        return $this->branches()->count() < $max;
    }

    public function canAddUser(): bool
    {
        $max = $this->plan()?->max_users ?? 3;
        return $this->users()->count() < $max;
    }

    public function canAddProduct(): bool
    {
        $max = $this->plan()?->max_products ?? 50;
        return Product::where('company_id', $this->id)->count() < $max;
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->plan()?->$feature ?? false);
    }
}
