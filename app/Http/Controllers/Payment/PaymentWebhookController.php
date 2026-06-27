<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubscriptionInvoice;
use App\Services\PaymentGatewayFactory;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway)
    {
        $factory = new PaymentGatewayFactory();

        // Subscription payments use system keys
        $orderId = $request->input('order_id') ?? $request->input('reference_id') ?? $request->input('order.invoice_number');

        if (str_starts_with($orderId ?? '', 'sub-')) {
            $gatewayService = $factory->make();

            if (!$gatewayService->verifySignature($request)) {
                Log::warning("{$gateway} webhook signature mismatch (subscription)", ['order_id' => $orderId]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $invoice = SubscriptionInvoice::where('midtrans_order_id', $orderId)->first();
            if (!$invoice) {
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            $transStatus = $request->input('transaction_status')
                ?? $request->input('status')
                ?? $request->input('transaction_status');

            if (in_array($transStatus, ['settlement', 'capture', 'settled', 'SUCCESS', 'completed'])) {
                (new SubscriptionService())->activateFromInvoice($invoice, $gateway);
                Log::info('Subscription invoice paid via ' . $gateway, ['invoice_no' => $invoice->invoice_no]);
            } elseif (in_array($transStatus, ['cancel', 'deny', 'expire', 'FAILED', 'expired'])) {
                $invoice->update(['status' => 'expired']);
            }

            return response()->json(['message' => 'OK']);
        }

        // Order payments
        $order = Order::withoutGlobalScopes()
            ->with('branch.company')
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $company = $order->branch?->company;
        $gatewayService = $factory->make($company);

        if ($company && $company->payment_gateway !== $gateway) {
            Log::warning("Gateway mismatch: webhook={$gateway}, order gateway={$company->payment_gateway}", ['order_id' => $orderId]);
        }

        if (!$gatewayService->verifySignature($request)) {
            Log::warning("{$gateway} webhook signature mismatch", ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transStatus = $request->input('transaction_status')
            ?? $request->input('status')
            ?? $request->input('transaction_status');

        $transactionId = $request->input('transaction_id')
            ?? $request->input('id');

        if (in_array($transStatus, ['settlement', 'capture', 'settled', 'SUCCESS', 'completed'])) {
            if ($order->status !== 'paid') {
                DB::transaction(function () use ($order, $transactionId, $transStatus) {
                    $today     = now()->format('Ymd');
                    $seq       = Order::withoutGlobalScopes()
                        ->whereDate('created_at', today())
                        ->where('branch_id', $order->branch_id)
                        ->whereNotNull('invoice_no')
                        ->lockForUpdate()->count() + 1;
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

                Log::info("{$gateway} payment confirmed", ['order_id' => $order->id, 'invoice' => $order->invoice_no]);
            }
        } elseif (in_array($transStatus, ['cancel', 'deny', 'expire', 'FAILED', 'expired'])) {
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
