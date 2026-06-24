<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $dari    = $request->filled('dari')   ? $request->dari   : today()->subDays(29)->format('Y-m-d');
        $sampai  = $request->filled('sampai') ? $request->sampai : today()->format('Y-m-d');

        $base = Order::where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai);

        $totalPendapatan = (clone $base)->sum('total');
        $totalTransaksi  = (clone $base)->count();
        $rataRata        = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        // Daily breakdown
        $perHari = (clone $base)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as transaksi, SUM(total) as pendapatan')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('tanggal')
            ->get();

        // Payment method breakdown
        $perMetode = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('payments.method, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get();

        // Top 10 products
        $topProduk = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('order_items.product_name, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as pendapatan')
            ->groupBy('order_items.product_name')
            ->orderByDesc('pendapatan')
            ->limit(10)
            ->get();

        return view('app.reports.sales', compact(
            'dari', 'sampai',
            'totalPendapatan', 'totalTransaksi', 'rataRata',
            'perHari', 'perMetode', 'topProduk'
        ));
    }
}
