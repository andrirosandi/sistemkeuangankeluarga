<?php

namespace App\Services;

use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use App\Models\RequestHeader;
use App\Models\RequestDetail;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Buat realisasi baru (header + details).
     *
     * @return TransactionHeader
     */
    public function createTransaction(array $headerData, array $items, string $status = 'draft'): TransactionHeader
    {
        return DB::transaction(function () use ($headerData, $items, $status) {
            $totalAmount = collect($items)->sum('amount');

            $header = TransactionHeader::create(array_merge($headerData, [
                'amount' => $totalAmount,
                'status' => $status,
            ]));

            foreach ($items as $item) {
                TransactionDetail::create([
                    'header_id'   => $header->id,
                    'description' => $item['description'],
                    'amount'      => $item['amount'],
                ]);
            }

            if ($status === 'completed') {
                $this->balanceService->updateBalance(
                    $header->transaction_date,
                    $header->trans_code,
                    $header->amount,
                    'add'
                );
            }

            return $header;
        });
    }

    /**
     * Update realisasi yang berstatus draft (header + upsert details).
     *
     * @return TransactionHeader
     */
    public function updateTransaction(TransactionHeader $transaction, array $headerData, array $items, string $status = 'draft'): TransactionHeader
    {
        return DB::transaction(function () use ($transaction, $headerData, $items, $status) {
            $totalAmount = collect($items)->sum('amount');

            $transaction->update(array_merge($headerData, [
                'amount' => $totalAmount,
                'status' => $status,
            ]));

            // Upsert details: update existing, create new, delete removed
            $existingItemIds = [];
            foreach ($items as $item) {
                if (isset($item['id']) && $item['id']) {
                    TransactionDetail::where('id', $item['id'])
                        ->where('header_id', $transaction->id)
                        ->update([
                            'description' => $item['description'],
                            'amount'      => $item['amount'],
                        ]);
                    $existingItemIds[] = $item['id'];
                } else {
                    $newDetail = TransactionDetail::create([
                        'header_id'   => $transaction->id,
                        'description' => $item['description'],
                        'amount'      => $item['amount'],
                    ]);
                    $existingItemIds[] = $newDetail->id;
                }
            }

            // Hapus detail yang sudah di-remove dari UI
            TransactionDetail::where('header_id', $transaction->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            if ($status === 'completed') {
                $this->balanceService->updateBalance(
                    $transaction->transaction_date,
                    $transaction->trans_code,
                    $transaction->amount,
                    'add'
                );
            }

            return $transaction->fresh();
        });
    }

    /**
     * Cairkan dana: ubah status draft → completed, update saldo & request details.
     */
    public function completeTransaction(TransactionHeader $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'completed']);

            // Update request_detail status ke 'realized'
            if ($transaction->request_id) {
                TransactionDetail::where('header_id', $transaction->id)
                    ->whereNotNull('request_detail_id')
                    ->get()
                    ->each(function ($td) {
                        RequestDetail::where('id', $td->request_detail_id)
                            ->update(['status' => 'realized']);
                    });
            }

            // Update saldo
            $this->balanceService->updateBalance(
                $transaction->transaction_date,
                $transaction->trans_code,
                $transaction->amount,
                'add'
            );

            // Notifikasi ke pembuat pengajuan
            if ($transaction->request_id) {
                $reqHeader = RequestHeader::find($transaction->request_id);
                if ($reqHeader) {
                    $type = $reqHeader->trans_code == 1 ? 'in' : 'out';
                    NotificationService::notifyUser(
                        $reqHeader->created_by,
                        'Dana dari pengajuan <strong>' . htmlspecialchars($reqHeader->description) . '</strong> telah <span class="text-success">dicairkan</span>. Nominal: Rp ' . number_format($transaction->amount, 0, ',', '.'),
                        "{$type}.request.show",
                        ['id' => $reqHeader->id]
                    );
                }
            }
        });
    }

    /**
     * Batalkan pencairan: completed → draft, revert saldo & request details.
     */
    public function cancelTransaction(TransactionHeader $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'draft']);

            // Revert request details ke 'pending'
            if ($transaction->request_id) {
                TransactionDetail::where('header_id', $transaction->id)
                    ->whereNotNull('request_detail_id')
                    ->get()
                    ->each(function ($td) {
                        RequestDetail::where('id', $td->request_detail_id)
                            ->update(['status' => 'pending']);
                    });
            }

            // Revert saldo
            $this->balanceService->updateBalance(
                $transaction->transaction_date,
                $transaction->trans_code,
                $transaction->amount,
                'remove'
            );

            // Notifikasi ke pembuat pengajuan
            if ($transaction->request_id) {
                $reqHeader = RequestHeader::find($transaction->request_id);
                if ($reqHeader) {
                    $type = $reqHeader->trans_code == 1 ? 'in' : 'out';
                    NotificationService::notifyUser(
                        $reqHeader->created_by,
                        'Pencairan dana untuk pengajuan <strong>' . htmlspecialchars($reqHeader->description) . '</strong> telah <span class="text-warning">dibatalkan</span>.',
                        "{$type}.request.show",
                        ['id' => $reqHeader->id]
                    );
                }
            }
        });
    }

    /**
     * Hapus realisasi draft. Jika berasal dari request, kembalikan status request ke 'requested'.
     */
    public function deleteTransaction(TransactionHeader $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Kembalikan request ke 'requested' agar bisa di-approve ulang
            if ($transaction->request_id) {
                RequestHeader::where('id', $transaction->request_id)
                    ->update([
                        'status'      => 'requested',
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

            $transaction->details()->delete();
            $transaction->delete();
        });
    }
}
