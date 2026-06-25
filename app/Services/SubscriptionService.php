<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Midtrans\Config;
use Midtrans\Snap;

class SubscriptionService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    public function createTrial(Company $company, Plan $plan): Subscription
    {
        return Subscription::create([
            'company_id'    => $company->id,
            'plan_id'       => $plan->id,
            'status'        => 'trial',
            'period'        => 'monthly',
            'trial_ends_at' => now()->addDays($plan->trial_days),
            'starts_at'     => now(),
            'ends_at'       => now()->addDays($plan->trial_days),
        ]);
    }

    public function createCheckout(Company $company, Plan $plan, string $period = 'monthly'): SubscriptionInvoice
    {
        $amount    = $period === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
        $invoiceNo = 'SUB/' . now()->format('Ymd') . '/' . str_pad($company->id, 4, '0', STR_PAD_LEFT) . '/' . now()->timestamp;
        $orderId   = 'sub-' . $company->id . '-' . now()->timestamp;

        // Get or create subscription record
        $subscription = $company->subscriptions()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'plan_id' => $plan->id,
                'status'  => 'trial',
                'period'  => $period,
            ]
        );

        $invoice = SubscriptionInvoice::create([
            'company_id'       => $company->id,
            'subscription_id'  => $subscription->id,
            'invoice_no'       => $invoiceNo,
            'amount'           => $amount,
            'status'           => 'pending',
            'midtrans_order_id'=> $orderId,
            'expires_at'       => now()->addHours(24),
        ]);

        // Create Midtrans Snap token
        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $company->name,
            ],
            'item_details' => [
                [
                    'id'       => $plan->slug,
                    'price'    => (int) $amount,
                    'quantity' => 1,
                    'name'     => 'E-Kasir ' . $plan->name . ' (' . ($period === 'yearly' ? 'Tahunan' : 'Bulanan') . ')',
                ],
            ],
            'callbacks' => [
                'notification' => rtrim(config('app.url'), '/') . '/webhook/midtrans',
            ],
        ]);

        $invoice->update([
            'midtrans_snap_token' => $snapToken,
            'midtrans_payload'    => ['plan_id' => $plan->id, 'period' => $period],
        ]);

        return $invoice;
    }

    public function activateFromInvoice(SubscriptionInvoice $invoice, string $paymentMethod): void
    {
        $payload  = $invoice->midtrans_payload ?? [];
        $planId   = $payload['plan_id'] ?? null;
        $period   = $payload['period']  ?? 'monthly';

        $endsAt = $period === 'yearly' ? now()->addYear() : now()->addMonth();

        $invoice->subscription()->update([
            'plan_id'   => $planId ?? $invoice->subscription->plan_id,
            'status'    => 'active',
            'period'    => $period,
            'starts_at' => now(),
            'ends_at'   => $endsAt,
        ]);

        $invoice->update([
            'status'         => 'paid',
            'payment_method' => $paymentMethod,
            'paid_at'        => now(),
        ]);
    }

    public function checkAndExpire(): int
    {
        return Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);
    }
}
