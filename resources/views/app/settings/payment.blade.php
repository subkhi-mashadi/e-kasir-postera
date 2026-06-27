@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')
@section('page-subtitle', 'Konfigurasi payment gateway untuk bisnis Anda')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    gateway: '{{ old('payment_gateway', $company->payment_gateway ?? 'midtrans') }}',
    init() {
        this.$watch('gateway', () => {});
    }
}">

    {{-- Info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 text-sm text-blue-700 flex gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
        </svg>
        <div>
            <p class="font-semibold mb-1">Payment Gateway</p>
            <p>Pilih gateway pembayaran yang ingin digunakan untuk transaksi QRIS dan langganan. API key akan langsung masuk ke rekening bisnis Anda.</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Konfigurasi Gateway</h2>
            <p class="text-sm text-slate-500 mt-0.5">Atur payment gateway default untuk bisnis Anda</p>
        </div>

        <form method="POST" action="{{ route('app.settings.payment.update') }}" class="px-6 py-5 space-y-5">
            @csrf

            {{-- Gateway selector --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Payment Gateway Default</label>
                <select name="payment_gateway" x-model="gateway"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                    <option value="midtrans">Midtrans</option>
                    <option value="doku">DOKU</option>
                </select>
                @error('payment_gateway')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Midtrans keys --}}
            <div x-show="gateway === 'midtrans'" class="space-y-4">
                <div class="bg-slate-50 rounded-xl px-4 py-3 text-sm text-slate-500">
                    <span class="font-semibold text-slate-700">Midtrans</span> &mdash; Dapatkan Server Key & Client Key dari <span class="font-mono">dashboard.midtrans.com</span> &rarr; Settings &rarr; Access Keys.
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Server Key
                        <span class="text-slate-400 font-normal ml-1">(rahasia)</span>
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'"
                            name="midtrans_server_key"
                            value="{{ old('midtrans_server_key', $company->midtrans_server_key) }}"
                            placeholder="SB-Mid-server-xxxx atau Mid-server-xxxx"
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('midtrans_server_key') border-red-400 @enderror">
                        <button type="button" @click="show=!show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('midtrans_server_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Client Key</label>
                    <input type="text" name="midtrans_client_key"
                        value="{{ old('midtrans_client_key', $company->midtrans_client_key) }}"
                        placeholder="SB-Mid-client-xxxx atau Mid-client-xxxx"
                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('midtrans_client_key') border-red-400 @enderror">
                    @error('midtrans_client_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" id="midtrans_is_production" name="midtrans_is_production" value="1"
                        {{ old('midtrans_is_production', $company->midtrans_is_production) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-400 mt-0.5">
                    <label for="midtrans_is_production" class="text-sm font-medium text-slate-700 cursor-pointer">Mode Produksi</label>
                </div>
            </div>

            {{-- DOKU keys --}}
            <div x-show="gateway === 'doku'" class="space-y-4">
                <div class="bg-slate-50 rounded-xl px-4 py-3 text-sm text-slate-500">
                    <span class="font-semibold text-slate-700">DOKU</span> &mdash; Dapatkan Client ID & Secret Key dari <span class="font-mono">dashboard.doku.com</span> &rarr; Settings &rarr; Integration.
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Client ID</label>
                    <input type="text" name="doku_client_id"
                        value="{{ old('doku_client_id', $company->doku_client_id) }}"
                        placeholder="BRN-xxxx"
                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('doku_client_id') border-red-400 @enderror">
                    @error('doku_client_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Secret Key
                        <span class="text-slate-400 font-normal ml-1">(rahasia)</span>
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'"
                            name="doku_secret_key"
                            value="{{ old('doku_secret_key', $company->doku_secret_key) }}"
                            placeholder="SK-xxxx"
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('doku_secret_key') border-red-400 @enderror">
                        <button type="button" @click="show=!show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('doku_secret_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" id="doku_is_production" name="doku_is_production" value="1"
                        {{ old('doku_is_production', $company->doku_is_production) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-400 mt-0.5">
                    <label for="doku_is_production" class="text-sm font-medium text-slate-700 cursor-pointer">Mode Produksi</label>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
