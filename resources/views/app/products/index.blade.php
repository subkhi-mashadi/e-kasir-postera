@extends('layouts.app')
@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Daftar Produk</h2>
    <a href="{{ route('app.products.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Produk
    </a>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('app.products.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari produk..."
           class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
    <select name="category_id"
            class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
        <option value="">Semua Kategori</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
    <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        Filter
    </button>
    @if(request('search') || request('category_id'))
        <a href="{{ route('app.products.index') }}"
           class="border border-slate-200 text-slate-500 hover:bg-slate-50 font-medium px-5 py-2.5 rounded-xl text-sm text-center">
            Reset
        </a>
    @endif
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Nama</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Kategori</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Harga</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Stok</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($products as $product)
            @php $inv = $product->inventories->first(); @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 text-slate-800">
                    <div class="font-medium">{{ $product->name }}</div>
                    @if ($product->sku)
                        <div class="text-xs text-slate-400 mt-0.5">SKU: {{ $product->sku }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-600">{{ $product->category->name ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-800">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3.5">
                    @if ($product->track_stock)
                        @php $qty = (int) ($inv?->qty ?? 0); $avail = $inv?->is_available ?? true; @endphp
                        @if (! $avail)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Tidak Tersedia</span>
                        @elseif ($qty <= 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Tersedia</span>
                        @elseif ($inv->min_qty > 0 && $qty <= (int) $inv->min_qty)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Rendah ({{ $qty }})</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">{{ $qty }}</span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Tidak dipantau</span>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    @if ($product->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('app.products.edit', $product) }}"
                           class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('app.products.destroy', $product) }}" method="POST"
                              onsubmit="return confirm('Hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($products->hasPages())
    <div class="mt-5">
        {{ $products->links() }}
    </div>
@endif
@endsection
