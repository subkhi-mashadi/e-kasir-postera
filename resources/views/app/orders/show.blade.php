@extends('layouts.app')
@section('title', 'Detail Order ' . ($order->invoice_no ?? '#'.$order->id))
@section('page-title', 'Detail Order')
@section('page-subtitle', $order->invoice_no ?? 'Menunggu Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <p class="font-mono text-lg font-bold text-slate-800">{{ $order->invoice_no ?? '—' }}</p>
                <p class="text-sm text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="text-right">
                @if($order->status === 'paid')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Lunas</span>
                @elseif($order->status === 'open')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Menunggu</span>
                @else
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-500">Dibatalkan</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Kasir</span>
                <span class="font-medium text-slate-700">{{ $order->user?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Meja</span>
                <span class="font-medium text-slate-700">{{ $order->table?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Pelanggan</span>
                <span class="font-medium text-slate-700">{{ $order->customer_name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Sumber</span>
                <span class="font-medium text-slate-700">{{ $order->source === 'qr' ? 'QR Order' : 'Kasir' }}</span>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800 text-sm">Item Pesanan</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
            <div class="px-5 py-3 flex justify-between items-start gap-4">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $item->qty }}× {{ $item->product?->name ?? $item->product_name ?? '—' }}
                    </p>
                    @if($item->variant_name)
                    <p class="text-xs text-slate-400 mt-0.5">Varian: {{ $item->variant_name }}</p>
                    @endif
                    @if($item->modifiers && $item->modifiers->count())
                    <p class="text-xs text-slate-400">
                        Modifier: {{ $item->modifiers->pluck('name')->join(', ') }}
                    </p>
                    @endif
                    @if($item->notes)
                    <p class="text-xs text-amber-600 mt-0.5">📝 {{ $item->notes }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-slate-800">
                        Rp {{ number_format($item->subtotal ?? ($item->price * $item->qty), 0, ',', '.') }}
                    </p>
                    @if($item->qty > 1)
                    <p class="text-xs text-slate-400">@ Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Totals --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-2 text-sm">
        <div class="flex justify-between text-slate-600">
            <span>Subtotal</span>
            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($order->discount_amount > 0)
        <div class="flex justify-between text-emerald-600">
            <span>Diskon</span>
            <span>− Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($order->tax_amount > 0)
        <div class="flex justify-between text-slate-600">
            <span>Pajak</span>
            <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="flex justify-between font-bold text-slate-800 text-base border-t border-slate-100 pt-2 mt-2">
            <span>Total</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        @if($order->paid_amount > 0)
        <div class="flex justify-between text-slate-500">
            <span>Dibayar</span>
            <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($order->change_amount > 0)
        <div class="flex justify-between text-slate-500">
            <span>Kembalian</span>
            <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    {{-- Payment info --}}
    @if($order->payments->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800 text-sm">Pembayaran</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($order->payments as $pay)
            <div class="px-5 py-3 flex justify-between items-center text-sm">
                <div>
                    <span class="font-medium text-slate-700 uppercase text-xs">{{ $pay->method }}</span>
                    @if($pay->reference)
                    <p class="text-xs text-slate-400 font-mono">{{ $pay->reference }}</p>
                    @endif
                </div>
                <span class="font-semibold text-slate-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($order->notes)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3.5 text-sm text-amber-800">
        <span class="font-semibold">Catatan:</span> {{ $order->notes }}
    </div>
    @endif

</div>
@endsection
