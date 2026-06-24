@extends('layouts.app')
@section('title', 'Meja')
@section('page-title', 'Meja')
@section('page-subtitle', 'Kelola meja dan QR code')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Daftar Meja</h2>
    <a href="{{ route('app.tables.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Meja
    </a>
</div>

{{-- Branch Switcher --}}
<form method="GET" action="{{ route('app.tables.index') }}" class="mb-6 flex items-center gap-3">
    <label class="text-sm font-medium text-slate-600">Cabang:</label>
    <select name="branch_id" onchange="this.form.submit()"
            class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
        <option value="">Semua Cabang</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
            </option>
        @endforeach
    </select>
</form>

@if ($tables->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm px-6 py-16 text-center text-slate-400 text-sm">
        Belum ada meja. Tambahkan meja baru.
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ($tables as $table)
        <div class="bg-white rounded-2xl shadow-sm p-5 flex flex-col gap-3">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-slate-800 text-base">{{ $table->name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Kapasitas: {{ $table->capacity }} orang</p>
                </div>
                @if ($table->is_active)
                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full font-medium">Aktif</span>
                @else
                    <span class="text-xs bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded-full font-medium">Nonaktif</span>
                @endif
            </div>

            {{-- Status Badge --}}
            @php
                $status = $table->status ?? 'available';
            @endphp
            @if ($status === 'available')
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 w-fit">
                    Tersedia
                </span>
            @elseif ($status === 'occupied')
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 w-fit">
                    Terisi
                </span>
            @elseif ($status === 'reserved')
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 w-fit">
                    Dipesan
                </span>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 mt-auto pt-1 flex-wrap">
                <a href="{{ route('app.tables.qr', $table) }}"
                   class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                    Lihat QR
                </a>
                <a href="{{ route('app.tables.edit', $table) }}"
                   class="border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                    Edit
                </a>
                <form action="{{ route('app.tables.destroy', $table) }}" method="POST"
                      onsubmit="return confirm('Hapus meja ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
