<div class="flex items-center gap-3 px-5 py-4 border-b border-amber-800/60">
    <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-900/50">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z"/>
        </svg>
    </div>
    <div class="min-w-0">
        <div class="font-bold text-sm truncate text-amber-50">{{ auth()->user()->company?->name ?? 'E-Kasir' }}</div>
        <div class="text-xs text-amber-300/70 truncate">{{ auth()->user()->name }}</div>
    </div>
</div>

<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
    @php
    $routeName = request()->route()?->getName();
    $is = fn($pattern) => str_starts_with($routeName ?? '', $pattern);
    @endphp

    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 mb-2 mt-2">Utama</p>

    <a href="{{ route('app.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-colors
            {{ $is('app.dashboard') ? 'bg-amber-500 text-white shadow-md shadow-amber-900/40' : 'text-amber-100/80 hover:bg-amber-800/50 hover:text-amber-50' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
    </a>

    {{-- More menu items will be added per feature --}}

</nav>

<div class="px-3 py-4 border-t border-amber-800/60">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-amber-300/60 hover:bg-amber-800/50 hover:text-amber-50 w-full transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Keluar
        </button>
    </form>
</div>
