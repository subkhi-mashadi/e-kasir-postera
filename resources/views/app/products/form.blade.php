@extends('layouts.app')
@section('title', isset($product) ? 'Edit Produk' : 'Tambah Produk')
@section('page-title', isset($product) ? 'Edit Produk' : 'Tambah Produk')

@section('content')

<div class="bg-white rounded-2xl shadow-sm p-6"
     x-data="{
         variants: {{ isset($product) ? $product->variants->toJson() : '[]' }},
         addVariant() { this.variants.push({ name: '', price_adjustment: 0 }) },
         removeVariant(i) { this.variants.splice(i, 1) }
     }">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($product) ? 'Edit Produk' : 'Tambah Produk Baru' }}
    </h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($product) ? route('app.products.update', $product) : route('app.products.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @if (isset($product))
            @method('PUT')
        @endif

        {{-- Image --}}
        <div x-data="{
            preview: '{{ isset($product) ? $product->getFirstMediaUrl('images', 'thumb') : '' }}',
            onFile(e) {
                const f = e.target.files[0];
                if (f) this.preview = URL.createObjectURL(f);
            }
        }">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Produk</label>
            <div class="flex items-start gap-4">
                <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center bg-slate-50 shrink-0">
                    <template x-if="preview">
                        <img :src="preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-3xl">🍽️</span>
                    </template>
                </div>
                <div class="flex-1 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors w-fit">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Pilih Foto
                        <input type="file" name="image" accept="image/*" class="sr-only" @change="onFile($event)">
                    </label>
                    <p class="text-xs text-slate-400">JPG, PNG, WebP. Maks 2MB.</p>
                    @isset($product)
                        @if ($product->hasMedia('images'))
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-red-500">
                            <input type="checkbox" name="remove_image" value="1" class="accent-red-500">
                            Hapus foto
                        </label>
                        @endif
                    @endisset
                </div>
            </div>
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">
                Nama <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $product->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: Nasi Goreng Spesial">
        </div>

        {{-- Category --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="category_id">Kategori</label>
            <select id="category_id" name="category_id"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SKU & Barcode (edit only) --}}
        @isset($product)
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="sku">SKU</label>
                <input type="text" id="sku" name="sku"
                       value="{{ old('sku', $product->sku) }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="barcode">Barcode</label>
                <input type="text" id="barcode" name="barcode"
                       value="{{ old('barcode', $product->barcode) }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500">
            </div>
        </div>
        @endisset

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 resize-none"
                      placeholder="Deskripsi produk (opsional)">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        {{-- Unit --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="unit">Satuan</label>
            <input type="text" id="unit" name="unit"
                   value="{{ old('unit', $product->unit ?? 'porsi') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="porsi">
        </div>

        {{-- Price & Cost Price --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="price">
                    Harga Jual <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                    <input type="number" id="price" name="price" required min="0" step="1"
                           value="{{ old('price', $product->price ?? '') }}"
                           class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none focus:border-amber-500"
                           placeholder="0">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="cost_price">Harga Modal</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                    <input type="number" id="cost_price" name="cost_price" min="0" step="1"
                           value="{{ old('cost_price', $product->cost_price ?? '') }}"
                           class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none focus:border-amber-500"
                           placeholder="0">
                </div>
            </div>
        </div>

        {{-- Tax Rate --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="tax_rate">Tarif Pajak (%)</label>
            <input type="number" id="tax_rate" name="tax_rate" min="0" max="100" step="0.01"
                   value="{{ old('tax_rate', isset($product) ? $product->tax_rate : ((float) (auth()->user()->company?->tax_rate ?? 0) ?: 11)) }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="0">
        </div>

        {{-- Checkboxes --}}
        <div class="flex flex-col gap-3 pt-1">
            <div class="flex items-center gap-3">
                <input type="hidden" name="track_stock" value="0">
                <input type="checkbox" id="track_stock" name="track_stock" value="1"
                       {{ old('track_stock', $product->track_stock ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 accent-amber-500 cursor-pointer">
                <label for="track_stock" class="text-sm font-medium text-slate-700 cursor-pointer">Pantau Stok</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 accent-amber-500 cursor-pointer">
                <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Aktif</label>
            </div>
        </div>

        {{-- Modifier Groups --}}
        @if ($modifierGroups->count())
        <div>
            <p class="text-sm font-medium text-slate-700 mb-2">Modifier Groups</p>
            <div class="border border-slate-200 rounded-xl p-4 space-y-2">
                @foreach ($modifierGroups as $g)
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="mg_{{ $g->id }}" name="modifier_groups[]" value="{{ $g->id }}"
                           {{ isset($product) && $product->modifierGroups->pluck('id')->contains($g->id) ? 'checked' : '' }}
                           class="w-4 h-4 accent-amber-500 cursor-pointer">
                    <label for="mg_{{ $g->id }}" class="text-sm text-slate-700 cursor-pointer">{{ $g->name }}</label>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Variants --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-700">Varian Produk</p>
                <button type="button" @click="addVariant"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium border border-amber-300 px-3 py-1 rounded-lg">
                    + Tambah Varian
                </button>
            </div>
            <div class="space-y-3">
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                        <div class="flex-1">
                            <label class="block text-xs text-slate-500 mb-1">Nama Varian</label>
                            <input type="text" :name="`variants[${index}][name]`" x-model="variant.name"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500 bg-white"
                                   placeholder="Contoh: Ukuran L">
                        </div>
                        <div class="w-36">
                            <label class="block text-xs text-slate-500 mb-1">Selisih Harga</label>
                            <input type="number" :name="`variants[${index}][price_adjustment]`" x-model="variant.price_adjustment"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500 bg-white"
                                   placeholder="0">
                        </div>
                        <button type="button" @click="removeVariant(index)"
                                class="mt-5 text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <p x-show="variants.length === 0" class="text-xs text-slate-400 py-2">Belum ada varian.</p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($product) ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
            <a href="{{ route('app.products.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
