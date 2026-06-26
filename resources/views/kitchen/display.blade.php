<!DOCTYPE html>
<html lang="id" class="h-full" style="height:100dvh;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapur — {{ $branch->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak]{display:none!important}
        @keyframes pulse-green { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.35)} 50%{box-shadow:0 0 0 8px rgba(16,185,129,0)} }
        .pulse-ready { animation: pulse-green 1.5s infinite; }
    </style>
</head>
<body class="h-full bg-slate-100 antialiased flex flex-col">

<div x-data="kitchenDisplay()" x-init="init()" class="flex flex-col h-full">

    {{-- Top bar (sama seperti POS) --}}
    <header class="h-12 bg-amber-900 flex items-center justify-between px-4 shrink-0 shadow-md">
        <div class="flex items-center gap-3">
            <span class="text-white font-bold text-sm tracking-wide">E-Kasir</span>
            <span class="text-amber-300/60 text-sm">|</span>
            <span class="text-amber-200 text-sm font-medium">{{ $branch->name }}</span>
            <span class="text-amber-300/60 text-sm">·</span>
            <span class="text-amber-300/70 text-xs" x-text="'Diperbarui ' + lastUpdate"></span>
        </div>
        <div class="flex items-center gap-2">
            {{-- Counter badges --}}
            <span class="bg-amber-500/30 text-amber-200 text-xs font-bold px-2.5 py-1 rounded-full" x-text="pendingCount + ' Antrian'"></span>
            <span class="bg-blue-500/30 text-blue-200 text-xs font-bold px-2.5 py-1 rounded-full" x-text="preparingCount + ' Diproses'"></span>
            <span class="bg-emerald-500/30 text-emerald-200 text-xs font-bold px-2.5 py-1 rounded-full" x-text="readyCount + ' Siap Antar'"></span>
            <span class="text-amber-700 mx-1">|</span>
            <a href="{{ route('pos.index') }}"
               class="text-amber-300/70 hover:text-amber-100 text-xs border border-amber-700 px-2.5 py-1 rounded-lg transition-colors">
                🧾 Kasir
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

    {{-- 3-column board --}}
    <div class="grid grid-cols-3 gap-0 flex-1 overflow-hidden">

        {{-- ANTRIAN --}}
        <div class="border-r border-slate-200 flex flex-col">
            <div class="bg-amber-50 border-b border-amber-200 px-4 py-2.5 shrink-0">
                <p class="text-amber-700 font-black text-xs uppercase tracking-widest">Antrian</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in pending" :key="order.id">
                    <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                        <div class="bg-amber-50 px-3 py-2 flex justify-between items-start border-b border-amber-100">
                            <div>
                                <p class="text-sm font-black text-amber-800" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span x-show="order.source === 'qr'" class="text-xs bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full font-medium">QR</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-100">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-slate-800" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                    <span x-show="item.modifiers" class="text-slate-400 text-xs block ml-4" x-text="item.modifiers"></span>
                                    <span x-show="item.notes" class="text-amber-600 text-xs block ml-4" x-text="'📝 ' + item.notes"></span>
                                </div>
                            </template>
                            <p x-show="order.notes" class="text-amber-600 text-xs mt-1" x-text="'Catatan: ' + order.notes"></p>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'preparing')"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Mulai Proses →
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="pending.length === 0" class="text-center py-10 text-slate-400 text-sm">Tidak ada antrian</div>
            </div>
        </div>

        {{-- DIPROSES --}}
        <div class="border-r border-slate-200 flex flex-col">
            <div class="bg-blue-50 border-b border-blue-200 px-4 py-2.5 shrink-0">
                <p class="text-blue-700 font-black text-xs uppercase tracking-widest">Sedang Diproses</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in preparing" :key="order.id">
                    <div class="bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">
                        <div class="bg-blue-50 px-3 py-2 flex justify-between items-start border-b border-blue-100">
                            <div>
                                <p class="text-sm font-black text-blue-800" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span x-show="order.source === 'qr'" class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-medium">QR</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-100">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-slate-800" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                    <span x-show="item.modifiers" class="text-slate-400 text-xs block ml-4" x-text="item.modifiers"></span>
                                    <span x-show="item.notes" class="text-amber-600 text-xs block ml-4" x-text="'📝 ' + item.notes"></span>
                                </div>
                            </template>
                            <p x-show="order.notes" class="text-amber-600 text-xs mt-1" x-text="'Catatan: ' + order.notes"></p>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'ready')"
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Selesai, Siap Antar ✓
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="preparing.length === 0" class="text-center py-10 text-slate-400 text-sm">Tidak ada pesanan diproses</div>
            </div>
        </div>

        {{-- SIAP ANTAR --}}
        <div class="flex flex-col">
            <div class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 shrink-0">
                <p class="text-emerald-700 font-black text-xs uppercase tracking-widest">Siap Diantarkan</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in ready" :key="order.id">
                    <div class="bg-white rounded-xl border border-emerald-300 shadow-sm pulse-ready overflow-hidden">
                        <div class="bg-emerald-50 px-3 py-2 flex justify-between items-start border-b border-emerald-100">
                            <div>
                                <p class="text-sm font-black text-emerald-800" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span class="text-lg">🛎️</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-100">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-slate-800" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                </div>
                            </template>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'delivered')"
                                    class="w-full bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Sudah Diantarkan ✓
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="ready.length === 0" class="text-center py-10 text-slate-400 text-sm">Tidak ada pesanan siap antar</div>
            </div>
        </div>

    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function kitchenDisplay() {
    return {
        orders: [],
        lastUpdate: '—',

        get pending()        { return this.orders.filter(o => o.kitchen_status === 'pending'); },
        get preparing()      { return this.orders.filter(o => o.kitchen_status === 'preparing'); },
        get ready()          { return this.orders.filter(o => o.kitchen_status === 'ready'); },
        get pendingCount()   { return this.pending.length; },
        get preparingCount() { return this.preparing.length; },
        get readyCount()     { return this.ready.length; },

        async init() {
            await this.loadOrders();
            setInterval(() => this.loadOrders(), 8000);
        },

        async loadOrders() {
            try {
                const res = await fetch('{{ route('kitchen.orders') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.orders = data.orders;
                    const now = new Date();
                    this.lastUpdate = now.getHours().toString().padStart(2,'0') + ':' +
                                      now.getMinutes().toString().padStart(2,'0') + ':' +
                                      now.getSeconds().toString().padStart(2,'0');
                }
            } catch (_) {}
        },

        async updateStatus(orderId, status) {
            try {
                const res = await fetch(`/kitchen/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({ kitchen_status: status }),
                });
                if (res.ok) await this.loadOrders();
            } catch (_) {}
        },
    };
}
</script>
</body>
</html>
