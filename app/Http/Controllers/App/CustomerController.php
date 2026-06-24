<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('app.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('app.customers.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Customer::create($data);
        return redirect()->route('app.customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('app.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $customer->update($data);
        return redirect()->route('app.customers.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('app.customers.index')->with('success', 'Pelanggan dihapus.');
    }
}
