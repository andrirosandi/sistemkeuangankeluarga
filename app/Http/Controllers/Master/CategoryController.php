<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return view('master.category.index', compact('categories'));
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Update kategori.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'Nama kategori berhasil diubah.');
    }

    /**
     * Hapus kategori.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus! Kategori ini mungkin sudah digunakan di data transaksi.');
        }
    }
}
