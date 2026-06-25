@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')

{{-- Period filter --}}
<form method="GET" action="{{ route('app.reports.sales') }}"
      class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
        <input type="date" name="dari" value="{{ $dari }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
        <input type="date" name="sampai" value="{{ $sampai }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
    </div>
    <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2 rounded-xl text-sm">
        Tampilkan
    </button>
    {{-- Quick presets --}}
    <div class="flex gap-2 flex-wrap">
        @php
            $presets = [
                'Hari Ini'   => [today()->format('Y-m-d'), today()->format('Y-m-d')],
                '7 Hari'     => [today()->subDays(6)->format('Y-m-d'), today()->format('Y-m-d')],
                '30 Hari'    => [today()->subDays(29)->format('Y-m-d'), today()->format('Y-m-d')],
                'Bulan Ini'  => [today()->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
            ];
        @endphp
        @foreach($presets as $label => [$d, $s])
            <a href="{{ route('app.reports.sales', ['dari' => $d, 'sampai' => $s]) }}"
               class="border border-slate-200 text-slate-600 hover:bg-amber-50 hover:border-amber-300 text-xs font-medium px-3 py-2 rounded-xl transition-colors
                      {{ $dari === $d && $sampai === $s ? 'bg-amber-50 border-amber-300 text-amber-700' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</form>

{{-- Export buttons --}}
<div class="flex gap-2 mt-3 mb-5">
    <a href="{{ route('app.reports.sales.excel', ['dari'=>$dari,'sampai'=>$sampai]) }}"
       class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition-colors">
        📊 Export Excel
    </a>
    <a href="{{ route('app.reports.sales.pdf', ['dari'=>$dari,'sampai'=>$sampai]) }}"
       class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition-colors">
        📄 Export PDF
    </a>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Total Pendapatan</p>
        <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Jumlah Transaksi</p>
        <p class="text-2xl font-black text-slate-800">{{ number_format($totalTransaksi) }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Rata-rata/Transaksi</p>
        <p class="text-2xl font-black text-slate-800">Rp {{ number_format($rataRata, 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Daily table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800 text-sm">Penjualan per Hari</h3>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Tanggal</th>
                        <th class="text-right px-5 py-3 font-medium text-slate-600">Transaksi</th>
                        <th class="text-right px-5 py-3 font-medium text-slate-600">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($perHari as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-slate-700">
                            {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}
                        </td>
                        <td class="px-5 py-3 text-right text-slate-600">{{ $row->transaksi }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">
                            Rp {{ number_format($row->pendapatan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right col: payment methods + top products --}}
    <div class="space-y-5">

        {{-- Payment methods --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800 text-sm">Metode Pembayaran</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($perMetode as $metode)
                @php
                    $pct = $totalPendapatan > 0 ? ($metode->total / $totalPendapatan) * 100 : 0;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-slate-700 capitalize">{{ $metode->method }}</span>
                        <span class="text-sm font-semibold text-slate-800">Rp {{ number_format($metode->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ round($pct) }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $metode->jumlah }} transaksi · {{ round($pct) }}%</p>
                </div>
                @empty
                <p class="text-slate-400 text-sm text-center py-4">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        {{-- Top products --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800 text-sm">Produk Terlaris</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($topProduk as $i => $produk)
                <div class="px-5 py-3 flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400 w-5">{{ $i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $produk->product_name }}</p>
                        <p class="text-xs text-slate-400">{{ number_format($produk->qty) }} terjual</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-800 whitespace-nowrap">
                        Rp {{ number_format($produk->pendapatan, 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="text-slate-400 text-sm text-center py-6">Belum ada data.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
