<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $query = Order::with(['table', 'items'])
            ->where('branch_id', $branchId)
            ->whereIn('status', ['paid', 'cancelled', 'open'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('invoice_no', 'like', "%{$s}%")
                ->orWhere('customer_name', 'like', "%{$s}%")
            );
        }

        $orders = $query->paginate(25)->withQueryString();

        return view('app.orders.index', compact('orders'));
    }
}
