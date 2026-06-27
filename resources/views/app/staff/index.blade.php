@extends('layouts.app')
@section('title', 'Manajemen Staf')
@section('page-title', 'Manajemen Staf')
@section('page-subtitle', 'Kelola akun kasir dan manajer cabang')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Daftar Staf</h2>
    <a href="{{ route('app.staff.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Staf
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Nama</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Email</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Role</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Cabang</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($staff as $member)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 font-medium text-slate-800">{{ $member->name }}</td>
                <td class="px-5 py-3.5 text-slate-600">{{ $member->email }}</td>
                <td class="px-5 py-3.5">
                    @if($member->hasRole('branch_manager'))
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Manajer</span>
                    @else
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Kasir</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-600">{{ $member->branch?->name ?? '—' }}</td>
                <td class="px-5 py-3.5">
                    @if($member->is_active)
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('app.staff.edit', $member) }}"
                           class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('app.staff.destroy', $member) }}" method="POST"
                              onsubmit="return confirm('Hapus akun {{ addslashes($member->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada staf. Tambah kasir pertama Anda.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
