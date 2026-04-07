<?php

namespace App\Services;

use App\Models\RequestHeader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OutstandingService
{
    /**
     * Query inti: ambil outstanding per detail item.
     * Menggabungkan request_detail dengan total realisasi dari transaction_detail (completed).
     *
     * @return Collection  Setiap row: r_id, rd_id, rd_amount, total_realized, remaining_amount, closed
     */
    public function getOutstandingDetails(Collection $userIds): Collection
    {
        return DB::table('request_header as a')
            ->join('request_detail as b', 'a.id', '=', 'b.header_id')
            ->leftJoinSub(
                DB::table('transaction_header as th')
                    ->join('transaction_detail as td', 'th.id', '=', 'td.header_id')
                    ->where('th.status', 'completed')
                    ->select('th.request_id as r_id', 'td.request_detail_id as rd_id', DB::raw('SUM(td.amount) as td_amount'))
                    ->groupBy('th.request_id', 'td.request_detail_id'),
                'y',
                function ($join) {
                    $join->on('a.id', '=', 'y.r_id')
                         ->on('b.id', '=', 'y.rd_id');
                }
            )
            ->whereIn('a.created_by', $userIds)
            ->where('a.status', 'approved')
            ->select(
                'a.id as r_id',
                'b.id as rd_id',
                'b.amount as rd_amount',
                DB::raw('COALESCE(y.td_amount, 0) as total_realized'),
                DB::raw("IF(b.status = 'closed', 1, 0) as closed"),
                DB::raw("IF(b.status = 'closed', 0, GREATEST(b.amount - COALESCE(y.td_amount, 0), 0)) as remaining_amount")
            )
            ->get();
    }

    /**
     * Ambil request yang status='requested' (menunggu approval).
     */
    public function getRequestedOutstanding(Collection $userIds): Collection
    {
        return RequestHeader::whereIn('created_by', $userIds)
            ->where('status', 'requested')
            ->get();
    }

    /**
     * Ambil request approved yang belum ada realisasi sama sekali (semua detail masih pending penuh).
     * Dari outstanding details: request yang tidak punya completed transaction.
     */
    public function getApprovedDraftOutstanding(Collection $userIds): Collection
    {
        $details = $this->getOutstandingDetails($userIds);

        // Group by request, filter: semua detail punya remaining = rd_amount (belum ada realisasi)
        $requestIds = $details->groupBy('r_id')
            ->filter(function ($items) {
                return $items->every(fn($d) => $d->remaining_amount == $d->rd_amount && !$d->closed);
            })
            ->keys();

        return RequestHeader::whereIn('id', $requestIds)
            ->where('status', 'approved')
            ->whereDoesntHave('transactions', fn($q) => $q->where('status', 'completed'))
            ->get();
    }

    /**
     * Ambil request approved yang sudah ada realisasi parsial (ada completed transaction, ada detail sisa).
     */
    public function getPartialOutstanding(Collection $userIds): Collection
    {
        $details = $this->getOutstandingDetails($userIds);

        // Group by request, filter: minimal 1 detail punya remaining > 0 DAN ada yang sudah realized
        $requestIds = $details->groupBy('r_id')
            ->filter(function ($items) {
                $hasRemaining = $items->contains(fn($d) => $d->remaining_amount > 0 && !$d->closed);
                $hasRealized = $items->contains(fn($d) => $d->total_realized > 0);
                return $hasRemaining && $hasRealized;
            })
            ->keys();

        return RequestHeader::whereIn('id', $requestIds)
            ->where('status', 'approved')
            ->whereHas('transactions', fn($q) => $q->where('status', 'completed'))
            ->get();
    }

    /**
     * Ambil outstanding per detail untuk satu request tertentu.
     * Menggantikan FinanceRequestService::calculateOutstanding() (fixes N+1).
     *
     * @return Collection  Setiap row: rd_id, description (via rd_id), rd_amount, total_realized, remaining_amount
     */
    public function getRequestOutstanding(int $requestId): Collection
    {
        return DB::table('request_detail as b')
            ->leftJoinSub(
                DB::table('transaction_header as th')
                    ->join('transaction_detail as td', 'th.id', '=', 'td.header_id')
                    ->where('th.status', 'completed')
                    ->where('th.request_id', $requestId)
                    ->select('td.request_detail_id as rd_id', DB::raw('SUM(td.amount) as td_amount'))
                    ->groupBy('td.request_detail_id'),
                'y',
                fn($join) => $join->on('b.id', '=', 'y.rd_id')
            )
            ->where('b.header_id', $requestId)
            ->where('b.status', '!=', 'closed')
            ->select(
                'b.id as rd_id',
                'b.description',
                'b.amount as rd_amount',
                DB::raw('COALESCE(y.td_amount, 0) as total_realized'),
                DB::raw('GREATEST(b.amount - COALESCE(y.td_amount, 0), 0) as remaining_amount')
            )
            ->having('remaining_amount', '>', 0)
            ->get();
    }

    /**
     * Data untuk dashboard widget outstanding: counts, amounts, aging.
     */
    public function getWidgetData(Collection $userIds): array
    {
        $requested = $this->getRequestedOutstanding($userIds);
        $approvedDraft = $this->getApprovedDraftOutstanding($userIds);
        $partial = $this->getPartialOutstanding($userIds);

        $allOutstanding = $requested->merge($approvedDraft)->merge($partial);

        $now = now();
        $aging = ['fresh' => 0, 'medium' => 0, 'old' => 0];
        $agingAmount = ['fresh' => 0, 'medium' => 0, 'old' => 0];

        foreach ($allOutstanding as $item) {
            $days = $now->diffInDays($item->created_at ?? $item->approved_at);
            if ($days <= 3) {
                $aging['fresh']++;
                $agingAmount['fresh'] += $item->amount;
            } elseif ($days <= 7) {
                $aging['medium']++;
                $agingAmount['medium'] += $item->amount;
            } else {
                $aging['old']++;
                $agingAmount['old'] += $item->amount;
            }
        }

        return [
            'totalCount'          => $allOutstanding->count(),
            'totalAmount'         => (float) $allOutstanding->sum('amount'),
            'requestedCount'      => $requested->count(),
            'requestedAmount'     => (float) $requested->sum('amount'),
            'approvedDraftCount'  => $approvedDraft->count(),
            'approvedDraftAmount' => (float) $approvedDraft->sum('amount'),
            'partialCount'        => $partial->count(),
            'partialAmount'       => (float) $partial->sum('amount'),
            'aging'               => $aging,
            'agingAmount'         => $agingAmount,
        ];
    }
}
