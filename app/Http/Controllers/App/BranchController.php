<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users', 'tables'])->get();
        return view('app.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('app.branches.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'nullable|string|max:20',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['company_id'] = auth()->user()->company_id;
        $data['is_active']  = $request->boolean('is_active', true);
        Branch::create($data);
        return redirect()->route('app.branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        return view('app.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'nullable|string|max:20',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $branch->update($data);
        return redirect()->route('app.branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('app.branches.index')->with('success', 'Cabang dihapus.');
    }
}
