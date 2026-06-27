<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Table;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class QrOrderController extends Controller
{
    public function show(string $token)
    {
        $table = Table::with('branch.company')
            ->where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $branch  = $table->branch;
        $company = $branch->company;

        $products = Product::withoutGlobalScopes()
            ->with([
                'category:id,name,icon',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
                'modifierGroups.options' => fn ($q) => $q->where('is_active', true),
                'inventories' => fn ($q) => $q->where('branch_id', $branch->id),
            ])
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($branch) {
                $inv = $p->inventories->first();
                return [
                    'id'             => $p->id,
                    'name'           => $p->name,
                    'description'    => $p->description,
                    'price'          => (float) $p->price,
                    'tax_rate'       => (float) $p->tax_rate,
                    'image_url'      => $p->image_url,
                    'category_id'    => $p->category_id,
                    'category_name'  => $p->category?->name ?? '—',
                    'category_icon'  => $p->category?->icon ?? '',
                    'track_stock'    => (bool) $p->track_stock,
                    'stock'          => $inv ? (float) $inv->qty : null,
                    'is_available'   => $inv ? (bool) $inv->is_available : true,
                    'variants'       => $p->variants->map(fn ($v) => [
                        'id'               => $v->id,
                        'name'             => $v->name,
                        'price_adjustment' => (float) $v->price_adjustment,
                    ])->values(),
                    'modifier_groups' => $p->modifierGroups->map(fn ($g) => [
                        'id'          => $g->id,
                        'name'        => $g->name,
                        'is_required' => (bool) $g->is_required,
                        'is_multiple' => (bool) $g->is_multiple,
                        'options'     => $g->options->map(fn ($o) => [
                            'id'    => $o->id,
                            'name'  => $o->name,
                            'price' => (float) $o->price,
                        ])->values(),
                    ])->values(),
                ];
            });

        $categories = Category::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'icon']);

        $qrisImageUrl = $branch->qris_image
            ? asset('storage/' . $branch->qris_image)
            : null;

        return view('order.menu', compact('table', 'branch', 'company', 'products', 'categories', 'qrisImageUrl'));
    }

    public function submit(Request $request, string $token)
    {
        // Rate limit: max 5 orders per minute per token
        $key = 'qr-order:' . $token;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429);
        }
        RateLimiter::hit($key, 60);

        $table = Table::with('branch.company')
            ->where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $branch  = $table->branch;
        $company = $branch->company;

        $data = $request->validate([
            'customer_name'    => 'required|string|max:100',
            'preferred_payment' => 'required|in:cash,qris',
            'notes'            => 'nullable|string|max:500',
            'items'          => 'required|array|min:1',
            'items.*.product_id'           => 'required|exists:products,id',
            'items.*.product_variant_id'   => 'nullable|exists:product_variants,id',
            'items.*.qty'                  => 'required|integer|min:1',
            'items.*.notes'                => 'nullable|string|max:255',
            'items.*.modifiers'            => 'nullable|array',
            'items.*.modifiers.*.modifier_option_id' => 'required|exists:modifier_options,id',
        ]);

        $gatewayName = $company->payment_gateway ?? 'midtrans';
        $orderId = null;

        DB::transaction(function () use ($data, $branch, $company, $table, $gatewayName, &$orderId) {
            $subtotal = 0;
            $taxTotal = 0;
            $itemsToSave = [];

            foreach ($data['items'] as $item) {
                $product = Product::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('is_active', true)
                    ->findOrFail($item['product_id']);

                $variant = (isset($item['product_variant_id']) && $item['product_variant_id'])
                    ? ProductVariant::find($item['product_variant_id'])
                    : null;

                $basePrice  = (float) $product->price + (float) ($variant?->price_adjustment ?? 0);
                $modTotal   = 0;
                $modsToSave = [];

                foreach ($item['modifiers'] ?? [] as $mod) {
                    $option = ModifierOption::with('group')->find($mod['modifier_option_id']);
                    if ($option) {
                        $modTotal += (float) $option->price;
                        $modsToSave[] = [
                            'modifier_option_id' => $option->id,
                            'modifier_name'      => $option->group->name,
                            'option_name'        => $option->name,
                            'price'              => (float) $option->price,
                        ];
                    }
                }

                $unitPrice = $basePrice + $modTotal;
                $lineTotal = $unitPrice * (int) $item['qty'];
                $lineTax   = $lineTotal * ((float) $product->tax_rate / 100);

                $subtotal += $lineTotal;
                $taxTotal += $lineTax;

                $itemsToSave[] = [
                    'product'   => $product,
                    'variant'   => $variant,
                    'unitPrice' => $unitPrice,
                    'qty'       => (int) $item['qty'],
                    'lineTotal' => $lineTotal,
                    'lineTax'   => $lineTax,
                    'notes'     => $item['notes'] ?? null,
                    'mods'      => $modsToSave,
                ];
            }

            $total  = $subtotal + $taxTotal;
            $isQris = $data['preferred_payment'] === 'qris';

            // For QRIS: pre-generate a unique order ID
            $gatewayOrderId = $isQris
                ? 'QR-' . $branch->id . '-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4))
                : null;

            $order = Order::withoutGlobalScopes()->create([
                'company_id'        => $company->id,
                'branch_id'         => $branch->id,
                'table_id'          => $table->id,
                'customer_name'     => $data['customer_name'],
                'customer_ip'       => request()->ip(),
                'type'              => 'dine_in',
                'source'            => 'qr',
                'preferred_payment' => $data['preferred_payment'],
                'status'            => 'open',
                'gateway'           => $gatewayName,
                'midtrans_order_id' => $gatewayOrderId,
                'midtrans_status'   => $isQris ? 'pending' : null,
                'subtotal'          => $subtotal,
                'discount_amount'   => 0,
                'tax_amount'        => $taxTotal,
                'total'             => $total,
                'paid_amount'       => 0,
                'change_amount'     => 0,
                'notes'             => $data['notes'] ?? null,
            ]);

            foreach ($itemsToSave as $item) {
                $orderItem = $order->items()->create([
                    'product_id'         => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_name'       => $item['product']->name,
                    'variant_name'       => $item['variant']?->name,
                    'unit_price'         => $item['unitPrice'],
                    'qty'                => $item['qty'],
                    'tax_amount'         => $item['lineTax'],
                    'subtotal'           => $item['lineTotal'],
                    'notes'              => $item['notes'],
                ]);

                foreach ($item['mods'] as $mod) {
                    $orderItem->modifiers()->create($mod);
                }
            }

            $orderId = $order->id;
        });

        $order = Order::withoutGlobalScopes()->with('items.modifiers')->find($orderId);

        // For QRIS: call payment gateway
        $qrUrl       = null;
        $redirectUrl = null;
        if ($data['preferred_payment'] === 'qris' && $order->midtrans_order_id) {
            try {
                $gateway = (new PaymentGatewayFactory())->make($company);
                $result  = $gateway->chargeQris(
                    $order->midtrans_order_id,
                    (int) round((float) $order->total),
                    $data['customer_name']
                );
                $qrUrl       = $result['qr_url'];
                $redirectUrl = $result['redirect_url'] ?? null;
                $order->update([
                    'midtrans_qr_url' => $qrUrl,
                    'midtrans_status' => $result['status'],
                ]);
            } catch (\Exception $e) {
                Log::error('Gateway QRIS charge failed', [
                    'order_id'    => $order->id,
                    'gateway'     => $gatewayName,
                    'total'       => $order->total,
                    'company_id'  => $company->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'order_id'          => $order->id,
            'preferred_payment' => $data['preferred_payment'],
            'qris_image_url'    => $qrUrl,
            'redirect_url'      => $redirectUrl,
            'table_name'        => $table->name,
            'customer_name'     => $data['customer_name'],
            'subtotal'          => (float) $order->subtotal,
            'tax_amount'        => (float) $order->tax_amount,
            'total'             => (float) $order->total,
            'notes'             => $order->notes,
            'items'             => $order->items->map(fn ($i) => [
                'product_name' => $i->product_name,
                'variant_name' => $i->variant_name,
                'qty'          => $i->qty,
                'unit_price'   => (float) $i->unit_price,
                'subtotal'     => (float) $i->subtotal,
                'notes'        => $i->notes,
                'modifiers'    => $i->modifiers->pluck('option_name')->join(', '),
            ])->values(),
            'message' => 'Pesanan berhasil dikirim!',
        ]);
    }

    public function paymentStatus(string $token, int $orderId)
    {
        $table = Table::with('branch.company')
            ->where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $order = Order::withoutGlobalScopes()
            ->where('id', $orderId)
            ->where('branch_id', $table->branch_id)
            ->firstOrFail();

        // If already confirmed paid, return immediately
        if ($order->status === 'paid') {
            return response()->json(['paid' => true, 'status' => 'paid']);
        }

        // Check with payment gateway directly (poll fallback)
        if ($order->midtrans_order_id) {
            try {
                $gateway = (new PaymentGatewayFactory())->make($table->branch->company);
                $result  = $gateway->getStatus($order->midtrans_order_id);

                $settledStatuses = $gateway->getName() === 'midtrans'
                    ? ['settlement', 'capture']
                    : ['settled', 'SUCCESS', 'completed'];

                if ($result && in_array($result->transaction_status ?? $result->status ?? '', $settledStatuses)) {
                    $this->markOrderPaid($order, $result, $gateway->getName());
                    return response()->json(['paid' => true, 'status' => 'paid']);
                }

                $status = $result->transaction_status ?? $result->status ?? $order->midtrans_status;
                $order->update(['midtrans_status' => $status]);
            } catch (\Exception) {
                // silent — return current DB state
            }
        }

        return response()->json(['paid' => false, 'status' => $order->midtrans_status ?? 'pending']);
    }

    private function markOrderPaid(Order $order, object $gatewayResult, string $gateway = 'midtrans'): void
    {
        if ($order->status === 'paid') return;

        $refProperty = $gateway === 'midtrans' ? 'transaction_id' : 'id';

        DB::transaction(function () use ($order, $gatewayResult, $refProperty) {
            $today     = now()->format('Ymd');
            $seq       = Order::withoutGlobalScopes()
                ->whereDate('created_at', today())
                ->where('branch_id', $order->branch_id)
                ->whereNotNull('invoice_no')
                ->lockForUpdate()->count() + 1;
            $invoiceNo = 'INV/' . $today . '/' . str_pad($order->branch_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $status = $gatewayResult->transaction_status ?? $gatewayResult->status ?? 'paid';

            $order->update([
                'status'          => 'paid',
                'kitchen_status'  => 'pending',
                'paid_amount'     => $order->total,
                'invoice_no'      => $invoiceNo,
                'midtrans_status' => $status,
                'synced_at'       => now(),
            ]);

            $order->payments()->create([
                'method'    => 'qris',
                'amount'    => (float) $order->total,
                'reference' => $gatewayResult->{$refProperty} ?? null,
            ]);
        });
    }

    public function history(string $token)
    {
        $table = Table::with('branch.company')
            ->where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $ip = request()->ip();

        $orders = Order::withoutGlobalScopes()
            ->with(['items.modifiers'])
            ->where('branch_id', $table->branch_id)
            ->where('customer_ip', $ip)
            ->whereDate('created_at', today())
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'                => $o->id,
                'table_name'        => $table->name,
                'customer_name'     => $o->customer_name,
                'total'             => (float) $o->total,
                'status'            => $o->status,
                'kitchen_status'    => $o->kitchen_status,
                'preferred_payment' => $o->preferred_payment,
                'created_at'        => $o->created_at->format('H:i'),
                'items'             => $o->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'variant_name' => $i->variant_name,
                    'qty'          => $i->qty,
                    'subtotal'     => (float) $i->subtotal,
                    'modifiers'    => $i->modifiers->pluck('option_name')->join(', '),
                ])->values(),
            ]);

        if (request()->wantsJson()) {
            return response()->json(['orders' => $orders]);
        }

        return view('order.history', compact('table', 'orders'));
    }
}
