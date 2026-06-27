@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('page-title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')

<div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
    </h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($customer) ? route('app.customers.update', $customer) : route('app.customers.store') }}"
          method="POST" class="space-y-5">
        @csrf
        @if (isset($customer))
            @method('PUT')
        @endif

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">
                Nama <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $customer->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Nama lengkap pelanggan">
        </div>

        {{-- Phone --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="phone">Telepon</label>
            <input type="text" id="phone" name="phone"
                   value="{{ old('phone', $customer->phone ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="08xxxxxxxxxx">
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email', $customer->email ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="email@contoh.com">
        </div>

        {{-- Credit Limit --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="credit_limit">Limit Kredit</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                <input type="number" id="credit_limit" name="credit_limit" min="0" step="1"
                       value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}"
                       class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="0">
            </div>
        </div>

        {{-- Is Active --}}
        <div class="flex items-center gap-3 pt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 accent-amber-500 cursor-pointer">
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Aktif</label>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($customer) ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}
            </button>
            <a href="{{ route('app.customers.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
