<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postera — Pilih Cabang</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-xl font-bold text-slate-800">Pilih Cabang</h1>
        <p class="text-slate-500 text-sm mt-1">Pilih cabang yang akan Anda kelola</p>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 p-6 space-y-3">
        @foreach ($branches as $branch)
        <form method="POST" action="{{ route('branch.select.post') }}">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <button type="submit"
                class="w-full text-left border border-slate-200 hover:border-amber-400 hover:bg-amber-50 rounded-2xl px-5 py-4 transition-colors group">
                <div class="font-semibold text-slate-800 group-hover:text-amber-700">{{ $branch->name }}</div>
                @if($branch->address)
                <div class="text-xs text-slate-400 mt-0.5">{{ $branch->address }}</div>
                @endif
            </button>
        </form>
        @endforeach

        <div class="pt-3 border-t border-slate-100 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-slate-400 hover:text-slate-600">Keluar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
