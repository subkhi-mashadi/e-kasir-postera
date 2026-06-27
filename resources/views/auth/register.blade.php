<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Usaha — Postera</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input:focus { outline: none !important; border-color: #f59e0b !important; }
    </style>
</head>
<body class="min-h-screen bg-amber-50 flex items-start justify-center p-4 py-10 relative">

    {{-- Watermark logo fullscreen --}}
    <div class="pointer-events-none select-none fixed inset-0">
        <img src="{{ asset('icons/logo.png') }}" alt=""
             class="w-full h-full object-cover opacity-[0.06]">
    </div>
    <div class="pointer-events-none fixed top-0 inset-x-0 h-64 bg-linear-to-b from-amber-200/40 to-transparent"></div>
    <div class="pointer-events-none fixed bottom-0 inset-x-0 h-48 bg-linear-to-t from-amber-200/30 to-transparent"></div>

    <div class="w-full max-w-lg relative z-10">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('icons/logo.png') }}" alt="Postera" class="w-20 h-20 object-contain drop-shadow-md">
            </a>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Postera</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar gratis · Trial 14 hari · Tanpa kartu kredit</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-amber-900/10 border border-amber-100/80 p-8">
            <h2 class="text-lg font-black text-slate-800 mb-1">Buat Akun Usaha</h2>
            <p class="text-slate-400 text-sm mb-6">Isi data usaha dan pemilik untuk mulai trial.</p>

            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 mb-5 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Info Usaha</p>
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Usaha <span class="text-red-400">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required
                            placeholder="Contoh: Warung Kopi Nusantara"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon <span class="text-red-400">*</span></label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                placeholder="08xxxxxxxxxx"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                placeholder="Kota / Kecamatan"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>

                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Akun Pemilik</p>
                <div class="space-y-4 mb-7">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Nama pemilik"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="email@usaha.com"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                            <input type="password" name="password" required minlength="8"
                                placeholder="Min. 8 karakter"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi <span class="text-red-400">*</span></label>
                            <input type="password" name="password_confirmation" required
                                placeholder="Ulangi password"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-amber-500 hover:bg-amber-600 disabled:bg-amber-300 text-white font-bold py-2.5 rounded-xl text-sm transition-colors shadow-lg shadow-amber-500/25">
                    <span x-text="loading ? 'Membuat akun...' : 'Mulai Trial Gratis 14 Hari'"></span>
                </button>

                <p class="text-center text-sm text-slate-500 mt-5">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-amber-600 font-semibold hover:underline">Masuk di sini</a>
                </p>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400/60 mt-5">
            Dengan mendaftar, kamu menyetujui syarat layanan Postera.
        </p>
    </div>

</body>
</html>
