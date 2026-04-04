<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use App\Models\RequestHeader;
use App\Models\RequestDetail;
use App\Models\Category;
use App\Models\Balance;
use App\Models\RoleVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private function getTransCode($type) {
        return $type === 'out' ? 2 : 1;
    }

    private function getTypeLabel($type) {
        return $type === 'out' ? 'Kas Keluar' : 'Kas Masuk';
    }

    protected function notifyUser($userId, $message)
    {
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Update balance table for a given month.
     */
    protected function updateBalance($transactionDate, $transCode, $amount, $operation = 'add')
    {
        $month = \Carbon\Carbon::parse($transactionDate)->format('Y-m');

        $balance = Balance::firstOrCreate(
            ['month' => $month],
            ['begin' => 0, 'total_in' => 0, 'total_out' => 0, 'ending' => 0]
        );

        if ($operation === 'add') {
            if ($transCode == 1) {
                $balance->total_in += $amount;
            } else {
                $balance->total_out += $amount;
            }
        } else {
            // reverse (for cancel completed)
            if ($transCode == 1) {
                $balance->total_in -= $amount;
            } else {
                $balance->total_out -= $amount;
            }
        }

        $balance->ending = $balance->begin + $balance->total_in - $balance->total_out;
        $balance->save();
    }

    public function create(Request $request, $type)
    {
        $title = "Buat Realisasi " . $this->getTypeLabel($type);
        $categories = \App\Models\Category::orderBy('name')->get();
        $templateData = null;

        if ($request->has('template_id')) {
            $templateData = \App\Models\TemplateHeader::with('details')->find($request->template_id);
        }

        return view('transaction.realisasi.form', compact('title', 'type', 'categories', 'templateData'));
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $transCode = $this->getTransCode($type);
            $totalAmount = collect($request->items)->sum('amount');
            $status = $request->input('action_type', 'draft') === 'completed' ? 'completed' : 'draft';

            $header = TransactionHeader::create([
                'category_id' => $request->category_id,
                'transaction_date' => $request->transaction_date,
                'trans_code' => $transCode,
                'description' => $request->description,
                'notes' => $request->notes,
                'amount' => $totalAmount,
                'status' => $status,
                'created_by' => auth()->id(),
                'request_id' => null,
            ]);

            foreach ($request->items as $item) {
                TransactionDetail::create([
                    'header_id' => $header->id,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            if ($status === 'completed') {
                $this->updateBalance($header->transaction_date, $header->trans_code, $header->amount, 'add');
                $msg = 'Realisasi berhasil disimpan dan dana dicairkan/dimasukkan.';
            } else {
                $msg = 'Realisasi berhasil disimpan sebagai Draft.';
            }

            DB::commit();
            return redirect()->route("{$type}.transaction.index")->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan realisasi: ' . $e->getMessage());
        }
    }

    public function edit($type, $id)
    {
        $transaction = TransactionHeader::with(['details'])->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi Draft yang dapat diedit.');
        }

        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($transaction->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        $title = "Edit Realisasi " . $this->getTypeLabel($type);
        $categories = Category::orderBy('name')->get();
        // Gunakan variable yang sama dengan create view
        $transactionData = $transaction;

        return view('transaction.realisasi.form', compact('title', 'type', 'categories', 'transactionData'));
    }

    public function update(Request $request, $type, $id)
    {
        $transaction = TransactionHeader::findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi Draft yang dapat diedit.');
        }

        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($transaction->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = collect($request->items)->sum('amount');
            $status = $request->input('action_type', 'draft') === 'completed' ? 'completed' : 'draft';

            $transaction->update([
                'category_id' => $request->category_id,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'notes' => $request->notes,
                'amount' => $totalAmount,
                'status' => $status,
            ]);

            // Untuk mencegah hilangnya relasi request_detail_id, kita update jika id cocok, atau delete+recreate
            // Pendekatan sederhana: hapus yang lama (yang tidak ada di request) lalu buat/update
            $existingItemIds = [];
            foreach ($request->items as $item) {
                if (isset($item['id']) && $item['id']) {
                    TransactionDetail::where('id', $item['id'])->where('header_id', $transaction->id)->update([
                        'description' => $item['description'],
                        'amount' => $item['amount']
                    ]);
                    $existingItemIds[] = $item['id'];
                } else {
                    $newDetail = TransactionDetail::create([
                        'header_id' => $transaction->id,
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);
                    $existingItemIds[] = $newDetail->id;
                }
            }
            
            // Hapus detail yang di-remove dari UI
            TransactionDetail::where('header_id', $transaction->id)->whereNotIn('id', $existingItemIds)->delete();

            if ($status === 'completed') {
                $this->updateBalance($transaction->transaction_date, $transaction->trans_code, $transaction->amount, 'add');
                $msg = 'Realisasi berhasil diupdate dan dana dicairkan/dimasukkan.';
            } else {
                $msg = 'Realisasi berhasil diupdate.';
            }

            DB::commit();
            return redirect()->route("{$type}.transaction.index")->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate realisasi: ' . $e->getMessage());
        }
    }

    /**
     * Display list of realizations (transactions).
     */
    public function index(Request $request, $type)
    {
        $transCode = $this->getTransCode($type);
        $title = "Realisasi " . $this->getTypeLabel($type);

        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        $query = TransactionHeader::with(['creator', 'category', 'requestHeader'])
            ->where('trans_code', $transCode)
            ->whereIn('created_by', $visibleUserIds);

        // Filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        $query->orderByRaw("FIELD(status, 'draft', 'completed', 'canceled'), created_at DESC");

        $transactions = $query->get();
        
        $templates = \App\Models\TemplateHeader::where('trans_code', $transCode)->orderBy('description')->get();

        return view('transaction.realisasi.index', compact('transactions', 'title', 'type', 'templates'));
    }

    /**
     * Show transaction detail.
     */
    public function show($type, $id)
    {
        $transaction = TransactionHeader::with(['details', 'category', 'creator', 'requestHeader.creator'])
            ->findOrFail($id);

        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($transaction->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        $title = "Detail Realisasi " . $this->getTypeLabel($type);
        return view('transaction.realisasi.show', compact('transaction', 'title', 'type'));
    }

    /**
     * Complete a draft transaction (disburse funds).
     */
    public function complete(Request $request, $type, $id)
    {
        $transaction = TransactionHeader::with('details')->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Draft yang dapat dicairkan.');
        }

        try {
            DB::beginTransaction();

            $transaction->update(['status' => 'completed']);

            // Update request_detail status to 'realized'
            if ($transaction->request_id) {
                TransactionDetail::where('header_id', $transaction->id)
                    ->whereNotNull('request_detail_id')
                    ->get()
                    ->each(function ($td) {
                        RequestDetail::where('id', $td->request_detail_id)
                            ->update(['status' => 'realized']);
                    });
            }

            // Update balance
            $this->updateBalance(
                $transaction->transaction_date,
                $transaction->trans_code,
                $transaction->amount,
                'add'
            );

            // Notify requester if from a request
            if ($transaction->request_id) {
                $reqHeader = RequestHeader::find($transaction->request_id);
                if ($reqHeader) {
                    $this->notifyUser(
                        $reqHeader->created_by,
                        'Dana dari pengajuan <strong>' . htmlspecialchars($reqHeader->description) . '</strong> telah <span class="text-success">dicairkan</span>. Nominal: Rp ' . number_format($transaction->amount, 0, ',', '.')
                    );
                }
            }

            DB::commit();
            return redirect()->route("{$type}.transaction.index")->with('success', 'Dana berhasil dicairkan dan saldo telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencairkan dana: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a completed transaction (revert to draft).
     */
    public function cancel(Request $request, $type, $id)
    {
        $transaction = TransactionHeader::findOrFail($id);

        if ($transaction->status !== 'completed') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Completed yang dapat dibatalkan (dikembalikan ke Draft).');
        }

        try {
            DB::beginTransaction();

            $transaction->update(['status' => 'draft']);

            // Revert request details to 'pending'
            if ($transaction->request_id) {
                TransactionDetail::where('header_id', $transaction->id)
                    ->whereNotNull('request_detail_id')
                    ->get()
                    ->each(function ($td) {
                        RequestDetail::where('id', $td->request_detail_id)
                            ->update(['status' => 'pending']);
                    });
            }

            // Reverse balance
            $this->updateBalance(
                $transaction->transaction_date,
                $transaction->trans_code,
                $transaction->amount,
                'remove'
            );

            // Notify requester if from a request
            if ($transaction->request_id) {
                $reqHeader = RequestHeader::find($transaction->request_id);
                if ($reqHeader) {
                    $this->notifyUser(
                        $reqHeader->created_by,
                        'Pencairan dana untuk pengajuan <strong>' . htmlspecialchars($reqHeader->description) . '</strong> telah <span class="text-warning">dibatalkan</span>.'
                    );
                }
            }

            DB::commit();
            return redirect()->route("{$type}.transaction.index")->with('success', 'Pencairan dibatalkan. Realisasi kembali menjadi Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan realisasi: ' . $e->getMessage());
        }
    }

    /**
     * Delete a draft transaction. Request goes back to 'requested'.
     */
    public function destroy($type, $id)
    {
        $transaction = TransactionHeader::findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Draft yang dapat dihapus.');
        }

        try {
            DB::beginTransaction();

            // Revert request to 'requested' so it can be re-approved
            if ($transaction->request_id) {
                RequestHeader::where('id', $transaction->request_id)
                    ->update([
                        'status' => 'requested',
                        'approved_by' => null,
                        'approved_at' => null,
                    ]);

                // Reset request detail status
                TransactionDetail::where('header_id', $transaction->id)
                    ->whereNotNull('request_detail_id')
                    ->get()
                    ->each(function ($td) {
                        RequestDetail::where('id', $td->request_detail_id)
                            ->update(['status' => null]);
                    });
            }

            // Delete transaction details first, then header
            $transaction->details()->delete();
            $transaction->delete();

            DB::commit();
            return redirect()->route("{$type}.transaction.index")->with('success', 'Realisasi dihapus. Pengajuan dikembalikan ke status Requested.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus realisasi: ' . $e->getMessage());
        }
    }
}
