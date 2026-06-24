@extends('layouts.app')
@section('title', 'Cabang')
@section('page-title', 'Cabang')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Daftar Cabang</h2>
    <a href="{{ route('app.branches.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Cabang
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Nama</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Kode</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Telepon</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Staff</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Meja</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($branches as $branch)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 font-medium text-slate-800">{{ $branch->name }}</td>
                <td class="px-5 py-3.5 text-slate-600 font-mono">{{ $branch->code ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-600">{{ $branch->phone ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-600">{{ $branch->users_count }}</td>
                <td class="px-5 py-3.5 text-slate-600">{{ $branch->tables_count }}</td>
                <td class="px-5 py-3.5">
                    @if ($branch->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('app.branches.edit', $branch) }}"
                           class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('app.branches.destroy', $branch) }}" method="POST"
                              onsubmit="return confirm('Hapus cabang ini?')">
                            @csrf
                            @method('DELETE')
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
                <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada cabang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
