<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->branch_id ?? session('branch_id') ?? auth()->user()->branch_id;
        $branches = Branch::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $tables   = Table::where('branch_id', $branchId)->orderBy('name')->get();

        return view('app.tables.index', compact('tables', 'branches', 'branchId'));
    }

    public function create(Request $request)
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        $defaultBranch = $request->branch_id ?? session('branch_id') ?? auth()->user()->branch_id;
        return view('app.tables.form', compact('branches', 'defaultBranch'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:50',
            'capacity'  => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['qr_token']  = Str::random(32);

        Table::create($data);
        return redirect()->route('app.tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table)
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->where('is_active', true)->get();
        return view('app.tables.form', compact('table', 'branches'));
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:50',
            'capacity'  => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $table->update($data);
        return redirect()->route('app.tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->route('app.tables.index')->with('success', 'Meja dihapus.');
    }

    public function regenerateQr(Table $table)
    {
        $table->update(['qr_token' => Str::random(32)]);
        return back()->with('success', 'QR code berhasil di-regenerate.');
    }

    public function qr(Table $table)
    {
        URL::forceRootUrl(config('app.url'));
        $url = route('order.show', $table->qr_token);
        URL::forceRootUrl(null); // reset so Blade view route() uses request URL
        $svg = QrCode::format('svg')->size(300)->errorCorrection('M')->generate($url);
        return view('app.tables.qr', compact('table', 'svg', 'url'));
    }
}
