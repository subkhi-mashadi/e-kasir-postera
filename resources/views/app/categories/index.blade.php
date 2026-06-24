@extends('layouts.app')
@section('title', 'Kategori')
@section('page-title', 'Kategori Menu')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Kategori Menu</h2>
    <a href="{{ route('app.categories.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Kategori
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Nama</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Produk</th>
                <th class="text-left px-5 py-3.5 font-medium text-slate-600">Status</th>
                <th class="px-5 py-3.5 font-medium text-slate-600 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($categories as $category)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 text-slate-800">
                    <div class="flex items-center gap-2">
                        @if ($category->icon)
                            <span class="text-lg leading-none">{{ $category->icon }}</span>
                        @endif
                        <span class="font-medium">{{ $category->name }}</span>
                        @if ($category->color)
                            <span class="inline-block w-3 h-3 rounded-full border border-slate-200"
                                  style="background-color: {{ $category->color }}"></span>
                        @endif
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-600">{{ $category->products_count }}</td>
                <td class="px-5 py-3.5">
                    @if ($category->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('app.categories.edit', $category) }}"
                           class="border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('app.categories.destroy', $category) }}" method="POST"
                              onsubmit="return confirm('Hapus kategori ini?')">
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
                <td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada kategori.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
