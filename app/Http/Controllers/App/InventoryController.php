<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->branch_id ?? session('branch_id') ?? auth()->user()->branch_id;
        $branches = Branch::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();

        $inventories = Inventory::with('product.category')
            ->where('branch_id', $branchId)
            ->whereHas('product', fn ($q) => $q->where('is_active', true)->where('track_stock', true))
            ->when($request->search, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$request->search}%")))
            ->orderByRaw('qty <= min_qty DESC')
            ->paginate(30)->withQueryString();

        return view('app.inventory.index', compact('inventories', 'branches', 'branchId'));
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'type'  => 'required|in:add,subtract,set,available,unavailable,delete',
            'qty'   => 'required_if:type,add,subtract,set|nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($data['type'] === 'delete') {
            $inventory->delete();
            return back()->with('success', 'Inventori dihapus.');
        }

        if (in_array($data['type'], ['available', 'unavailable'])) {
            $inventory->update(['is_available' => $data['type'] === 'available']);
            return back()->with('success', 'Status ketersediaan diperbarui.');
        }

        $qtyBefore = $inventory->qty;

        $qtyChange = match ($data['type']) {
            'add'      => $data['qty'],
            'subtract' => -$data['qty'],
            'set'      => $data['qty'] - $qtyBefore,
        };

        $qtyAfter = max(0, $qtyBefore + $qtyChange);
        $inventory->update(['qty' => $qtyAfter]);

        InventoryLog::create([
            'product_id'  => $inventory->product_id,
            'branch_id'   => $inventory->branch_id,
            'user_id'     => auth()->id(),
            'type'        => 'adjustment',
            'qty_before'  => $qtyBefore,
            'qty_change'  => $qtyAfter - $qtyBefore,
            'qty_after'   => $qtyAfter,
            'notes'       => $data['notes'],
        ]);

        return back()->with('success', 'Stok berhasil disesuaikan.');
    }
}
