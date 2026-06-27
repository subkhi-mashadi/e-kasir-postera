@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')
@section('page-subtitle', 'Integrasi payment gateway untuk QR Order pelanggan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 text-sm text-blue-700 flex gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
        </svg>
        <div>
            <p class="font-semibold mb-1">Kenapa perlu konfigurasi ini?</p>
            <p>Dengan mengisi Server Key & Client Key akun Midtrans bisnis Anda, pembayaran QRIS dari pelanggan akan langsung masuk ke rekening Anda — bukan tercampur dengan usaha lain.</p>
            <p class="mt-2">Buat akun di <span class="font-mono font-medium">midtrans.com</span>, lalu salin key dari menu <strong>Settings → Access Keys</strong>.</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Midtrans Keys</h2>
            <p class="text-sm text-slate-500 mt-0.5">Isi dengan key dari dashboard Midtrans bisnis Anda</p>
        </div>

        <form method="POST" action="{{ route('app.settings.payment.update') }}" class="px-6 py-5 space-y-5">
            @csrf

            {{-- Server Key --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Server Key
                    <span class="text-slate-400 font-normal ml-1">(rahasia, jangan dibagikan)</span>
                </label>
                <div x-data="{ show: false }" class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        name="midtrans_server_key"
                        value="{{ old('midtrans_server_key', $company->midtrans_server_key) }}"
                        placeholder="SB-Mid-server-xxxx atau Mid-server-xxxx"
                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('midtrans_server_key') border-red-400 @enderror"
                    >
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

            {{-- Client Key --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Client Key</label>
                <input
                    type="text"
                    name="midtrans_client_key"
                    value="{{ old('midtrans_client_key', $company->midtrans_client_key) }}"
                    placeholder="SB-Mid-client-xxxx atau Mid-client-xxxx"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 @error('midtrans_client_key') border-red-400 @enderror"
                >
                @error('midtrans_client_key')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mode --}}
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    <input
                        type="checkbox"
                        id="midtrans_is_production"
                        name="midtrans_is_production"
                        value="1"
                        {{ old('midtrans_is_production', $company->midtrans_is_production) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-400"
                    >
                </div>
                <div>
                    <label for="midtrans_is_production" class="text-sm font-medium text-slate-700 cursor-pointer">
                        Mode Produksi
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Aktifkan jika menggunakan key produksi (transaksi nyata). Biarkan nonaktif untuk testing (Sandbox).
                    </p>
                </div>
            </div>

            {{-- Status indicator --}}
            @if($company->midtrans_server_key)
            <div class="flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                Terkonfigurasi — mode {{ $company->midtrans_is_production ? 'Produksi' : 'Sandbox' }}
            </div>
            @else
            <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                Belum dikonfigurasi — pembayaran QRIS menggunakan akun sistem (tidak direkomendasikan untuk produksi)
            </div>
            @endif

            <div class="pt-2">
                <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>

    {{-- Help --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm text-slate-600 space-y-2">
        <p class="font-semibold text-slate-700">Cara mendapatkan key Midtrans:</p>
        <ol class="list-decimal list-inside space-y-1">
            <li>Daftar / login di <span class="font-mono">dashboard.midtrans.com</span></li>
            <li>Pilih environment (Sandbox untuk testing, Production untuk live)</li>
            <li>Buka <strong>Settings → Access Keys</strong></li>
            <li>Salin <em>Server Key</em> dan <em>Client Key</em></li>
        </ol>
    </div>

</div>
@endsection
