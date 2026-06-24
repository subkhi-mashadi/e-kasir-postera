@extends('layouts.app')
@section('title', 'Modifier')
@section('page-title', 'Modifier Groups')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Modifier Groups</h2>
    <a href="{{ route('app.modifiers.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
        + Tambah Grup Modifier
    </a>
</div>

@if ($groups->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm px-6 py-14 text-center text-slate-400 text-sm">
        Belum ada modifier group.
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($groups as $group)
        <div class="bg-white rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-slate-800">{{ $group->name }}</h3>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @if ($group->is_required)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Wajib</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Opsional</span>
                        @endif
                        @if ($group->is_multiple)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Multi Pilih</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Pilih Satu</span>
                        @endif
                        @if ($group->min_select || $group->max_select)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                {{ $group->min_select ?? 0 }}–{{ $group->max_select ?? '∞' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Options --}}
            <ul class="divide-y divide-slate-100">
                @forelse ($group->options as $option)
                <li class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-700">{{ $option->name }}</span>
                    <span class="text-xs font-medium text-slate-500">
                        @if ($option->price > 0)
                            +Rp {{ number_format($option->price, 0, ',', '.') }}
                        @elseif ($option->price < 0)
                            −Rp {{ number_format(abs($option->price), 0, ',', '.') }}
                        @else
                            Gratis
                        @endif
                    </span>
                </li>
                @empty
                <li class="py-2 text-xs text-slate-400">Belum ada opsi.</li>
                @endforelse
            </ul>

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                <a href="{{ route('app.modifiers.edit', $group) }}"
                   class="flex-1 text-center border border-amber-400 text-amber-600 hover:bg-amber-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                    Edit
                </a>
                <form action="{{ route('app.modifiers.destroy', $group) }}" method="POST"
                      onsubmit="return confirm('Hapus modifier group ini?')" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full border border-red-300 text-red-500 hover:bg-red-50 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
