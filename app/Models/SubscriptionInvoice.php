<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'company_id', 'subscription_id', 'invoice_no', 'amount', 'status',
        'midtrans_order_id', 'midtrans_snap_token', 'payment_method',
        'paid_at', 'expires_at', 'midtrans_payload',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'paid_at'           => 'datetime',
        'expires_at'        => 'datetime',
        'midtrans_payload'  => 'array',
    ];

    public function company()      { return $this->belongsTo(Company::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
}
