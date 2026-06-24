@extends('layouts.app')
@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')
@section('page-title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="mb-5">
    <a href="{{ route('app.categories.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">&larr; Kembali</a>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
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

    <form action="{{ isset($category) ? route('app.categories.update', $category) : route('app.categories.store') }}"
          method="POST" class="space-y-4">
        @csrf
        @if (isset($category))
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">Nama <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $category->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: Minuman">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="icon">Ikon (emoji, opsional)</label>
            <input type="text" id="icon" name="icon"
                   value="{{ old('icon', $category->icon ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: 🍹">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="color">Warna (opsional)</label>
            <div class="flex items-center gap-3">
                <input type="color" id="color" name="color"
                       value="{{ old('color', $category->color ?? '#f59e0b') }}"
                       class="h-10 w-16 border border-slate-200 rounded-xl cursor-pointer outline-none focus:border-amber-500">
                <span class="text-xs text-slate-400">Pilih warna untuk badge kategori</span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="sort_order">Urutan</label>
            <input type="number" id="sort_order" name="sort_order" min="0"
                   value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500">
        </div>

        <div class="flex items-center gap-3 pt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 accent-amber-500 cursor-pointer">
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Aktif</label>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($category) ? 'Simpan Perubahan' : 'Tambah Kategori' }}
            </button>
            <a href="{{ route('app.categories.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
