<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Category;
use App\Models\RoleVisibility;
use App\Models\TransactionHeader;
use App\Models\RequestHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Dashboard page — renders permission-driven widget layout.
     */
    public function index()
    {
        $user = auth()->user();
        $categories = Category::orderBy('name')->get(['id', 'name', 'color']);

        // Build available scopes based on permissions
        $availableScopes = [];
        if ($user->can('dashboard.scope.self')) {
            $availableScopes[] = ['value' => 'self', 'label' => 'Diri Sendiri'];
        }
        if ($user->can('dashboard.scope.group')) {
            $availableScopes[] = ['value' => 'group', 'label' => 'Grup'];
        }
        if ($user->can('dashboard.scope.all')) {
            $availableScopes[] = ['value' => 'all', 'label' => 'Semua'];
        }

        $defaultScope = $availableScopes[0]['value'] ?? 'self';

        return view('dashboard', compact('categories', 'availableScopes', 'defaultScope'));
    }

    // ─── API Methods ──────────────────────────────────────────────

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
     * Alerts widget — pending requests needing approval.
     */
    public function widgetAlerts(Request $request)
    {
        [$userIds] = $this->resolveScope($request);

        $alerts = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = $alerts->map(function ($req) {
            return [
                'id'          => $req->id,
                'description' => $req->description,
                'amount'      => (float) $req->amount,
                'creator'     => $req->creator->name ?? 'Sistem',
                'type'        => $req->trans_code == 1 ? 'in' : 'out',
                'category'    => $req->category->name ?? 'Tanpa Kategori',
                'created_at'  => $req->created_at->format('d M Y'),
            ];
        })->toArray();

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

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Resolve scope to a list of visible user IDs.
     * Server-side enforcement: caps scope based on user permissions.
     *
     * @return array{Collection, string} [userIds, resolvedScope]
     */
    private function resolveScope(Request $request): array
    {
        $user = auth()->user();
        $scope = $request->input('scope', 'self');

        // Enforce maximum allowed scope
        if ($scope === 'all' && !$user->can('dashboard.scope.all')) {
            $scope = $user->can('dashboard.scope.group') ? 'group' : 'self';
        }
        if ($scope === 'group' && !$user->can('dashboard.scope.group')) {
            $scope = 'self';
        }

        return match ($scope) {
            'all'   => [User::pluck('id'), 'all'],
            'group' => [RoleVisibility::getVisibleUserIds($user), 'group'],
            default => [collect([$user->id]), 'self'],
        };
    }
}
