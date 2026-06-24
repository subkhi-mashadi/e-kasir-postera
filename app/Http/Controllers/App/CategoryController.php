<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('app.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('app.categories.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'nullable|string|max:20',
            'icon'       => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Category::create($data);
        return redirect()->route('app.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('app.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'nullable|string|max:20',
            'icon'       => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $category->update($data);
        return redirect()->route('app.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('app.categories.index')->with('success', 'Kategori dihapus.');
    }
}
