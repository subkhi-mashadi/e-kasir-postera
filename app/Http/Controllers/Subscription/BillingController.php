<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Transaction;

class BillingController extends Controller
{
    public function index()
    {
        $company      = auth()->user()->company;
        $subscription = $company?->subscription;
        $plans        = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $pendingInvoice = SubscriptionInvoice::where('company_id', $company?->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('subscription.billing', compact('company', 'subscription', 'plans', 'pendingInvoice'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'period'  => 'required|in:monthly,yearly',
        ]);

        $company = auth()->user()->company;
        $plan    = Plan::findOrFail($request->plan_id);

        $service = new SubscriptionService();
        $invoice = $service->createCheckout($company, $plan, $request->period);

        return response()->json([
            'snap_token' => $invoice->midtrans_snap_token,
            'invoice_id' => $invoice->id,
        ]);
    }

    public function callback(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $invoice   = SubscriptionInvoice::findOrFail($invoiceId);

        if ($invoice->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('app.dashboard')->with('success', 'Langganan berhasil diaktifkan!');
        }

        // Webhook may not have arrived yet — check directly with Midtrans
        try {
            Config::$serverKey    = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');

            $status = Transaction::status($invoice->midtrans_order_id);
            if (in_array($status->transaction_status, ['settlement', 'capture'])) {
                (new SubscriptionService())->activateFromInvoice($invoice, $status->payment_type ?? 'snap');
                return redirect()->route('app.dashboard')->with('success', 'Langganan berhasil diaktifkan!');
            }
        } catch (\Throwable) {
            // Midtrans API unreachable — fall through
        }

        return redirect()->route('subscription.billing')->with('info', 'Menunggu konfirmasi pembayaran...');
    }
}
