@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', $currentBranch?->name)

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-5 shadow-sm">
        <div class="text-sm text-slate-500 mb-1">Transaksi Hari Ini</div>
        <div class="text-3xl font-bold text-slate-800">{{ number_format($todayOrders) }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm">
        <div class="text-sm text-slate-500 mb-1">Omzet Hari Ini</div>
        <div class="text-3xl font-bold text-amber-600">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm">
        <div class="text-sm text-slate-500 mb-1">Stok Menipis</div>
        <div class="text-3xl font-bold {{ $lowStock > 0 ? 'text-red-500' : 'text-slate-800' }}">{{ $lowStock }}</div>
    </div>
</div>

<div class="bg-white rounded-2xl p-6 shadow-sm">
    <h2 class="font-semibold text-slate-700 mb-4">Akses Cepat</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('pos.index') }}" class="border border-slate-200 rounded-2xl p-4 text-center hover:border-amber-400 hover:bg-amber-50 transition-colors">
            <div class="text-2xl mb-2">🛒</div>
            <div class="text-sm font-medium text-slate-700">Kasir (POS)</div>
        </a>
        <a href="{{ route('app.products.index') }}" class="border border-slate-200 rounded-2xl p-4 text-center hover:border-amber-400 hover:bg-amber-50 transition-colors">
            <div class="text-2xl mb-2">📦</div>
            <div class="text-sm font-medium text-slate-700">Produk</div>
        </a>
        <a href="{{ route('app.reports.sales') }}" class="border border-slate-200 rounded-2xl p-4 text-center hover:border-amber-400 hover:bg-amber-50 transition-colors">
            <div class="text-2xl mb-2">📊</div>
            <div class="text-sm font-medium text-slate-700">Laporan</div>
        </a>
        <a href="{{ route('subscription.billing') }}" class="border border-slate-200 rounded-2xl p-4 text-center hover:border-amber-400 hover:bg-amber-50 transition-colors">
            <div class="text-2xl mb-2">⚙️</div>
            <div class="text-sm font-medium text-slate-700">Pengaturan</div>
        </a>
    </div>
</div>
@endsection
