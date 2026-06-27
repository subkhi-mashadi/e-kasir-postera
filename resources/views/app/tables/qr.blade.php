@extends('layouts.app')
@section('title', 'QR Meja — ' . $table->name)
@section('page-title', 'QR Meja')

@push('styles')
<style>
    @media print {
        body * { visibility: hidden !important; }
        #qr-card, #qr-card * { visibility: visible !important; }
        #qr-card {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

@section('content')

<div class="max-w-md mx-auto space-y-5">
    {{-- QR Card --}}
    <div id="qr-card" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center gap-4">
        <div class="text-center">
            <p class="text-lg font-bold text-slate-800">{{ $table->name }}</p>
            <p class="text-sm text-slate-500">{{ $table->branch->name ?? '' }}</p>
        </div>

        <div class="w-full flex justify-center p-4">
            {!! $svg !!}
        </div>

        <div class="w-full text-center">
            <p class="text-xs text-slate-400 break-all">{{ $url }}</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 no-print">
        <button onclick="window.print()"
                class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors text-center">
            Cetak QR
        </button>

        <form action="{{ route('app.tables.regenerate-qr', $table) }}" method="POST"
              onsubmit="return confirm('QR lama akan tidak berlaku. Lanjutkan?')"
              class="flex-1">
            @csrf
            <button type="submit"
                    class="w-full border border-red-300 text-red-500 hover:bg-red-50 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                Regenerate QR
            </button>
        </form>
    </div>

    <p class="text-xs text-slate-400 text-center no-print">
        Perhatian: regenerate QR akan membuat QR lama tidak berfungsi.
    </p>
</div>
@endsection
