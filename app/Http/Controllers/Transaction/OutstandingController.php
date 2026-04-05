<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\RequestHeader;
use App\Models\RoleVisibility;
use Illuminate\Http\Request;

class OutstandingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        // 1. Menunggu Approval — request.status = 'requested'
        $requested = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'requested')
            ->orderByRaw("FIELD(priority, 'high', 'normal', 'low'), created_at ASC")
            ->get();

        // 2. Approved, Belum Cair — request.status = 'approved' + transaction.status = 'draft'
        $approvedDraft = RequestHeader::with(['category', 'creator', 'approver', 'transaction'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'approved')
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'draft');
            })
            ->orderBy('approved_at', 'asc')
            ->get();

        // 3. Realisasi Parsial — approved + transaction completed tapi tidak full
        //    Cek: ada request_detail.status = 'pending' ATAU transaction.amount < request.amount
        $partial = RequestHeader::with(['category', 'creator', 'approver', 'transaction', 'details'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'approved')
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'completed');
            })
            ->where(function ($q) {
                // Item belum semua ter-realize
                $q->whereHas('details', function ($dq) {
                    $dq->where('status', 'pending');
                })
                // ATAU nominal realisasi kurang dari nominal request
                ->orWhereHas('transaction', function ($tq) {
                    $tq->where('status', 'completed')
                       ->whereColumn('amount', '<', 'request_header.amount');
                });
            })
            ->orderBy('approved_at', 'asc')
            ->get();

        return view('transaction.outstanding.index', compact(
            'requested',
            'approvedDraft',
            'partial'
        ));
    }
}
