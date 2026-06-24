@extends('layouts.app')
@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Daftar Pelanggan</h2>
    <a href="{{ route('app.customers.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Pelanggan
    </a>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('app.customers.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari nama, telepon, atau email..."
           class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
    <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        Cari
    </button>
    @if(request('search'))
        <a href="{{ route('app.customers.index') }}"
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
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Telepon</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Poin</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Kredit Tersisa</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($customers as $customer)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 text-slate-800">
                    <div class="font-medium">{{ $customer->name }}</div>
                    @if ($customer->email)
                        <div class="text-xs text-slate-400 mt-0.5">{{ $customer->email }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-600">{{ $customer->phone ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-800">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                        {{ number_format($customer->points ?? 0) }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-slate-800">
                    @php
                        $kredit = ($customer->credit_limit ?? 0) - ($customer->credit_used ?? 0);
                    @endphp
                    Rp {{ number_format($kredit, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3.5">
                    @if ($customer->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('app.customers.edit', $customer) }}"
                           class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('app.customers.destroy', $customer) }}" method="POST"
                              onsubmit="return confirm('Hapus pelanggan ini?')">
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
                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada pelanggan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($customers->hasPages())
    <div class="mt-5">
        {{ $customers->links() }}
    </div>
@endif
@endsection
