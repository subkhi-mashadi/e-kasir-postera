<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Kasir — Masuk</title>
    @vite(['resources/css/app.css'])
    <style>
        input:focus { outline: none !important; border-color: #f59e0b !important; }
        input[type=checkbox]:focus { outline: none !important; }
    </style>
</head>

<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">

    <div class="w-full max-w-xl">
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-amber-500 rounded-2xl shadow-lg shadow-amber-500/30 mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">E-Kasir</h1>
            <p class="text-slate-500 text-sm mt-1">Masuk ke akun Anda</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 p-8">
            @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-2xl px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full border rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 {{ $errors->has('email') ? 'border-red-300' : 'border-slate-200' }}"
                        placeholder="email@usaha.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                        class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
                </div>

                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 rounded-xl transition-colors shadow-lg shadow-amber-500/25">
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-400 text-center space-y-1">
                <div>Demo: <span class="font-mono text-slate-500">kasir@ekasir.test</span> / password</div>
                <div>Owner: <span class="font-mono text-slate-500">owner@ekasir.test</span> / password</div>
                <div>Admin: <span class="font-mono text-slate-500">superadmin@ekasir.test</span> / password</div>
            </div>
        </div>
    </div>
</body>

</html>
