@extends('layouts.app')
@section('title', isset($table) ? 'Edit Meja' : 'Tambah Meja')
@section('page-title', isset($table) ? 'Edit Meja' : 'Tambah Meja')

@section('content')
<div class="mb-5">
    <a href="{{ route('app.tables.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">&larr; Kembali</a>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 max-w-lg">
    <h2 class="text-base font-semibold text-slate-800 mb-5">
        {{ isset($table) ? 'Edit Meja' : 'Tambah Meja Baru' }}
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

    <form action="{{ isset($table) ? route('app.tables.update', $table) : route('app.tables.store') }}"
          method="POST" class="space-y-5">
        @csrf
        @if (isset($table))
            @method('PUT')
        @endif

        {{-- Branch --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="branch_id">
                Cabang <span class="text-red-500">*</span>
            </label>
            <select id="branch_id" name="branch_id" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
                <option value="">— Pilih Cabang —</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ old('branch_id', $table->branch_id ?? $defaultBranch) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">
                Nama Meja <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name', $table->name ?? '') }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="Contoh: Meja 1">
        </div>

        {{-- Capacity --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="capacity">Kapasitas (orang)</label>
            <input type="number" id="capacity" name="capacity" min="1"
                   value="{{ old('capacity', $table->capacity ?? 4) }}"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                   placeholder="4">
        </div>

        {{-- Is Active --}}
        <div class="flex items-center gap-3 pt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $table->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 accent-amber-500 cursor-pointer">
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Aktif</label>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                {{ isset($table) ? 'Simpan Perubahan' : 'Tambah Meja' }}
            </button>
            <a href="{{ route('app.tables.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection
