<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\RoleVisibility;
use App\Services\OutstandingService;
use Illuminate\Http\Request;

class OutstandingController extends Controller
{
    public function __construct(
        private OutstandingService $outstandingService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);
        $highlightRequestId = $request->query('request_id');

        // 1. Menunggu Approval
        $requested = $this->outstandingService->getRequestedOutstanding($visibleUserIds)
            ->load(['category', 'creator'])
            ->sortBy(fn($r) => match ($r->priority) { 'high' => 0, 'normal' => 1, 'low' => 2, default => 1 })
            ->values();

        // 2. Approved, Belum Direalisasikan
        $approvedDraft = $this->outstandingService->getApprovedDraftOutstanding($visibleUserIds)
            ->load(['category', 'creator', 'approver', 'transactions'])
            ->sortBy('approved_at')
            ->values();

        // 3. Realisasi Parsial
        $partial = $this->outstandingService->getPartialOutstanding($visibleUserIds)
            ->load(['category', 'creator', 'approver', 'transactions', 'details'])
            ->sortBy('approved_at')
            ->values();

        return view('transaction.outstanding.index', compact(
            'requested',
            'approvedDraft',
            'partial',
            'highlightRequestId'
        ));
    }
}
