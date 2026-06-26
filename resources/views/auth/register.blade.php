<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Usaha — E-Kasir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-950 via-amber-900 to-amber-800 flex items-center justify-center p-4">

<div class="w-full max-w-lg">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-400 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-900/50">
                <svg class="w-6 h-6 text-amber-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z"/>
                </svg>
            </div>
            <span class="text-2xl font-black text-white tracking-tight">E-Kasir</span>
        </a>
        <p class="text-amber-300/70 text-sm mt-2">Daftar gratis · Trial 14 hari · Tanpa kartu kredit</p>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl shadow-amber-950/40 p-8">
        <h1 class="text-xl font-black text-slate-800 mb-1">Buat Akun Usaha</h1>
        <p class="text-slate-400 text-sm mb-7">Isi data usaha dan pemilik untuk mulai trial.</p>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 mb-5 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            {{-- Section: Info Usaha --}}
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Info Usaha</p>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Usaha <span class="text-red-400">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                        placeholder="Contoh: Warung Kopi Nusantara"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon <span class="text-red-400">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                            placeholder="08xxxxxxxxxx"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            placeholder="Kota / Kecamatan"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Section: Akun Pemilik --}}
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Akun Pemilik</p>

            <div class="space-y-4 mb-7">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Nama pemilik"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="email@usaha.com"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password" required minlength="8"
                            placeholder="Min. 8 karakter"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi <span class="text-red-400">*</span></label>
                        <input type="password" name="password_confirmation" required
                            placeholder="Ulangi password"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="loading"
                class="w-full bg-amber-500 hover:bg-amber-600 disabled:bg-amber-300 text-white font-bold py-3 rounded-2xl text-sm transition-colors shadow-lg shadow-amber-500/30">
                <span x-text="loading ? 'Membuat akun...' : 'Mulai Trial Gratis 14 Hari'"></span>
            </button>

            <p class="text-center text-xs text-slate-400 mt-4">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-amber-600 font-semibold hover:underline">Masuk di sini</a>
            </p>
        </form>
    </div>

    <p class="text-center text-xs text-amber-400/50 mt-6">
        Dengan mendaftar, kamu menyetujui syarat layanan E-Kasir.
    </p>
</div>

</body>
</html>
