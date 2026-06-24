<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dikirim</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-amber-50 min-h-screen flex items-center justify-center px-4">

<div class="max-w-sm w-full text-center">
    <div class="bg-white rounded-3xl shadow-sm p-8">
        <div class="text-6xl mb-4">🎉</div>
        <h1 class="text-xl font-black text-slate-800 mb-2">Pesanan Terkirim!</h1>
        @php $name = request('name'); @endphp
        @if ($name)
            <p class="text-slate-500 text-sm">Hei <strong>{{ $name }}</strong>, pesananmu sudah masuk ke kasir.</p>
        @else
            <p class="text-slate-500 text-sm">Pesananmu sudah masuk ke kasir.</p>
        @endif
        <p class="text-slate-400 text-xs mt-2">Silakan tunggu, kami segera siapkan pesananmu.</p>
        <p class="text-slate-400 text-xs mt-1">Pembayaran dilakukan di kasir.</p>

        <div class="mt-8 bg-amber-50 rounded-2xl p-4">
            <p class="text-xs text-amber-700 font-semibold">Ingin pesan lagi?</p>
            <p class="text-xs text-amber-600 mt-1">Scan ulang QR di mejamu</p>
        </div>
    </div>
</div>

</body>
</html>
