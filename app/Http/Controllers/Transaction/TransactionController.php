<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\TransactionType;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\TransactionHeader;
use App\Models\Category;
use App\Models\RoleVisibility;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use TransactionType;

    public function __construct(
        private TransactionService $transactionService,
    ) {}

    /**
     * Cek apakah user boleh melihat transaksi ini.
     */
    private function isVisibleToUser($transaction, $visibleUserIds): bool
    {
        if ($visibleUserIds->contains($transaction->created_by)) {
            return true;
        }
        if ($transaction->request_id && $transaction->requestHeader) {
            return $visibleUserIds->contains($transaction->requestHeader->created_by);
        }
        return false;
    }

    /**
     * Authorize visibility — abort 403 jika tidak boleh akses.
     */
    private function authorizeVisibility($transaction): void
    {
        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$this->isVisibleToUser($transaction, $visibleUserIds)) {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index(Request $request, $type)
    {
        $transCode = $this->getTransCode($type);
        $title = "Realisasi " . $this->getTypeLabel($type);

        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        $query = TransactionHeader::with(['creator', 'category', 'requestHeader'])
            ->where('trans_code', $transCode)
            ->where(function ($q) use ($visibleUserIds) {
                $q->whereIn('created_by', $visibleUserIds)
                  ->orWhereHas('requestHeader', function ($rq) use ($visibleUserIds) {
                      $rq->whereIn('created_by', $visibleUserIds);
                  });
            });

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        $query->orderByRaw("FIELD(status, 'draft', 'completed'), created_at DESC");

        $transactions = $query->get();
        $templates = \App\Models\TemplateHeader::where('trans_code', $transCode)->orderBy('description')->get();

        return view('transaction.realisasi.index', compact('transactions', 'title', 'type', 'templates'));
    }

    public function create(Request $request, $type)
    {
        $title = "Buat Realisasi " . $this->getTypeLabel($type);
        $categories = Category::orderBy('name')->get();
        $templateData = null;

        if ($request->has('template_id')) {
            $templateData = \App\Models\TemplateHeader::with('details')->find($request->template_id);
        }

        return view('transaction.realisasi.form', compact('title', 'type', 'categories', 'templateData'));
    }

    public function store(StoreTransactionRequest $request, $type)
    {
        try {
            $transCode = $this->getTransCode($type);
            $status = $request->input('action_type', 'draft') === 'completed' ? 'completed' : 'draft';

            $this->transactionService->createTransaction(
                headerData: [
                    'category_id'      => $request->category_id,
                    'transaction_date' => $request->transaction_date,
                    'trans_code'       => $transCode,
                    'description'      => $request->description,
                    'notes'            => $request->notes,
                    'created_by'       => auth()->id(),
                    'request_id'       => null,
                ],
                items: $request->items,
                status: $status,
            );

            $msg = $status === 'completed'
                ? 'Realisasi berhasil disimpan dan dana dicairkan/dimasukkan.'
                : 'Realisasi berhasil disimpan sebagai Draft.';

            return redirect()->route("{$type}.transaction.index")->with('success', $msg);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan realisasi. Silakan coba lagi.');
        }
    }

    public function show($id, $type)
    {
        $transaction = TransactionHeader::with(['details', 'category', 'creator', 'requestHeader.creator'])
            ->findOrFail($id);

        $this->authorizeVisibility($transaction);

        $title = "Detail Realisasi " . $this->getTypeLabel($type);
        return view('transaction.realisasi.show', compact('transaction', 'title', 'type'));
    }

    public function edit($id, $type)
    {
        $transaction = TransactionHeader::with(['details', 'requestHeader'])->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi Draft yang dapat diedit.');
        }

        $this->authorizeVisibility($transaction);

        $title = "Edit Realisasi " . $this->getTypeLabel($type);
        $categories = Category::orderBy('name')->get();
        $transactionData = $transaction;

        return view('transaction.realisasi.form', compact('title', 'type', 'categories', 'transactionData'));
    }

    public function update(StoreTransactionRequest $request, $id, $type)
    {
        $transaction = TransactionHeader::with('requestHeader')->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi Draft yang dapat diedit.');
        }

        $this->authorizeVisibility($transaction);

        try {
            $status = $request->input('action_type', 'draft') === 'completed' ? 'completed' : 'draft';

            $this->transactionService->updateTransaction(
                transaction: $transaction,
                headerData: [
                    'category_id'      => $request->category_id,
                    'transaction_date' => $request->transaction_date,
                    'description'      => $request->description,
                    'notes'            => $request->notes,
                ],
                items: $request->items,
                status: $status,
            );

            $msg = $status === 'completed'
                ? 'Realisasi berhasil diupdate dan dana dicairkan/dimasukkan.'
                : 'Realisasi berhasil diupdate.';

            return redirect()->route("{$type}.transaction.index")->with('success', $msg);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate realisasi. Silakan coba lagi.');
        }
    }

    public function complete(Request $request, $id, $type)
    {
        $transaction = TransactionHeader::with(['details', 'requestHeader'])->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Draft yang dapat dicairkan.');
        }

        $this->authorizeVisibility($transaction);

        try {
            $this->transactionService->completeTransaction($transaction);

            return redirect()->route("{$type}.transaction.index")->with('success', 'Dana berhasil dicairkan dan saldo telah diperbarui.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal mencairkan dana. Silakan coba lagi.');
        }
    }

    public function cancel(Request $request, $id, $type)
    {
        $transaction = TransactionHeader::with('requestHeader')->findOrFail($id);

        if ($transaction->status !== 'completed') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Completed yang dapat dibatalkan.');
        }

        $this->authorizeVisibility($transaction);

        try {
            $this->transactionService->cancelTransaction($transaction);

            return redirect()->route("{$type}.transaction.index")->with('success', 'Pencairan dibatalkan. Realisasi kembali menjadi Draft.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal membatalkan realisasi. Silakan coba lagi.');
        }
    }

    public function destroy($id, $type)
    {
        $transaction = TransactionHeader::with('requestHeader')->findOrFail($id);

        if ($transaction->status !== 'draft') {
            return redirect()->route("{$type}.transaction.index")->with('error', 'Hanya realisasi berstatus Draft yang dapat dihapus.');
        }

        $this->authorizeVisibility($transaction);

        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()->route("{$type}.transaction.index")->with('success', 'Realisasi dihapus. Pengajuan dikembalikan ke status Requested.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menghapus realisasi. Silakan coba lagi.');
        }
    }
}
