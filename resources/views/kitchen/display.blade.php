<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapur — {{ $branch->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak]{display:none!important}
        @keyframes pulse-green { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.4)} 50%{box-shadow:0 0 0 8px rgba(16,185,129,0)} }
        .pulse-ready { animation: pulse-green 1.5s infinite; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen">

<div x-data="kitchenDisplay()" x-init="init()">

    {{-- Top bar --}}
    <div class="bg-slate-800 border-b border-slate-700 px-6 py-3 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <span class="text-2xl">👨‍🍳</span>
            <div>
                <p class="font-black text-lg leading-tight">Dapur — {{ $branch->name }}</p>
                <p class="text-slate-400 text-xs" x-text="'Diperbarui ' + lastUpdate"></p>
            </div>
        </div>
        <div class="flex gap-2 text-xs font-bold">
            <span class="bg-amber-500/20 text-amber-400 px-3 py-1 rounded-full" x-text="pendingCount + ' Antrian'"></span>
            <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full" x-text="preparingCount + ' Diproses'"></span>
            <span class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full" x-text="readyCount + ' Siap Antar'"></span>
        </div>
    </div>

    {{-- Columns --}}
    <div class="grid grid-cols-3 gap-0 min-h-[calc(100vh-60px)]">

        {{-- PENDING column --}}
        <div class="border-r border-slate-700/60 flex flex-col">
            <div class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-3">
                <p class="text-amber-400 font-black text-sm uppercase tracking-widest">Antrian</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in pending" :key="order.id">
                    <div class="bg-slate-800 rounded-xl border border-amber-500/30 overflow-hidden">
                        <div class="bg-amber-500/10 px-3 py-2 flex justify-between items-start">
                            <div>
                                <p class="text-sm font-black text-amber-300" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span x-show="order.source === 'qr'" class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full">QR</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-700/50">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-white" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                    <span x-show="item.modifiers" class="text-slate-400 text-xs block ml-4" x-text="item.modifiers"></span>
                                    <span x-show="item.notes" class="text-amber-300 text-xs block ml-4" x-text="'📝 ' + item.notes"></span>
                                </div>
                            </template>
                            <p x-show="order.notes" class="text-amber-300 text-xs mt-1" x-text="'Catatan: ' + order.notes"></p>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'preparing')"
                                    class="w-full bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Mulai Proses →
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="pending.length === 0" class="text-center py-10 text-slate-600 text-sm">Tidak ada antrian</div>
            </div>
        </div>

        {{-- PREPARING column --}}
        <div class="border-r border-slate-700/60 flex flex-col">
            <div class="bg-blue-500/10 border-b border-blue-500/20 px-4 py-3">
                <p class="text-blue-400 font-black text-sm uppercase tracking-widest">Sedang Diproses</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in preparing" :key="order.id">
                    <div class="bg-slate-800 rounded-xl border border-blue-500/30 overflow-hidden">
                        <div class="bg-blue-500/10 px-3 py-2 flex justify-between items-start">
                            <div>
                                <p class="text-sm font-black text-blue-300" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span x-show="order.source === 'qr'" class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full">QR</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-700/50">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-white" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                    <span x-show="item.modifiers" class="text-slate-400 text-xs block ml-4" x-text="item.modifiers"></span>
                                    <span x-show="item.notes" class="text-amber-300 text-xs block ml-4" x-text="'📝 ' + item.notes"></span>
                                </div>
                            </template>
                            <p x-show="order.notes" class="text-amber-300 text-xs mt-1" x-text="'Catatan: ' + order.notes"></p>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'ready')"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Selesai, Siap Antar ✓
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="preparing.length === 0" class="text-center py-10 text-slate-600 text-sm">Tidak ada pesanan diproses</div>
            </div>
        </div>

        {{-- READY column --}}
        <div class="flex flex-col">
            <div class="bg-emerald-500/10 border-b border-emerald-500/20 px-4 py-3">
                <p class="text-emerald-400 font-black text-sm uppercase tracking-widest">Siap Diantarkan</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <template x-for="order in ready" :key="order.id">
                    <div class="bg-slate-800 rounded-xl border border-emerald-500/50 pulse-ready overflow-hidden">
                        <div class="bg-emerald-500/10 px-3 py-2 flex justify-between items-start">
                            <div>
                                <p class="text-sm font-black text-emerald-300" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name || '') + ' · ' + order.created_at"></p>
                            </div>
                            <span class="text-lg">🛎️</span>
                        </div>
                        <div class="px-3 py-2 space-y-1 border-b border-slate-700/50">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-sm">
                                    <span class="font-bold text-white" x-text="item.qty + '× ' + item.product_name"></span>
                                    <span x-show="item.variant_name" class="text-slate-400 text-xs" x-text="' · ' + item.variant_name"></span>
                                </div>
                            </template>
                        </div>
                        <div class="px-3 py-2">
                            <button @click="updateStatus(order.id, 'delivered')"
                                    class="w-full bg-slate-600 hover:bg-slate-500 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                Sudah Diantarkan ✓
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="ready.length === 0" class="text-center py-10 text-slate-600 text-sm">Tidak ada pesanan siap antar</div>
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

        get pending()   { return this.orders.filter(o => o.kitchen_status === 'pending'); },
        get preparing() { return this.orders.filter(o => o.kitchen_status === 'preparing'); },
        get ready()     { return this.orders.filter(o => o.kitchen_status === 'ready'); },
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
                    this.lastUpdate = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0') + ':' + now.getSeconds().toString().padStart(2,'0');
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
                if (res.ok) {
                    await this.loadOrders();
                }
            } catch (_) {}
        },
    };
}
</script>
</body>
</html>
