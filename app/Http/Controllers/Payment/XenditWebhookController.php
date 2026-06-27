<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->header('x-callback-token', '');

        if (! hash_equals(config('services.xendit.webhook_token', ''), $token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $body        = $request->all();
        $referenceId = $body['reference_id'] ?? null;
        $status      = $body['status'] ?? null;

        Log::info('Xendit QRIS webhook', compact('referenceId', 'status'));

        if (! $referenceId || $status !== 'SUCCEEDED') {
            return response()->json(['ok' => true]);
        }

        $order = Order::withoutGlobalScopes()
            ->where('midtrans_order_id', $referenceId)
            ->first();

        if (! $order || $order->status === 'paid') {
            return response()->json(['ok' => true]);
        }

        $order->update([
            'status'          => 'paid',
            'kitchen_status'  => 'pending',
            'midtrans_status' => 'settlement',
            'paid_amount'     => $order->total,
            'synced_at'       => now(),
        ]);

        $order->payments()->create([
            'method'    => 'qris',
            'amount'    => $order->total,
            'reference' => $body['id'] ?? null,
        ]);

        if (! $order->invoice_no) {
            $today = now()->format('Ymd');
            $seq   = Order::withoutGlobalScopes()
                ->whereDate('created_at', today())
                ->where('branch_id', $order->branch_id)
                ->whereNotNull('invoice_no')
                ->count() + 1;
            $order->update([
                'invoice_no' => 'INV/' . $today . '/' . str_pad($order->branch_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
