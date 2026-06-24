@extends('layouts.app')
@section('title', 'Riwayat Order')
@section('page-title', 'Riwayat Order')

@section('content')

{{-- Filter --}}
<form method="GET" action="{{ route('app.orders.index') }}"
      class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
        <input type="date" name="dari" value="{{ request('dari') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
        <input type="date" name="sampai" value="{{ request('sampai') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
            <option value="">Semua</option>
            <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>Lunas</option>
            <option value="open"      {{ request('status') === 'open'      ? 'selected' : '' }}>Menunggu</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
    </div>
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-slate-500 mb-1">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice atau nama pelanggan..."
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
    </div>
    <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2 rounded-xl text-sm">
        Filter
    </button>
    @if(request()->hasAny(['dari','sampai','status','search']))
        <a href="{{ route('app.orders.index') }}"
           class="border border-slate-200 text-slate-500 hover:bg-slate-50 font-medium px-5 py-2 rounded-xl text-sm">
            Reset
        </a>
    @endif
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Waktu</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Invoice</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Pelanggan</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Meja</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Item</th>
                    <th class="text-right px-5 py-3.5 font-medium text-slate-600">Total</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Pembayaran</th>
                    <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs text-slate-700">
                            {{ $order->invoice_no ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-700">
                        {{ $order->customer_name}}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 text-xs">
                        {{ $order->table?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-500 text-xs">
                        {{ $order->items->count() }} item
                    </td>
                    <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5">
                        @if($order->preferred_payment === 'qris')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">QRIS</span>
                        @elseif($order->preferred_payment === 'cash')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Tunai</span>
                        @else
                            <span class="text-slate-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if($order->status === 'paid')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Lunas</span>
                        @elseif($order->status === 'open')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Menunggu</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-500">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">
                        Belum ada order untuk filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($orders->hasPages())
    <div class="mt-5">{{ $orders->links() }}</div>
@endif

@endsection
