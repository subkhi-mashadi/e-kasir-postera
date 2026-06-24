<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir') — E-Kasir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-100 antialiased">

{{-- Top bar --}}
<header class="h-12 bg-amber-900 flex items-center justify-between px-4 shrink-0 shadow-md">
    <div class="flex items-center gap-3">
        <span class="text-white font-bold text-sm tracking-wide">E-Kasir</span>
        <span class="text-amber-300/60 text-sm">|</span>
        <span class="text-amber-200 text-sm font-medium">{{ session('branch_name') ?? auth()->user()->branch?->name ?? '—' }}</span>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-amber-300/70 text-xs">{{ auth()->user()->name }}</span>
        {{-- Incoming QR orders button (only shown on POS page via Alpine) --}}
        @if (request()->routeIs('pos.index'))
        <button id="btn-incoming"
                class="relative text-amber-300/70 hover:text-amber-100 text-xs border border-amber-700 px-2.5 py-1 rounded-lg transition-colors hidden"
                onclick="document.dispatchEvent(new CustomEvent('open-incoming'))">
            🔔 Pesanan Masuk
            <span id="incoming-badge"
                  class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs w-4 h-4 rounded-full items-center justify-center font-bold hidden"></span>
        </button>
        @endif
        <a href="{{ route('kitchen.index') }}"
           class="text-amber-300/70 hover:text-amber-100 text-xs border border-amber-700 px-2.5 py-1 rounded-lg transition-colors">
            👨‍🍳 Dapur
        </a>
        <a href="{{ route('app.dashboard') }}"
           class="text-amber-300/70 hover:text-amber-100 text-xs border border-amber-700 px-2.5 py-1 rounded-lg transition-colors">
            Dashboard
        </a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="text-amber-300/70 hover:text-amber-100 text-xs border border-amber-700 px-2.5 py-1 rounded-lg transition-colors">
                Keluar
            </button>
        </form>
    </div>
</header>

@yield('content')

</body>
</html>
