@extends('layouts.app')
@section('title', 'Laporan Per Kasir')
@section('page-title', 'Laporan Per Kasir')

@section('content')

{{-- Period filter --}}
<form method="GET" action="{{ route('app.reports.per-kasir') }}"
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
            <a href="{{ route('app.reports.per-kasir', ['dari' => $d, 'sampai' => $s]) }}"
               class="border border-slate-200 text-slate-600 hover:bg-amber-50 hover:border-amber-300 text-xs font-medium px-3 py-2 rounded-xl transition-colors
                      {{ $dari === $d && $sampai === $s ? 'bg-amber-50 border-amber-300 text-amber-700' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">Performa Kasir</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">#</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Kasir</th>
                    <th class="text-right px-5 py-3 font-medium text-slate-600">Transaksi</th>
                    <th class="text-right px-5 py-3 font-medium text-slate-600">Pendapatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($perKasir as $i => $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-400 font-medium">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 text-slate-700 font-medium">
                        {{ $row->user?->name ?? '(Tanpa Kasir)' }}
                    </td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ number_format($row->transaksi) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800">
                        Rp {{ number_format($row->pendapatan, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data untuk periode ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($perKasir->isNotEmpty())
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <td colspan="2" class="px-5 py-3 text-sm font-semibold text-slate-700">Total</td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-slate-700">
                        {{ number_format($perKasir->sum('transaksi')) }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-semibold text-slate-800">
                        Rp {{ number_format($perKasir->sum('pendapatan'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
