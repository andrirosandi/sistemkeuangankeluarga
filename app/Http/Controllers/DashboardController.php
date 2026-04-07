<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Category;
use App\Models\TransactionHeader;
use App\Models\RequestHeader;
use App\Models\User;
use App\Services\OutstandingService;
use App\Services\ScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(
        private ScopeService $scopeService,
        private OutstandingService $outstandingService,
    ) {}
    /**
     * Dashboard page — renders permission-driven widget layout.
     */
    public function index()
    {
        $user = auth()->user();
        $categories = Category::orderBy('name')->get(['id', 'name', 'color']);

        $availableScopes = $this->scopeService->buildAvailableScopes($user);
        $defaultScope = $availableScopes[0]['value'] ?? 'self';

        // Detect if user is an approver
        $isApprover = $user->can('in.request.approve') || $user->can('out.request.approve');

        return view('dashboard', compact('categories', 'availableScopes', 'defaultScope', 'isApprover'));
    }

    // ─── Existing Widget API Methods ─────────────────────────────

    /**
     * Balance cards — system-wide, no scope filter.
     */
    public function widgetBalance()
    {
        $month = now()->format('Y-m');
        $balance = Balance::where('month', $month)->first();

        return response()->json([
            'begin'    => (float) ($balance->begin ?? 0),
            'totalIn'  => (float) ($balance->total_in ?? 0),
            'totalOut' => (float) ($balance->total_out ?? 0),
            'ending'   => (float) ($balance->ending ?? 0),
        ]);
    }

    /**
     * Summary widget — total in/out counts and amounts.
     */
    public function widgetSummary(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $categoryId = $request->input('category_id');

        $query = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $in = (clone $query)->where('trans_code', 1);
        $out = (clone $query)->where('trans_code', 2);

        $data = [
            'totalIn'   => (float) ($in->sum('amount') ?? 0),
            'totalOut'  => (float) ($out->sum('amount') ?? 0),
            'countIn'   => $in->count(),
            'countOut'  => $out->count(),
        ];

        return view('dashboard.summary', compact('data'))->render();
    }

    /**
     * Activity widget — last 7 days transaction totals.
     */
    public function widgetActivity(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $categoryId = $request->input('category_id');
        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');

        $query = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', '>=', $startDate);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $transactions = $query->get();

        // Group by date
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $days[$date] = ['date' => $date, 'debit' => 0, 'credit' => 0];
        }

        foreach ($transactions as $trx) {
            $d = $trx->transaction_date;
            if (!isset($days[$d])) continue;
            if ($trx->trans_code == 1) {
                $days[$d]['debit'] += $trx->amount;
            } else {
                $days[$d]['credit'] += $trx->amount;
            }
        }

        return view('dashboard.activity', ['data' => array_values($days)])->render();
    }

    /**
     * Alerts widget — pending requests needing action.
     *
     * Shows three types of "pending" items:
     * 1. Requested — awaiting approval (visible to users with approve permission)
     * 2. Belum Direalisasikan — approved but transaction still draft
     * 3. Parsial — approved with some details still pending realization
     */
    public function widgetAlerts(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $user = auth()->user();
        $isApprover = $user->can('in.request.approve') || $user->can('out.request.approve');

        $alerts = collect();

        // 1. Requested — awaiting approval (only visible to approvers)
        if ($isApprover) {
            $requestedAlerts = RequestHeader::with(['category', 'creator'])
                ->whereIn('created_by', $userIds)
                ->where('status', 'requested')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($requestedAlerts as $req) {
                $alerts->push([
                    'id'          => $req->id,
                    'description' => $req->description,
                    'amount'      => (float) $req->amount,
                    'creator'     => $req->creator->name ?? 'Sistem',
                    'type'        => $req->trans_code == 1 ? 'in' : 'out',
                    'category'    => $req->category->name ?? 'Tanpa Kategori',
                    'created_at'  => $req->created_at->format('d M Y'),
                    'alert_type'  => 'requested',
                    'badge_label' => 'Menunggu Approve',
                    'badge_class' => 'bg-yellow-lt text-yellow',
                ]);
            }
        }

        // 2. Approved but transaction still draft (belum direalisasikan)
        $approvedDraft = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'approved')
            ->whereHas('transactions', function ($q) {
                $q->where('status', 'draft');
            })
            ->orderBy('approved_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($approvedDraft as $req) {
            $alerts->push([
                'id'          => $req->id,
                'description' => $req->description,
                'amount'      => (float) $req->amount,
                'creator'     => $req->creator->name ?? 'Sistem',
                'type'        => $req->trans_code == 1 ? 'in' : 'out',
                'category'    => $req->category->name ?? 'Tanpa Kategori',
                'created_at'  => $req->created_at->format('d M Y'),
                'alert_type'  => 'approved_draft',
                'badge_label' => 'Belum Direalisasikan',
                'badge_class' => 'bg-blue-lt text-blue',
            ]);
        }

        // 3. Partial realized — approved with pending detail items or amount mismatch
        $partialRealized = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'approved')
            ->whereHas('transactions', function ($q) {
                $q->where('status', 'completed');
            })
            ->where(function ($q) {
                $q->whereHas('details', function ($dq) {
                    $dq->where('status', 'pending');
                })
                ->orwhereHas('transactions', function ($tq) {
                    $tq->where('status', 'completed')
                       ->whereColumn('amount', '<', 'request_header.amount');
                });
            })
            ->orderBy('approved_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($partialRealized as $req) {
            $alerts->push([
                'id'          => $req->id,
                'description' => $req->description,
                'amount'      => (float) $req->amount,
                'creator'     => $req->creator->name ?? 'Sistem',
                'type'        => $req->trans_code == 1 ? 'in' : 'out',
                'category'    => $req->category->name ?? 'Tanpa Kategori',
                'created_at'  => $req->created_at->format('d M Y'),
                'alert_type'  => 'partial',
                'badge_label' => 'Parsial',
                'badge_class' => 'bg-purple-lt text-purple',
            ]);
        }

        // Sort by created_at desc and limit total
        $data = $alerts->sortByDesc('created_at')->take(10)->values()->toArray();

        return view('dashboard.alerts', compact('data'))->render();
    }

    /**
     * Recent transactions widget.
     */
    public function widgetRecent(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $categoryId = $request->input('category_id');

        $query = TransactionHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'completed');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $transactions = $query
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = $transactions->map(function ($trx) {
            return [
                'id'          => $trx->id,
                'date'        => $trx->transaction_date,
                'description' => $trx->description,
                'amount'      => (float) $trx->amount,
                'type'        => $trx->trans_code == 1 ? 'in' : 'out',
                'category'    => $trx->category->name ?? 'Tanpa Kategori',
                'color'       => $trx->category->color ?? '#6c757d',
                'creator'     => $trx->creator->name ?? 'Sistem',
            ];
        })->toArray();

        return view('dashboard.recent', compact('data'))->render();
    }

    // ─── NEW Widget API Methods ──────────────────────────────────

    /**
     * W1: Request Summary — total requests, amounts, status breakdown.
     */
    public function widgetRequestSummary(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $month = now()->format('Y-m');

        $query = RequestHeader::whereIn('created_by', $userIds)
            ->where('request_date', 'like', $month . '%');

        $statuses = ['draft', 'requested', 'approved', 'rejected'];
        $byStatus = [];
        foreach ($statuses as $status) {
            $sq = (clone $query)->where('status', $status);
            $byStatus[$status] = [
                'count'  => $sq->count(),
                'amount' => (float) $sq->sum('amount'),
            ];
        }

        // Outstanding = requested (belum dijawab) + approved tapi transaksi belum direalisasikan/sebagian
        $outstandingRequested = (clone $query)->where('status', 'requested')->sum('amount');
        $outstandingApproved = (clone $query)->where('status', 'approved')
            ->whereHas('transactions', function ($q) {
                $q->where('status', 'draft');
            })->sum('amount');

        // Partial: approved requests whose transaction is completed but has pending detail items or amount mismatch
        $partialRealized = (clone $query)->where('status', 'approved')
            ->whereHas('transactions', function ($q) {
                $q->where('status', 'completed');
            })
            ->where(function ($q) {
                $q->whereHas('details', function ($dq) {
                    $dq->where('status', 'pending');
                })->orwhereHas('transactions', function ($tq) {
                    $tq->where('status', 'completed')
                       ->whereColumn('amount', '<', 'request_header.amount');
                });
            })->sum('amount');

        $data = [
            'totalCount'    => (clone $query)->count(),
            'totalAmount'   => (float) (clone $query)->sum('amount'),
            'byStatus'      => $byStatus,
            'outstanding'   => (float) ($outstandingRequested + $outstandingApproved + $partialRealized),
        ];

        return view('dashboard.request-summary', compact('data'))->render();
    }

    /**
     * W2: Category Breakdown — donut chart data (JSON).
     */
    public function widgetCategoryBreakdown(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $month = now()->format('Y-m');

        // Transaction-based breakdown (completed only)
        $rows = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%')
            ->selectRaw('category_id, trans_code, SUM(amount) as total')
            ->groupBy('category_id', 'trans_code')
            ->get();

        $categories = Category::pluck('color', 'id')->toArray();
        $names = Category::pluck('name', 'id')->toArray();

        $result = [];
        foreach ($rows as $row) {
            $catId = $row->category_id;
            if (!isset($result[$catId])) {
                $result[$catId] = [
                    'category' => $names[$catId] ?? 'Lainnya',
                    'color'    => $categories[$catId] ?? '#6c757d',
                    'totalIn'  => 0,
                    'totalOut' => 0,
                ];
            }
            if ($row->trans_code == 1) {
                $result[$catId]['totalIn'] = (float) $row->total;
            } else {
                $result[$catId]['totalOut'] = (float) $row->total;
            }
        }

        return response()->json(array_values($result));
    }

    /**
     * W3: Group Ranking — spending/earning by role.
     */
    public function widgetGroupRanking(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $month = now()->format('Y-m');

        // Single aggregated query: get totals per created_by
        $totals = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%')
            ->selectRaw('created_by, trans_code, SUM(amount) as total')
            ->groupBy('created_by', 'trans_code')
            ->get();

        // Get user-role mapping in one query
        $users = User::whereIn('id', $userIds)->with('roles')->get();
        $userRoleMap = [];
        foreach ($users as $u) {
            $userRoleMap[$u->id] = $u->roles->first()?->name ?? 'Tanpa Role';
        }

        // Aggregate by role
        $byRole = [];
        foreach ($totals as $row) {
            $role = $userRoleMap[$row->created_by] ?? 'Tanpa Role';
            if ($role === 'admin') continue;

            if (!isset($byRole[$role])) {
                $byRole[$role] = ['totalIn' => 0, 'totalOut' => 0];
            }
            if ($row->trans_code == 1) {
                $byRole[$role]['totalIn'] += $row->total;
            } else {
                $byRole[$role]['totalOut'] += $row->total;
            }
        }

        $ranking = [];
        foreach ($byRole as $name => $amounts) {
            $ranking[] = [
                'name'     => $name,
                'totalOut' => (float) $amounts['totalOut'],
                'totalIn'  => (float) $amounts['totalIn'],
                'net'      => (float) ($amounts['totalIn'] - $amounts['totalOut']),
            ];
        }

        usort($ranking, fn($a, $b) => $b['totalOut'] <=> $a['totalOut']);

        $maxOut = collect($ranking)->max('totalOut') ?: 1;

        return view('dashboard.group-ranking', compact('ranking', 'maxOut'))->render();
    }

    /**
     * W4: User Ranking — spending/earning by user.
     */
    public function widgetUserRanking(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $month = now()->format('Y-m');

        // Single aggregated query
        $totals = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%')
            ->selectRaw('created_by, trans_code, SUM(amount) as total')
            ->groupBy('created_by', 'trans_code')
            ->get();

        $users = User::whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        $byUser = [];
        foreach ($totals as $row) {
            if (!isset($byUser[$row->created_by])) {
                $byUser[$row->created_by] = ['totalIn' => 0, 'totalOut' => 0];
            }
            if ($row->trans_code == 1) {
                $byUser[$row->created_by]['totalIn'] += $row->total;
            } else {
                $byUser[$row->created_by]['totalOut'] += $row->total;
            }
        }

        $ranking = [];
        foreach ($byUser as $userId => $amounts) {
            $ranking[] = [
                'name'     => $users[$userId]->name ?? 'Unknown',
                'totalOut' => (float) $amounts['totalOut'],
                'totalIn'  => (float) $amounts['totalIn'],
                'net'      => (float) ($amounts['totalIn'] - $amounts['totalOut']),
            ];
        }

        usort($ranking, fn($a, $b) => $b['totalOut'] <=> $a['totalOut']);

        $maxOut = collect($ranking)->max('totalOut') ?: 1;

        return view('dashboard.user-ranking', compact('ranking', 'maxOut'))->render();
    }

    /**
     * W5: Outstanding Board — requests not yet fully realized + aging.
     */
    public function widgetOutstanding(Request $request)
    {
        [$userIds] = $this->resolveScope($request);
        $data = $this->outstandingService->getWidgetData($userIds);

        return view('dashboard.outstanding', compact('data'))->render();
    }

    /**
     * W6: Month Comparison — current vs previous month (JSON for chart).
     */
    public function widgetMonthCompare()
    {
        $currentMonth = now()->format('Y-m');
        $prevMonth = now()->subMonth()->format('Y-m');

        $current = Balance::where('month', $currentMonth)->first();
        $previous = Balance::where('month', $prevMonth)->first();

        $currentIn = (float) ($current->total_in ?? 0);
        $currentOut = (float) ($current->total_out ?? 0);
        $prevIn = (float) ($previous->total_in ?? 0);
        $prevOut = (float) ($previous->total_out ?? 0);

        return response()->json([
            'labels'  => [now()->subMonth()->translatedFormat('M Y'), now()->translatedFormat('M Y')],
            'current' => [
                'in'  => $currentIn,
                'out' => $currentOut,
                'net' => $currentIn - $currentOut,
            ],
            'previous' => [
                'in'  => $prevIn,
                'out' => $prevOut,
                'net' => $prevIn - $prevOut,
            ],
            'deltaIn'  => $prevIn > 0 ? round(($currentIn - $prevIn) / $prevIn * 100, 1) : 0,
            'deltaOut' => $prevOut > 0 ? round(($currentOut - $prevOut) / $prevOut * 100, 1) : 0,
        ]);
    }

    /**
     * W7: Approval Stats — for users who have approve permission.
     */
    public function widgetApprovalStats(Request $request)
    {
        $user = auth()->user();
        $month = now()->format('Y-m');

        // Requests that this user approved/rejected
        $reviewed = RequestHeader::where('approved_by', $user->id)
            ->where('approved_at', 'like', $month . '%');

        $approvedCount = (clone $reviewed)->where('status', 'approved')->count();
        $rejectedCount = (clone $reviewed)->where('status', 'rejected')->count();
        $approvedAmount = (float) (clone $reviewed)->where('status', 'approved')->sum('amount');

        // Pending requests visible to this user (from visible users)
        [$userIds] = $this->resolveScope($request);
        $pendingCount = RequestHeader::whereIn('created_by', $userIds)
            ->where('status', 'requested')
            ->count();
        $pendingAmount = (float) RequestHeader::whereIn('created_by', $userIds)
            ->where('status', 'requested')
            ->sum('amount');

        // Overdue: pending requests older than 3 days
        $overdueCount = RequestHeader::whereIn('created_by', $userIds)
            ->where('status', 'requested')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        // Average response time (in hours) — SQLite compatible
        $avgResponseHours = RequestHeader::where('approved_by', $user->id)
            ->whereNotNull('approved_at')
            ->where('approved_at', 'like', $month . '%')
            ->selectRaw('AVG((julianday(approved_at) - julianday(created_at)) * 24) as avg_hours')
            ->value('avg_hours');

        $data = [
            'approvedCount'  => $approvedCount,
            'rejectedCount'  => $rejectedCount,
            'approvedAmount' => $approvedAmount,
            'pendingCount'   => $pendingCount,
            'pendingAmount'  => $pendingAmount,
            'overdueCount'   => $overdueCount,
            'avgResponseHours' => $avgResponseHours ? round($avgResponseHours, 1) : null,
        ];

        return view('dashboard.approval-stats', compact('data'))->render();
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Resolve scope via ScopeService.
     *
     * @return array{Collection, string} [userIds, resolvedScope]
     */
    private function resolveScope(Request $request): array
    {
        $user = auth()->user();
        $scope = $request->input('scope', 'self');

        return $this->scopeService->resolveScope($user, $scope);
    }
}
