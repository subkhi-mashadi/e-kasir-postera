<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->get();

        return view('auth.branch-select', compact('branches'));
    }

    public function select(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);

        $branch = Branch::where('id', $request->branch_id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        session(['branch_id' => $branch->id]);

        return redirect()->intended(route('app.dashboard'));
    }

    public function changeBranch(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);

        $branch = Branch::where('id', $request->branch_id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        session(['branch_id' => $branch->id]);

        return back()->with('success', 'Cabang berhasil diganti.');
    }
}
