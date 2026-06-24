@extends('layouts.app')
@section('title', isset($group) ? 'Edit Modifier' : 'Tambah Modifier')
@section('page-title', isset($group) ? 'Edit Modifier Group' : 'Tambah Modifier Group')

@section('content')
<div class="mb-5">
    <a href="{{ route('app.modifiers.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">&larr; Kembali</a>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6"
     x-data="{
         options: {{ isset($group) ? $group->options->map(fn($o) => ['name' => $o->name, 'price' => $o->price])->toJson() : '[]' }},
         addOption() { this.options.push({ name: '', price: 0 }) },
         removeOption(i) { this.options.splice(i, 1) }
     }">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($group) ? 'Edit Modifier Group' : 'Tambah Modifier Group' }}
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

    <form action="{{ isset($group) ? route('app.modifiers.update', $group) : route('app.modifiers.store') }}"
          method="POST" class="space-y-5">
        @csrf
        @if (isset($group))
            @method('PUT')
        @endif

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">
                Nama Group <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $group->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: Tingkat Kepedasan">
        </div>

        {{-- Checkboxes --}}
        <div class="flex flex-col gap-3 pt-1">
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" id="is_required" name="is_required" value="1"
                       {{ old('is_required', $group->is_required ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 accent-amber-500 cursor-pointer">
                <label for="is_required" class="text-sm font-medium text-slate-700 cursor-pointer">Wajib dipilih</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_multiple" value="0">
                <input type="checkbox" id="is_multiple" name="is_multiple" value="1"
                       {{ old('is_multiple', $group->is_multiple ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 accent-amber-500 cursor-pointer">
                <label for="is_multiple" class="text-sm font-medium text-slate-700 cursor-pointer">Boleh pilih banyak</label>
            </div>
        </div>

        {{-- Min & Max Select --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="min_select">Min Pilihan</label>
                <input type="number" id="min_select" name="min_select" min="0"
                       value="{{ old('min_select', $group->min_select ?? 0) }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="max_select">Maks Pilihan</label>
                <input type="number" id="max_select" name="max_select" min="0"
                       value="{{ old('max_select', $group->max_select ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="Kosongkan = tidak terbatas">
            </div>
        </div>

        {{-- Options --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-slate-700">Opsi</p>
                <button type="button" @click="addOption"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium border border-amber-300 px-3 py-1 rounded-lg">
                    + Tambah Opsi
                </button>
            </div>
            <div class="space-y-3">
                <template x-for="(option, index) in options" :key="index">
                    <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                        <div class="flex-1">
                            <label class="block text-xs text-slate-500 mb-1">Nama Opsi</label>
                            <input type="text" :name="`options[${index}][name]`" x-model="option.name"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500 bg-white"
                                   placeholder="Contoh: Pedas Banget">
                        </div>
                        <div class="w-36">
                            <label class="block text-xs text-slate-500 mb-1">Harga Tambahan</label>
                            <input type="number" :name="`options[${index}][price]`" x-model="option.price"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500 bg-white"
                                   placeholder="0">
                        </div>
                        <button type="button" @click="removeOption(index)"
                                class="mt-5 text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <p x-show="options.length === 0" class="text-xs text-slate-400 py-2">Belum ada opsi.</p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($group) ? 'Simpan Perubahan' : 'Tambah Modifier Group' }}
            </button>
            <a href="{{ route('app.modifiers.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
