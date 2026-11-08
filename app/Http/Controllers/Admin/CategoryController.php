<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('items')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori disimpan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->items()->count() > 0) {
            return back()->with('galat', 'Kategori ini masih dipakai '.$category->items()->count().' laporan, jadi belum bisa dihapus. Pindahkan laporannya ke kategori lain dulu.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('sukses', 'Kategori dihapus.');
    }
}