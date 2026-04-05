<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BulkDeletable;
use App\Http\Requests\Master\StoreCategoryRequest;
use App\Http\Requests\Master\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    use BulkDeletable;

    protected function bulkDeleteConfig(): array
    {
        return [
            'model' => Category::class,
            'table' => 'categories',
            'label' => 'kategori',
        ];
    }
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
    public function store(StoreCategoryRequest $request)
    {

        try {
            Category::create([
                'name' => $request->name,
                'color' => $request->color ?? '#616876'
            ]);

            return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal menambahkan kategori! Silakan coba lagi.',
                'modal' => 'add'
            ]);
        }
    }

    /**
     * Update kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {

        try {
            $category->update([
                'name' => $request->name,
                'color' => $request->color ?? $category->color
            ]);

            return redirect()->back()->with('success', 'Kategori berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal mengubah kategori! Pastikan nama belum digunakan.',
                'modal' => 'edit'
            ]);
        }
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
