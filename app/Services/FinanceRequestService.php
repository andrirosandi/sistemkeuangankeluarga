<?php

namespace App\Services;

use App\Models\RequestHeader;
use App\Models\RequestDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use App\Models\TemporaryMedia;
use Illuminate\Support\Facades\DB;

class FinanceRequestService
{
    /**
     * Buat pengajuan baru (header + details + media).
     *
     * @return RequestHeader
     */
    public function createRequest(array $headerData, array $items, ?array $mediaIds = null, int $userId = 0): RequestHeader
    {
        return DB::transaction(function () use ($headerData, $items, $mediaIds, $userId) {
            $totalAmount = collect($items)->sum('amount');

            $header = RequestHeader::create(array_merge($headerData, [
                'amount' => $totalAmount,
            ]));

            foreach ($items as $item) {
                RequestDetail::create([
                    'header_id'   => $header->id,
                    'description' => $item['description'],
                    'amount'      => $item['amount'],
                ]);
            }

            $this->attachMedia($header, $mediaIds, $userId);

            // Notifikasi approver jika langsung diajukan
            if ($header->status === 'requested') {
                $type = $header->trans_code == 1 ? 'in' : 'out';
                NotificationService::notifyApprovers($header, $type);
            }

            return $header;
        });
    }

    /**
     * Update pengajuan draft (header + recreate details + media).
     *
     * @return RequestHeader
     */
    public function updateRequest(RequestHeader $req, array $headerData, array $items, ?array $mediaIds = null, int $userId = 0): RequestHeader
    {
        return DB::transaction(function () use ($req, $headerData, $items, $mediaIds, $userId) {
            $totalAmount = collect($items)->sum('amount');

            $req->update(array_merge($headerData, [
                'amount' => $totalAmount,
            ]));

            // Hapus details lama, buat ulang
            $req->details()->delete();

            foreach ($items as $item) {
                RequestDetail::create([
                    'header_id'   => $req->id,
                    'description' => $item['description'],
                    'amount'      => $item['amount'],
                ]);
            }

            $this->attachMedia($req, $mediaIds, $userId);

            // Notifikasi approver jika langsung diajukan
            if ($req->status === 'requested') {
                $type = $req->trans_code == 1 ? 'in' : 'out';
                NotificationService::notifyApprovers($req, $type);
            }

            return $req->fresh();
        });
    }

    /**
     * Approve pengajuan: ubah status, auto-buat draft realisasi.
     *
     * @return TransactionHeader  Draft realisasi yang dibuat otomatis
     */
    public function approveRequest(RequestHeader $req, int $approverId): TransactionHeader
    {
        return DB::transaction(function () use ($req, $approverId) {
            // 1. Update request status
            $req->update([
                'status'      => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // 2. Auto-create draft transaction
            $transaction = TransactionHeader::create([
                'category_id'      => $req->category_id,
                'description'      => $req->description,
                'notes'            => $req->notes,
                'amount'           => $req->amount,
                'request_id'       => $req->id,
                'trans_code'       => $req->trans_code,
                'transaction_date' => $req->request_date,
                'created_by'       => $approverId,
                'status'           => 'draft',
            ]);

            // 3. Copy request details → transaction details
            foreach ($req->details as $detail) {
                TransactionDetail::create([
                    'header_id'         => $transaction->id,
                    'description'       => $detail->description,
                    'amount'            => $detail->amount,
                    'request_detail_id' => $detail->id,
                ]);

                $detail->update(['status' => 'pending']);
            }

            // 4. Notifikasi ke pembuat pengajuan
            $approverName = auth()->user()->name ?? 'Sistem';
            NotificationService::notifyUser(
                $req->created_by,
                'Pengajuan <strong>' . htmlspecialchars($req->description) . '</strong> telah <span class="text-success">disetujui</span> oleh ' . $approverName . '.'
            );

            return $transaction;
        });
    }

    /**
     * Reject pengajuan dengan alasan.
     */
    public function rejectRequest(RequestHeader $req, int $approverId, string $reason): void
    {
        DB::transaction(function () use ($req, $approverId, $reason) {
            $req->update([
                'status'           => 'rejected',
                'approved_by'      => $approverId,
                'approved_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            $approverName = auth()->user()->name ?? 'Sistem';
            NotificationService::notifyUser(
                $req->created_by,
                'Pengajuan <strong>' . htmlspecialchars($req->description) . '</strong> telah <span class="text-danger">ditolak</span> oleh ' . $approverName . '. Alasan: ' . htmlspecialchars($reason)
            );
        });
    }

    /**
     * Submit pengajuan draft → requested.
     */
    public function submitRequest(RequestHeader $req): void
    {
        DB::transaction(function () use ($req) {
            $req->update(['status' => 'requested']);

            $type = $req->trans_code == 1 ? 'in' : 'out';
            NotificationService::notifyApprovers($req, $type);
        });
    }

    /**
     * Cancel pengajuan draft -> canceled.
     */
    public function cancelRequest(RequestHeader $req): void
    {
        DB::transaction(function () use ($req) {
            $req->update(['status' => 'canceled']);
        });
    }

    /**
     * Attach temporary media ke pengajuan.
     */
    private function attachMedia(RequestHeader $header, ?array $mediaIds, int $userId): void
    {
        if (!$mediaIds) return;

        $tempMedia = TemporaryMedia::whereIn('id', $mediaIds)
            ->where('user_id', $userId)
            ->get();

        foreach ($tempMedia as $temp) {
            $mediaItems = $temp->getMedia('temp');
            foreach ($mediaItems as $media) {
                $media->move($header, 'requests');
            }
            $temp->delete();
        }
    }
}
