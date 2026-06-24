<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use Illuminate\Http\Request;

class ModifierGroupController extends Controller
{
    public function index()
    {
        $groups = ModifierGroup::with('options')->get();
        return view('app.modifiers.index', compact('groups'));
    }

    public function create()
    {
        return view('app.modifiers.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'is_required' => 'boolean',
            'is_multiple' => 'boolean',
            'min_select'  => 'nullable|integer|min:0',
            'max_select'  => 'nullable|integer|min:1',
            'options'          => 'nullable|array',
            'options.*.name'   => 'required|string|max:100',
            'options.*.price'  => 'nullable|numeric|min:0',
        ]);

        $group = ModifierGroup::create([
            'name'        => $data['name'],
            'is_required' => $request->boolean('is_required'),
            'is_multiple' => $request->boolean('is_multiple'),
            'min_select'  => $data['min_select'] ?? 0,
            'max_select'  => $data['max_select'] ?? 1,
        ]);

        foreach ($request->input('options', []) as $opt) {
            if (! empty($opt['name'])) {
                $group->options()->create([
                    'name'      => $opt['name'],
                    'price'     => $opt['price'] ?? 0,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('app.modifiers.index')->with('success', 'Grup modifier berhasil ditambahkan.');
    }

    public function edit(ModifierGroup $modifier)
    {
        $modifier->load('options');
        return view('app.modifiers.form', compact('modifier'));
    }

    public function update(Request $request, ModifierGroup $modifier)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'is_required' => 'boolean',
            'is_multiple' => 'boolean',
            'min_select'  => 'nullable|integer|min:0',
            'max_select'  => 'nullable|integer|min:1',
        ]);

        $modifier->update([
            'name'        => $data['name'],
            'is_required' => $request->boolean('is_required'),
            'is_multiple' => $request->boolean('is_multiple'),
            'min_select'  => $data['min_select'] ?? 0,
            'max_select'  => $data['max_select'] ?? 1,
        ]);

        return redirect()->route('app.modifiers.index')->with('success', 'Grup modifier diperbarui.');
    }

    public function destroy(ModifierGroup $modifier)
    {
        $modifier->delete();
        return redirect()->route('app.modifiers.index')->with('success', 'Grup modifier dihapus.');
    }

    public function storeOption(Request $request, ModifierGroup $modifier)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);
        $opt = $modifier->options()->create(['is_active' => true] + $data);
        return response()->json($opt);
    }

    public function destroyOption(ModifierGroup $modifier, ModifierOption $option)
    {
        $option->delete();
        return response()->json(['ok' => true]);
    }
}
