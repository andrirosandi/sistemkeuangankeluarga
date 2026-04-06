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
        $highlightRequestId = $request->query('request_id');

        // 1. Menunggu Approval — request.status = 'requested'
        $requestedQuery = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'requested');

        $requested = $requestedQuery
            ->orderByRaw("FIELD(priority, 'high', 'normal', 'low'), created_at ASC")
            ->get();

        // 2. Approved, Belum Cair — request.status = 'approved' + BELUM ADA transaction yg completed
        $approvedDraftQuery = RequestHeader::with(['category', 'creator', 'approver', 'transactions'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'approved')
            ->whereDoesntHave('transactions', function ($q) {
                $q->where('status', 'completed');
            })
            ->whereHas('details', function ($dq) {
                $dq->where('status', 'pending');
            });

        $approvedDraft = $approvedDraftQuery
            ->orderBy('approved_at', 'asc')
            ->get();

        // 3. Realisasi Parsial — approved + transaction completed + masih ada detail pending
        //    Detail status adalah sumber kebenaran:
        //      pending  = belum cair (masih outstanding)
        //      realized = sudah cair (selesai)
        //      closed   = di-write-off (selesai)
        $partialQuery = RequestHeader::with(['category', 'creator', 'approver', 'transactions', 'details'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'approved')
            ->whereHas('transactions', function ($q) {
                $q->where('status', 'completed');
            })
            ->whereHas('details', function ($dq) {
                $dq->where('status', 'pending');
            });

        $partial = $partialQuery
            ->orderBy('approved_at', 'asc')
            ->get();

        return view('transaction.outstanding.index', compact(
            'requested',
            'approvedDraft',
            'partial',
            'highlightRequestId'
        ));
    }
}
