@extends('layouts.app')
@section('title', 'Inventori')
@section('page-title', 'Inventori')
@section('page-subtitle', 'Kelola stok produk per cabang')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Stok Inventori</h2>
</div>

{{-- Branch Switcher + Search --}}
<form method="GET" action="{{ route('app.inventory.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <select name="branch_id" onchange="this.form.submit()"
            class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
        <option value="">Semua Cabang</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
            </option>
        @endforeach
    </select>

    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari produk..."
           class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">

    <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        Cari
    </button>
    @if(request('search'))
        <a href="{{ route('app.inventory.index', array_filter(['branch_id' => $branchId])) }}"
           class="border border-slate-200 text-slate-500 hover:bg-slate-50 font-medium px-5 py-2.5 rounded-xl text-sm text-center">
            Reset
        </a>
    @endif
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Produk</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Kategori</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Stok</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Min Stok</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($inventories as $inv)
            <tr class="hover:bg-slate-50 transition-colors"
                x-data="{ open: false }">
                <td class="px-5 py-3.5 text-slate-800 font-medium">{{ $inv->product->name ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-600">{{ $inv->product->category->name ?? '—' }}</td>
                <td class="px-5 py-3.5">
                    @if (! $inv->is_available)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                            Tidak Tersedia
                        </span>
                    @elseif ((int) $inv->qty <= 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                            Tersedia
                        </span>
                    @elseif ($inv->min_qty > 0 && $inv->qty <= $inv->min_qty)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                            Rendah ({{ (int) $inv->qty }})
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                            {{ (int) $inv->qty }}
                        </span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-600">{{ (int) $inv->min_qty }}</td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <button type="button" @click="open = !open"
                                class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Sesuaikan
                        </button>
                        <form action="{{ route('app.inventory.adjust', $inv->id) }}" method="POST"
                              onsubmit="return confirm('Hapus data inventori {{ addslashes($inv->product->name ?? '') }}?')">
                            @csrf
                            <input type="hidden" name="type" value="delete">
                            <button type="submit"
                                    class="border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>

                    {{-- Inline Modal --}}
                    <div x-show="open" x-cloak
                         @click.outside="open = false"
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm" @click.stop
                             x-data="{ adjType: 'add' }">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-semibold text-slate-800">Sesuaikan Stok</h3>
                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <p class="text-sm text-slate-500 mb-4">{{ $inv->product->name ?? '' }}
                                <span class="ml-1 text-xs font-medium {{ $inv->is_available ? 'text-emerald-600' : 'text-slate-400' }}">
                                    · {{ $inv->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span>
                            </p>

                            <form action="{{ route('app.inventory.adjust', $inv->id) }}" method="POST" class="space-y-4">
                                @csrf

                                {{-- Type --}}
                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">Tipe Penyesuaian</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach ([
                                            'add'         => 'Tambah',
                                            'subtract'    => 'Kurangi',
                                            'set'         => 'Set Stok',
                                            'available'   => 'Tersedia',
                                            'unavailable' => 'Tdk Tersedia',
                                        ] as $val => $label)
                                        <label class="flex items-center justify-center gap-1.5 border rounded-xl px-2 py-2 cursor-pointer text-xs font-medium transition-colors"
                                               :class="adjType === '{{ $val }}'
                                                    ? '{{ $val === 'delete' ? 'border-red-400 bg-red-50 text-red-600' : 'border-amber-400 bg-amber-50 text-amber-700' }}'
                                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                            <input type="radio" name="type" value="{{ $val }}"
                                                   x-model="adjType"
                                                   class="sr-only">
                                            {{ $label }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Qty (hidden for available/unavailable/delete) --}}
                                <div x-show="['add','subtract','set'].includes(adjType)">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Jumlah <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="qty" min="0"
                                           :required="['add','subtract','set'].includes(adjType)"
                                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                                           placeholder="0">
                                </div>

                                {{-- Info for availability types --}}
                                <div x-show="['available','unavailable'].includes(adjType)"
                                     class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500">
                                    Status ketersediaan akan diperbarui tanpa mengubah jumlah stok.
                                </div>

                                {{-- Notes (only for qty types) --}}
                                <div x-show="['add','subtract','set'].includes(adjType)">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                                    <input type="text" name="notes"
                                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                                           placeholder="Opsional">
                                </div>

                                <div class="flex items-center gap-3 pt-1">
                                    <button type="submit"
                                            class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                                        Simpan
                                    </button>
                                    <button type="button" @click="open = false"
                                            class="flex-1 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-5 py-2.5 rounded-xl text-sm transition-colors">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data inventori.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($inventories->hasPages())
    <div class="mt-5">
        {{ $inventories->links() }}
    </div>
@endif
@endsection
