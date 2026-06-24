@extends('layouts.pos')
@section('title', 'Kasir')

@section('content')
<div class="flex h-[calc(100vh-3rem)] overflow-hidden"
     x-data="posApp()"
     x-init="loadProducts()">

    {{-- LEFT: Product Browser --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Search + Category bar --}}
        <div class="bg-white border-b border-slate-100 px-4 py-3 flex gap-3 items-center shrink-0">
            <input type="text" x-model="search" placeholder="Cari produk..."
                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-amber-500 bg-slate-50">

            <div class="flex items-center gap-2 overflow-x-auto">
                <button @click="activeCategory = null"
                        :class="activeCategory === null
                            ? 'bg-amber-500 text-white'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors">
                    Semua
                </button>
                <template x-for="cat in categories" :key="cat.id">
                    <button @click="activeCategory = cat.id"
                            :class="activeCategory === cat.id
                                ? 'bg-amber-500 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors">
                        <span x-text="(cat.icon ? cat.icon + ' ' : '') + cat.name"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div x-show="loading" class="flex items-center justify-center h-40 text-slate-400 text-sm">
                Memuat produk...
            </div>

            <div x-show="!loading && filteredProducts.length === 0" class="flex items-center justify-center h-40 text-slate-400 text-sm">
                Tidak ada produk ditemukan.
            </div>

            <div x-show="!loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <button type="button" @click="openProduct(product)"
                            :class="!product.is_available ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer'"
                            :disabled="!product.is_available"
                            class="bg-white rounded-2xl p-3 text-left transition-all shadow-sm border border-slate-100">
                        <div class="w-full aspect-square bg-amber-50 rounded-xl overflow-hidden flex items-center justify-center mb-2">
                            <template x-if="product.image_url">
                                <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!product.image_url">
                                <span class="text-3xl" x-text="product.category_icon || '🍽️'"></span>
                            </template>
                        </div>
                        <div class="text-xs font-semibold text-slate-800 leading-tight line-clamp-2 mb-1" x-text="product.name"></div>
                        <div class="text-xs text-amber-600 font-bold" x-text="'Rp ' + formatNumber(product.price)"></div>
                        <div class="mt-1">
                            <template x-if="!product.is_available">
                                <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">Tdk Tersedia</span>
                            </template>
                            <template x-if="product.is_available && product.track_stock && product.stock !== null && product.stock <= 0">
                                <span class="text-xs text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">Tersedia</span>
                            </template>
                            <template x-if="product.is_available && product.track_stock && product.stock > 0">
                                <span class="text-xs text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded-md" x-text="'Stok: ' + Math.floor(product.stock)"></span>
                            </template>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="w-80 xl:w-96 flex flex-col bg-white border-l border-slate-100 shrink-0">

        {{-- Order type & table --}}
        <div class="px-4 py-3 border-b border-slate-100 space-y-2 shrink-0">
            <div class="flex gap-2">
                <button @click="orderType = 'dine_in'"
                        :class="orderType === 'dine_in' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'"
                        class="flex-1 py-1.5 rounded-xl text-xs font-medium transition-colors">
                    🍽️ Makan di Sini
                </button>
                <button @click="orderType = 'takeaway'"
                        :class="orderType === 'takeaway' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'"
                        class="flex-1 py-1.5 rounded-xl text-xs font-medium transition-colors">
                    📦 Bawa Pulang
                </button>
            </div>

            {{-- Nama pelanggan --}}
            <input type="text" x-model="customerName" placeholder="Nama pelanggan (opsional)"
                   class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500 bg-white">

            {{-- No meja (dine in) --}}
            <div x-show="orderType === 'dine_in'">
                <select x-model="tableId" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500 bg-white">
                    <option value="">— Pilih No. Meja —</option>
                    @foreach ($tables as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-32 text-slate-300">
                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-sm">Keranjang kosong</span>
            </div>

            <template x-for="(item, index) in cart" :key="item.cartId">
                <div class="bg-slate-50 rounded-xl p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-slate-800 truncate" x-text="item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></div>
                            <template x-if="item.modifiers && item.modifiers.length > 0">
                                <div class="text-xs text-slate-400 mt-0.5" x-text="item.modifiers.map(m => m.option_name).join(', ')"></div>
                            </template>
                            <template x-if="item.notes">
                                <div class="text-xs text-slate-400 mt-0.5 italic" x-text="'Catatan: ' + item.notes"></div>
                            </template>
                        </div>
                        <button @click="removeItem(item.cartId)" class="text-red-400 hover:text-red-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <button @click="updateQty(item.cartId, -1)"
                                    class="w-6 h-6 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">−</button>
                            <span class="text-sm font-semibold text-slate-700 w-6 text-center" x-text="item.qty"></span>
                            <button @click="updateQty(item.cartId, 1)"
                                    class="w-6 h-6 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">+</button>
                        </div>
                        <span class="text-xs font-bold text-amber-600" x-text="'Rp ' + formatNumber(item.subtotal)"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totals & Checkout --}}
        <div class="border-t border-slate-100 px-4 py-3 space-y-2 shrink-0">
            {{-- Notes --}}
            <input type="text" x-model="notes" placeholder="Catatan order (opsional)"
                   class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500">

            {{-- Summary --}}
            <div class="space-y-1 text-xs text-slate-500">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + formatNumber(subtotal)"></span>
                </div>
                <div x-show="taxTotal > 0" class="flex justify-between">
                    <span>Pajak</span>
                    <span x-text="'Rp ' + formatNumber(taxTotal)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span x-text="'− Rp ' + formatNumber(discountAmount)"></span>
                </div>
            </div>

            <div class="flex justify-between items-center py-2 border-t border-slate-100">
                <span class="font-bold text-slate-800 text-sm">Total</span>
                <span class="font-bold text-amber-600 text-lg" x-text="'Rp ' + formatNumber(total)"></span>
            </div>

            <button @click="openPayment()"
                    :disabled="cart.length === 0"
                    :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                    class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                Bayar
            </button>
        </div>
    </div>

    {{-- Product Detail Modal (variant + modifier picker) --}}
    <div x-show="showProductModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-y-auto max-h-[90vh]" @click.stop>

            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800" x-text="selectedProduct?.name"></h3>
                    <p class="text-sm text-amber-600 font-semibold" x-text="'Rp ' + formatNumber(itemTotalPrice())"></p>
                </div>
                <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Variants --}}
                <template x-if="selectedProduct?.variants?.length > 0">
                    <div>
                        <p class="text-xs font-semibold text-slate-700 mb-2">Varian</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="v in selectedProduct.variants" :key="v.id">
                                <button type="button" @click="selectedVariant = v"
                                        :class="selectedVariant?.id === v.id ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                        class="border rounded-xl px-3 py-1.5 text-xs font-medium transition-colors">
                                    <span x-text="v.name + (v.price_adjustment !== 0 ? ' (' + (v.price_adjustment > 0 ? '+' : '') + 'Rp ' + formatNumber(v.price_adjustment) + ')' : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Modifier Groups --}}
                <template x-for="group in (selectedProduct?.modifier_groups || [])" :key="group.id">
                    <div>
                        <p class="text-xs font-semibold text-slate-700 mb-2">
                            <span x-text="group.name"></span>
                            <span x-show="group.is_required" class="text-red-500 ml-1">*</span>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="opt in group.options" :key="opt.id">
                                <button type="button" @click="toggleModifier(group, opt)"
                                        :class="isModifierSelected(group.id, opt.id) ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                        class="border rounded-xl px-3 py-1.5 text-xs font-medium transition-colors">
                                    <span x-text="opt.name + (opt.price > 0 ? ' +Rp ' + formatNumber(opt.price) : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Qty --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Jumlah</span>
                    <div class="flex items-center gap-3">
                        <button @click="itemQty = Math.max(1, itemQty - 1)"
                                class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 flex items-center justify-center">−</button>
                        <span class="text-base font-bold text-slate-800 w-8 text-center" x-text="itemQty"></span>
                        <button @click="itemQty++"
                                class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 flex items-center justify-center">+</button>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Catatan Item</label>
                    <input type="text" x-model="itemNotes" placeholder="Opsional (mis: pedas extra)"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                <button @click="confirmAddToCart()"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-2xl text-sm transition-colors">
                    Tambah ke Keranjang · <span x-text="'Rp ' + formatNumber(itemTotalPrice() * itemQty)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPayment" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Pembayaran</h3>
                <button @click="showPayment = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Diskon --}}
                <div>
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Diskon (Rp)</label>
                    <input type="number" x-model="discountAmount" min="0" placeholder="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                {{-- Total --}}
                <div class="bg-amber-50 rounded-2xl p-4 text-center">
                    <template x-if="discountAmount > 0">
                        <p class="text-xs text-emerald-600 mb-0.5" x-text="'Diskon − Rp ' + formatNumber(discountAmount)"></p>
                    </template>
                    <p class="text-xs text-amber-700 mb-1">Total Tagihan</p>
                    <p class="text-2xl font-black text-amber-600" x-text="'Rp ' + formatNumber(total)"></p>
                </div>

                {{-- Payment Method --}}
                <div>
                    <p class="text-xs font-semibold text-slate-700 mb-2">Metode Pembayaran</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['cash' => '💵 Tunai', 'qris' => '📱 QRIS', 'transfer' => '🏦 Transfer', 'card' => '💳 Kartu', 'credit' => '📒 Kredit'] as $val => $label)
                        <button type="button" @click="paymentMethod = '{{ $val }}'"
                                :class="paymentMethod === '{{ $val }}' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                class="border rounded-xl py-2 text-xs font-medium transition-colors text-center">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Reference (non-cash) --}}
                <div x-show="paymentMethod !== 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Nomor Referensi</label>
                    <input type="text" x-model="paymentRef" placeholder="Opsional"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                {{-- Paid amount --}}
                <div x-show="paymentMethod === 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Uang Diterima</label>
                    <input type="number" x-model="paidAmount" min="0" placeholder="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">

                    {{-- Quick amounts --}}
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <template x-for="amount in quickAmounts()" :key="amount">
                            <button type="button" @click="paidAmount = amount"
                                    class="bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-600 text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors"
                                    x-text="'Rp ' + formatNumber(amount)">
                            </button>
                        </template>
                    </div>

                    {{-- Change --}}
                    <div x-show="paidAmount >= total && total > 0"
                         class="mt-3 bg-emerald-50 rounded-xl p-3 flex justify-between items-center">
                        <span class="text-xs text-emerald-700 font-medium">Kembalian</span>
                        <span class="text-base font-black text-emerald-600" x-text="'Rp ' + formatNumber(change)"></span>
                    </div>
                </div>

                <button @click="checkout()"
                        :disabled="processing || (paymentMethod === 'cash' && paidAmount < total)"
                        :class="(processing || (paymentMethod === 'cash' && paidAmount < total))
                            ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                            : 'bg-amber-500 hover:bg-amber-600 text-white'"
                        class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                    <span x-text="processing ? 'Memproses...' : 'Konfirmasi Pembayaran'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        // State
        products: [],
        categories: [],
        loading: true,
        activeCategory: null,
        search: '',

        // Cart
        cart: [],
        orderType: 'dine_in',
        tableId: '',
        customerId: '',
        customerName: '',
        notes: '',
        discountAmount: 0,

        // Product modal
        showProductModal: false,
        selectedProduct: null,
        selectedVariant: null,
        selectedModifiers: {},
        itemNotes: '',
        itemQty: 1,

        // Payment
        showPayment: false,
        paymentMethod: 'cash',
        paidAmount: 0,
        paymentRef: '',
        processing: false,

        // Computed
        get filteredProducts() {
            return this.products.filter(p => {
                const matchCat = !this.activeCategory || p.category_id == this.activeCategory;
                const matchSearch = !this.search || p.name.toLowerCase().includes(this.search.toLowerCase());
                return matchCat && matchSearch;
            });
        },

        get subtotal() {
            return this.cart.reduce((s, i) => s + i.subtotal, 0);
        },

        get taxTotal() {
            return this.cart.reduce((s, i) => s + i.taxAmount, 0);
        },

        get total() {
            return Math.max(0, this.subtotal + this.taxTotal - parseFloat(this.discountAmount || 0));
        },

        get change() {
            return Math.max(0, parseFloat(this.paidAmount || 0) - this.total);
        },

        // Methods
        async loadProducts() {
            this.loading = true;
            try {
                const res = await fetch('/pos/products');
                const data = await res.json();
                this.products = data.products;
                this.categories = data.categories;
            } catch (e) {
                console.error('Gagal memuat produk:', e);
            }
            this.loading = false;
        },

        openProduct(product) {
            if (!product.is_available) return;
            if (product.variants.length === 0 && product.modifier_groups.length === 0) {
                this.addToCart(product, null, {}, 1, '');
            } else {
                this.selectedProduct = product;
                this.selectedVariant = product.variants.length > 0 ? product.variants[0] : null;
                this.selectedModifiers = {};
                this.itemNotes = '';
                this.itemQty = 1;
                this.showProductModal = true;
            }
        },

        itemTotalPrice() {
            if (!this.selectedProduct) return 0;
            const base = this.selectedProduct.price + (this.selectedVariant?.price_adjustment || 0);
            const modTotal = Object.values(this.selectedModifiers).flat().reduce((s, m) => s + m.price, 0);
            return base + modTotal;
        },

        toggleModifier(group, option) {
            if (!this.selectedModifiers[group.id]) {
                this.selectedModifiers[group.id] = [];
            }
            const list = this.selectedModifiers[group.id];
            const idx = list.findIndex(m => m.id === option.id);
            if (idx >= 0) {
                list.splice(idx, 1);
            } else if (group.is_multiple) {
                list.push(option);
            } else {
                this.selectedModifiers[group.id] = [option];
            }
        },

        isModifierSelected(groupId, optionId) {
            return (this.selectedModifiers[groupId] || []).some(m => m.id === optionId);
        },

        confirmAddToCart() {
            if (!this.selectedProduct) return;
            this.addToCart(
                this.selectedProduct,
                this.selectedVariant,
                this.selectedModifiers,
                this.itemQty,
                this.itemNotes
            );
        },

        addToCart(product, variant, modifiers, qty, notes) {
            const modList = Object.values(modifiers).flat();
            const modPrice = modList.reduce((s, m) => s + m.price, 0);
            const unitPrice = product.price + (variant?.price_adjustment || 0) + modPrice;

            const modKey = modList.map(m => m.id).sort().join(',');
            const existing = this.cart.find(i =>
                i.product_id === product.id &&
                (i.product_variant_id || null) === (variant?.id || null) &&
                i.modifiers.map(m => m.id).sort().join(',') === modKey &&
                (i.notes || '') === (notes || '')
            );

            if (existing) {
                existing.qty += qty;
                existing.subtotal = existing.unit_price * existing.qty;
                existing.taxAmount = existing.subtotal * (existing.tax_rate / 100);
            } else {
                const subtotal = unitPrice * qty;
                this.cart.push({
                    cartId: Date.now() + Math.random(),
                    product_id: product.id,
                    product_variant_id: variant?.id || null,
                    product_name: product.name,
                    variant_name: variant?.name || null,
                    unit_price: unitPrice,
                    tax_rate: product.tax_rate,
                    qty: qty,
                    taxAmount: subtotal * (product.tax_rate / 100),
                    subtotal: subtotal,
                    notes: notes,
                    modifiers: modList,
                });
            }
            this.showProductModal = false;
        },

        removeItem(cartId) {
            this.cart = this.cart.filter(i => i.cartId !== cartId);
        },

        updateQty(cartId, delta) {
            const item = this.cart.find(i => i.cartId === cartId);
            if (!item) return;
            item.qty = Math.max(1, item.qty + delta);
            item.subtotal = item.unit_price * item.qty;
            item.taxAmount = item.subtotal * (item.tax_rate / 100);
        },

        openPayment() {
            if (this.cart.length === 0) return;
            this.paidAmount = this.total;
            this.showPayment = true;
        },

        quickAmounts() {
            const t = this.total;
            const base = [t, 50000, 100000, 200000];
            const rounded = [
                Math.ceil(t / 5000) * 5000,
                Math.ceil(t / 10000) * 10000,
                Math.ceil(t / 50000) * 50000,
            ];
            return [...new Set([...base, ...rounded])].filter(a => a >= t).sort((a, b) => a - b).slice(0, 5);
        },

        async checkout() {
            if (this.cart.length === 0 || this.processing) return;
            if (this.paymentMethod === 'cash' && parseFloat(this.paidAmount) < this.total) return;

            this.processing = true;

            const payload = {
                type: this.orderType,
                table_id: this.tableId || null,
                customer_id: this.customerId || null,
                customer_name: this.customerName || null,
                notes: this.notes || null,
                discount_amount: this.discountAmount || 0,
                paid_amount: this.paymentMethod === 'cash' ? parseFloat(this.paidAmount) : this.total,
                payment_method: this.paymentMethod,
                payment_reference: this.paymentRef || null,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    product_variant_id: item.product_variant_id || null,
                    qty: item.qty,
                    notes: item.notes || null,
                    modifiers: item.modifiers.map(m => ({ modifier_option_id: m.id })),
                })),
            };

            try {
                const res = await fetch('/pos/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (res.ok) {
                    window.location.href = `/pos/orders/${data.order_id}/receipt`;
                } else {
                    alert(data.message || JSON.stringify(data.errors || 'Terjadi kesalahan'));
                }
            } catch (e) {
                alert('Gagal memproses pembayaran. Periksa koneksi internet.');
            }

            this.processing = false;
        },

        formatNumber(n) {
            return Number(n || 0).toLocaleString('id-ID');
        },
    };
}
</script>
@endsection
