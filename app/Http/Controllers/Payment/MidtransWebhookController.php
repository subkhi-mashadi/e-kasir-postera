<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubscriptionInvoice;
use App\Services\MidtransService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', $payload);

        $orderId         = $payload['order_id']          ?? null;
        $statusCode      = $payload['status_code']       ?? null;
        $grossAmount     = $payload['gross_amount']      ?? null;
        $signatureKey    = $payload['signature_key']     ?? null;
        $transStatus     = $payload['transaction_status'] ?? null;
        $transactionId   = $payload['transaction_id']   ?? null;

        if (! $orderId || ! $signatureKey) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Verify signature
        $midtrans = new MidtransService();
        if (! $midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook signature mismatch', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Check if this is a subscription invoice payment
        if (str_starts_with($orderId, 'sub-')) {
            $invoice = SubscriptionInvoice::where('midtrans_order_id', $orderId)->first();
            if ($invoice && in_array($transStatus, ['settlement', 'capture'])) {
                (new SubscriptionService())->activateFromInvoice($invoice, $payload['payment_type'] ?? 'midtrans');
                Log::info('Subscription invoice paid', ['invoice_no' => $invoice->invoice_no]);
            } elseif ($invoice && in_array($transStatus, ['cancel', 'deny', 'expire'])) {
                $invoice->update(['status' => 'expired']);
            }
            return response()->json(['message' => 'OK']);
        }

        $order = Order::withoutGlobalScopes()
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // settlement / capture → payment confirmed
        if (in_array($transStatus, ['settlement', 'capture'])) {
            if ($order->status !== 'paid') {
                DB::transaction(function () use ($order, $transactionId, $transStatus) {
                    $today     = now()->format('Ymd');
                    $seq       = Order::withoutGlobalScopes()
                        ->whereDate('created_at', today())
                        ->where('branch_id', $order->branch_id)
                        ->lockForUpdate()->count();
                    $invoiceNo = 'INV/' . $today . '/' . str_pad($order->branch_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                    $order->update([
                        'status'          => 'paid',
                        'kitchen_status'  => 'pending',
                        'paid_amount'     => $order->total,
                        'invoice_no'      => $invoiceNo,
                        'midtrans_status' => $transStatus,
                        'synced_at'       => now(),
                    ]);

                    $order->payments()->create([
                        'method'    => 'qris',
                        'amount'    => (float) $order->total,
                        'reference' => $transactionId,
                    ]);
                });

                Log::info('Midtrans payment confirmed', ['order_id' => $order->id, 'invoice' => $order->invoice_no]);
            }
        } elseif (in_array($transStatus, ['cancel', 'deny', 'expire'])) {
            $order->update([
                'status'          => 'cancelled',
                'midtrans_status' => $transStatus,
            ]);
        } else {
            $order->update(['midtrans_status' => $transStatus]);
        }

        return response()->json(['message' => 'OK']);
    }
}
