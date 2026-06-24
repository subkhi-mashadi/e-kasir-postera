<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'company_id', 'plan_id', 'status', 'period',
        'trial_ends_at', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function plan()    { return $this->belongsTo(Plan::class); }
    public function invoices(){ return $this->hasMany(SubscriptionInvoice::class); }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }
}
