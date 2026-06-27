<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postera — Masuk</title>
    @vite(['resources/css/app.css'])
    <style>
        input:focus { outline: none !important; border-color: #f59e0b !important; }
        input[type=checkbox]:focus { outline: none !important; }
    </style>
</head>

<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4 relative">

    {{-- Watermark logo fullscreen --}}
    <div class="pointer-events-none select-none fixed inset-0">
        <img src="{{ asset('icons/logo.png') }}" alt=""
             class="w-full h-full object-cover opacity-[0.06]">
    </div>
    <div class="pointer-events-none fixed top-0 inset-x-0 h-64 bg-linear-to-b from-amber-200/40 to-transparent"></div>
    <div class="pointer-events-none fixed bottom-0 inset-x-0 h-48 bg-linear-to-t from-amber-200/30 to-transparent"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('icons/logo.png') }}" alt="Postera" class="w-20 h-20 object-contain mx-auto drop-shadow-md">
            </a>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Postera</h1>
            <p class="text-slate-500 text-sm mt-1">Masuk ke akun Anda</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-amber-900/10 border border-amber-100/80 p-8">
            @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full border rounded-xl px-4 py-2.5 text-sm {{ $errors->has('email') ? 'border-red-300' : 'border-slate-200' }}"
                        placeholder="email@usaha.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                        class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
                </div>

                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl transition-colors shadow-lg shadow-amber-500/25">
                    Masuk
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-5">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-amber-600 font-semibold hover:underline">Daftar gratis</a>
            </p>
        </div>
    </div>
</body>
</html>
