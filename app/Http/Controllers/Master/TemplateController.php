<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TemplateHeader;
use App\Models\TemplateDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = TemplateHeader::with(['category', 'creator'])
            ->latest('id');

        // Filter by Search
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Filter by Type
        if ($request->type) {
            $query->where('trans_code', $request->type);
        }

        $templates = $query->paginate(10)->withQueryString();

        return view('master.template.index', compact('templates'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('master.template.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'trans_code' => 'required|in:1,2',
            'details' => 'required|array|min:1',
            'details.*.description' => 'required|string|max:255',
            'details.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = collect($request->details)->sum('amount');

            $header = TemplateHeader::create([
                'category_id' => $request->category_id,
                'trans_code' => $request->trans_code,
                'description' => $request->description,
                'amount' => $totalAmount,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->details as $item) {
                $header->details()->create([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            DB::commit();
            return redirect()->route('master.templates.index')->with('success', 'Template berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(TemplateHeader $template)
    {
        $template->load('details');
        $categories = Category::orderBy('name')->get();
        return view('master.template.edit', compact('template', 'categories'));
    }

    public function update(Request $request, TemplateHeader $template)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'trans_code' => 'required|in:1,2',
            'details' => 'required|array|min:1',
            'details.*.description' => 'required|string|max:255',
            'details.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = collect($request->details)->sum('amount');

            $template->update([
                'category_id' => $request->category_id,
                'trans_code' => $request->trans_code,
                'description' => $request->description,
                'amount' => $totalAmount,
            ]);

            // Simple approach: Delete existing details and recreate
            $template->details()->delete();
            foreach ($request->details as $item) {
                $template->details()->create([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            DB::commit();
            return redirect()->route('master.templates.index')->with('success', 'Template berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui template: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(TemplateHeader $template)
    {
        try {
            $template->delete();
            return redirect()->route('master.templates.index')->with('success', 'Template berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }
}
