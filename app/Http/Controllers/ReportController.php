<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Category;
use App\Models\RequestHeader;
use App\Models\RoleVisibility;
use App\Models\TransactionHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class ReportController extends Controller
{
    /**
     * Report landing page — card grid with links to each report.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->can('report.view');

        return view('report.index', compact('isAdmin'));
    }

    /**
     * R1: Laporan Tahunan — 12-month line chart from balance table.
     */
    public function annual(Request $request)
    {
        $year = $request->input('year', now()->year);
        $user = auth()->user();

        $balances = Balance::where('month', 'like', $year . '-%')
            ->orderBy('month')
            ->get();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $row = $balances->firstWhere('month', $key);
            $months[] = [
                'month'   => $key,
                'label'   => Carbon::createFromFormat('Y-m', $key)->translatedFormat('M'),
                'in'      => (float) ($row->total_in ?? 0),
                'out'     => (float) ($row->total_out ?? 0),
                'ending'  => (float) ($row->ending ?? 0),
            ];
        }

        $years = Balance::selectRaw('SUBSTRING(month, 1, 4) as y')
            ->distinct()->orderBy('y', 'desc')->pluck('y')->toArray();
        if (!in_array($year, $years)) array_unshift($years, $year);

        return view('report.annual', compact('months', 'year', 'years'));
    }

    /**
     * R2: Laporan per Kategori — donut chart + detail table.
     */
    public function category(Request $request)
    {
        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));

        $rows = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%')
            ->selectRaw('category_id, trans_code, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('category_id', 'trans_code')
            ->get();

        $categories = Category::pluck('name', 'id')->toArray();
        $colors = Category::pluck('color', 'id')->toArray();

        $data = [];
        foreach ($rows as $row) {
            $catId = $row->category_id;
            if (!isset($data[$catId])) {
                $data[$catId] = [
                    'name'     => $categories[$catId] ?? 'Lainnya',
                    'color'    => $colors[$catId] ?? '#6c757d',
                    'totalIn'  => 0,
                    'totalOut' => 0,
                    'countIn'  => 0,
                    'countOut' => 0,
                ];
            }
            if ($row->trans_code == 1) {
                $data[$catId]['totalIn'] = (float) $row->total;
                $data[$catId]['countIn'] = $row->cnt;
            } else {
                $data[$catId]['totalOut'] = (float) $row->total;
                $data[$catId]['countOut'] = $row->cnt;
            }
        }

        $availableScopes = $this->buildAvailableScopes();

        return view('report.category', compact('data', 'month', 'scope', 'availableScopes'));
    }

    /**
     * R3: Laporan Mutasi Detail — grid table with pagination.
     */
    public function mutation(Request $request)
    {
        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));
        $transCode = $request->input('trans_code');

        $query = TransactionHeader::with(['category', 'creator', 'details'])
            ->where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%');

        if ($transCode) {
            $query->where('trans_code', $transCode);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $availableScopes = $this->buildAvailableScopes();

        return view('report.mutation', compact('transactions', 'month', 'scope', 'transCode', 'availableScopes'));
    }

    /**
     * R4: Efisiensi — Realisasi vs Pengajuan.
     */
    public function efficiency(Request $request)
    {
        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));

        // Total requested amount
        $totalRequested = RequestHeader::whereIn('created_by', $userIds)
            ->whereIn('status', ['approved', 'rejected', 'requested'])
            ->where('request_date', 'like', $month . '%')
            ->sum('amount');

        // Total realized (completed transactions from requests)
        $totalRealized = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->whereNotNull('request_id')
            ->where('transaction_date', 'like', $month . '%')
            ->sum('amount');

        // Total rejected
        $totalRejected = RequestHeader::whereIn('created_by', $userIds)
            ->where('status', 'rejected')
            ->where('request_date', 'like', $month . '%')
            ->sum('amount');

        // Breakdown by category
        $byCategory = TransactionHeader::where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->whereNotNull('request_id')
            ->where('transaction_date', 'like', $month . '%')
            ->selectRaw('category_id, SUM(amount) as realized')
            ->groupBy('category_id')
            ->get();

        $requestByCategory = RequestHeader::whereIn('created_by', $userIds)
            ->whereIn('status', ['approved', 'rejected', 'requested'])
            ->where('request_date', 'like', $month . '%')
            ->selectRaw('category_id, SUM(amount) as requested')
            ->groupBy('category_id')
            ->get();

        $catNames = Category::pluck('name', 'id')->toArray();
        $catColors = Category::pluck('color', 'id')->toArray();

        $categoryData = [];
        foreach ($requestByCategory as $row) {
            $catId = $row->category_id;
            $realized = $byCategory->firstWhere('category_id', $catId)?->realized ?? 0;
            $categoryData[] = [
                'name'      => $catNames[$catId] ?? 'Lainnya',
                'color'     => $catColors[$catId] ?? '#6c757d',
                'requested' => (float) $row->requested,
                'realized'  => (float) $realized,
                'savings'   => (float) ($row->requested - $realized),
            ];
        }

        $data = [
            'totalRequested' => (float) $totalRequested,
            'totalRealized'  => (float) $totalRealized,
            'totalRejected'  => (float) $totalRejected,
            'totalSavings'   => (float) ($totalRequested - $totalRealized),
            'efficiencyRate' => $totalRequested > 0 ? round($totalRealized / $totalRequested * 100, 1) : 0,
            'byCategory'     => $categoryData,
        ];

        $availableScopes = $this->buildAvailableScopes();

        return view('report.efficiency', compact('data', 'month', 'scope', 'availableScopes'));
    }

    /**
     * R5: Laporan Outstanding — requests not yet realized.
     */
    public function outstanding(Request $request)
    {
        [$userIds, $scope] = $this->resolveScope($request);

        // 1. Requested (belum dijawab)
        $requested = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'requested')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Approved tapi transaksi masih draft
        $approvedDraft = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'approved')
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'draft');
            })
            ->orderBy('approved_at', 'asc')
            ->get();

        // 3. Partial realized
        $partial = RequestHeader::with(['category', 'creator'])
            ->whereIn('created_by', $userIds)
            ->where('status', 'approved')
            ->whereHas('details', function ($q) {
                $q->where('status', 'pending');
            })
            ->whereDoesntHave('transaction', function ($q) {
                $q->where('status', 'draft');
            })
            ->orderBy('approved_at', 'asc')
            ->get();

        $availableScopes = $this->buildAvailableScopes();

        return view('report.outstanding', compact('requested', 'approvedDraft', 'partial', 'scope', 'availableScopes'));
    }

    /**
     * R6: Laporan per Anggota/Group — admin only.
     */
    public function perMember(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $users = User::with('roles')->where('is_active', 1)->get();

        $members = [];
        foreach ($users as $u) {
            $totalOut = TransactionHeader::where('status', 'completed')
                ->where('created_by', $u->id)
                ->where('trans_code', 2)
                ->where('transaction_date', 'like', $month . '%')
                ->sum('amount');

            $totalIn = TransactionHeader::where('status', 'completed')
                ->where('created_by', $u->id)
                ->where('trans_code', 1)
                ->where('transaction_date', 'like', $month . '%')
                ->sum('amount');

            $requestCount = RequestHeader::where('created_by', $u->id)
                ->where('request_date', 'like', $month . '%')
                ->count();

            $members[] = [
                'name'         => $u->name,
                'role'         => $u->roles->first()?->name ?? '-',
                'totalOut'     => (float) $totalOut,
                'totalIn'      => (float) $totalIn,
                'net'          => (float) ($totalIn - $totalOut),
                'requestCount' => $requestCount,
            ];
        }

        usort($members, fn($a, $b) => $b['totalOut'] <=> $a['totalOut']);
        $maxOut = collect($members)->max('totalOut') ?: 1;

        return view('report.per-member', compact('members', 'month', 'maxOut'));
    }

    /**
     * R7: Laporan Pemasukan — income detail.
     */
    public function income(Request $request)
    {
        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));

        $transactions = TransactionHeader::with(['category', 'creator'])
            ->where('status', 'completed')
            ->where('trans_code', 1)
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%')
            ->orderBy('transaction_date', 'desc')
            ->get();

        // By category
        $byCategory = $transactions->groupBy(fn($t) => $t->category?->name ?? 'Lainnya')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
                'color' => $group->first()->category?->color ?? '#6c757d',
            ]);

        $totalIncome = $transactions->sum('amount');
        $availableScopes = $this->buildAvailableScopes();

        return view('report.income', compact('transactions', 'byCategory', 'totalIncome', 'month', 'scope', 'availableScopes'));
    }

    /**
     * Export Mutation to PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_if(!auth()->user()->can('report.export'), 403);

        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));
        $transCode = $request->input('trans_code');

        $query = TransactionHeader::with(['category', 'creator'])
            ->where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%');

        if ($transCode) {
            $query->where('trans_code', $transCode);
        }

        $transactions = $query->orderBy('transaction_date', 'asc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.mutation-pdf', compact('transactions', 'month'));
        
        return $pdf->download('Mutasi_' . $month . '.pdf');
    }

    /**
     * Export Mutation to Excel.
     */
    public function exportExcel(Request $request)
    {
        abort_if(!auth()->user()->can('report.export'), 403);

        [$userIds, $scope] = $this->resolveScope($request);
        $month = $request->input('month', now()->format('Y-m'));
        $transCode = $request->input('trans_code');

        $query = TransactionHeader::with(['category', 'creator'])
            ->where('status', 'completed')
            ->whereIn('created_by', $userIds)
            ->where('transaction_date', 'like', $month . '%');

        if ($transCode) {
            $query->where('trans_code', $transCode);
        }

        $transactions = $query->orderBy('transaction_date', 'asc')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MutationExport($transactions), 
            'Mutasi_' . $month . '.xlsx'
        );
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Resolve scope — same logic as DashboardController.
     */
    private function resolveScope(Request $request): array
    {
        $user = auth()->user();
        $scope = $request->input('scope', 'self');

        // If user only has report.view.self (not report.view), force self scope
        if (!$user->can('report.view') && $user->can('report.view.self')) {
            $scope = 'self';
        }

        if ($scope === 'all' && !$user->can('dashboard.scope.all')) {
            $scope = $user->can('dashboard.scope.group') ? 'group' : 'self';
        }
        if ($scope === 'group' && !$user->can('dashboard.scope.group')) {
            $scope = 'self';
        }

        $userIds = match ($scope) {
            'all'   => User::pluck('id'),
            'group' => RoleVisibility::getVisibleUserIds($user),
            default => collect([$user->id]),
        };

        return [$userIds, $scope];
    }

    /**
     * Build available scope options for the filter dropdown.
     */
    private function buildAvailableScopes(): array
    {
        $user = auth()->user();
        $scopes = [];

        // If user only has report.view.self, only show self scope
        if (!$user->can('report.view')) {
            return [['value' => 'self', 'label' => 'Diri Sendiri']];
        }

        if ($user->can('dashboard.scope.self')) {
            $scopes[] = ['value' => 'self', 'label' => 'Diri Sendiri'];
        }
        if ($user->can('dashboard.scope.group')) {
            $scopes[] = ['value' => 'group', 'label' => 'Grup'];
        }
        if ($user->can('dashboard.scope.all')) {
            $scopes[] = ['value' => 'all', 'label' => 'Semua'];
        }

        return $scopes;
    }
}
