<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langganan Berakhir — Postera</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
<div class="max-w-md text-center">
    <div class="text-6xl mb-4">⏰</div>
    <h1 class="text-2xl font-bold text-slate-800 mb-2">Langganan Berakhir</h1>
    <p class="text-slate-500 mb-6">Masa langganan usaha Anda telah habis. Perpanjang untuk melanjutkan akses.</p>
    <a href="{{ route('subscription.billing') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
        Perpanjang Sekarang
    </a>
    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="text-sm text-slate-400 hover:text-slate-600">Keluar</button>
    </form>
</div>
</body>
</html>
