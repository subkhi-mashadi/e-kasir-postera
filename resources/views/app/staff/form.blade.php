@extends('layouts.app')
@section('title', isset($staff) ? 'Edit Staf' : 'Tambah Staf')
@section('page-title', isset($staff) ? 'Edit Staf' : 'Tambah Staf')
@section('page-subtitle', isset($staff) ? 'Ubah data akun staf' : 'Tambah kasir atau manajer cabang baru')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <form action="{{ isset($staff) ? route('app.staff.update', $staff) : route('app.staff.store') }}"
              method="POST" class="space-y-5">
            @csrf
            @isset($staff) @method('PUT') @endisset

            @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $staff->name ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="Nama kasir">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $staff->email ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="kasir@email.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Password {{ isset($staff) ? '(kosongkan jika tidak diubah)' : '' }}
                    @unless(isset($staff))<span class="text-red-500">*</span>@endunless
                </label>
                <input type="password" name="password"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500"
                       placeholder="{{ isset($staff) ? '••••••' : 'Min. 6 karakter' }}"
                       {{ isset($staff) ? '' : 'required' }}>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Cabang <span class="text-red-500">*</span>
                </label>
                <select name="branch_id"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
                    <option value="">Pilih cabang</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ old('branch_id', $staff->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-500 bg-white">
                    @php $currentRole = isset($staff) ? ($staff->getRoleNames()->first() ?? 'cashier') : old('role', 'cashier'); @endphp
                    <option value="cashier"     {{ $currentRole === 'cashier'         ? 'selected' : '' }}>Kasir</option>
                    <option value="branch_manager" {{ $currentRole === 'branch_manager' ? 'selected' : '' }}>Manajer Cabang</option>
                </select>
            </div>

            @isset($staff)
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ $staff->is_active ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-amber-500">
                <label for="is_active" class="text-sm font-medium text-slate-700">Akun aktif</label>
            </div>
            @endisset

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                    {{ isset($staff) ? 'Simpan Perubahan' : 'Tambah Staf' }}
                </button>
                <a href="{{ route('app.staff.index') }}"
                   class="flex-1 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-5 py-2.5 rounded-xl text-sm transition-colors text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
