<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Balance;
use App\Models\TransactionHeader;
use App\Models\RoleVisibility;
use Carbon\Carbon;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Buku Mutasi Kas';
        
        $month = $request->input('month', date('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        // Siapkan opsi dropdown bulan dari transaksi yang ada + current month
        $availableMonths = TransactionHeader::whereIn('created_by', $visibleUserIds)
            ->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as date_val")
            ->groupBy('date_val')
            ->orderBy('date_val', 'desc')
            ->pluck('date_val')
            ->toArray();
            
        $currentMonthRaw = date('Y-m');
        if (!in_array($currentMonthRaw, $availableMonths)) {
            array_unshift($availableMonths, $currentMonthRaw);
            rsort($availableMonths);
        }

        $monthOptions = [];
        foreach($availableMonths as $m) {
            $monthOptions[$m] = Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y');
        }

        // Cari record Balance untuk bulan ini (jika ada). 
        // Jika ada visibility yang berbeda, summary balance bulanan mungkin tidak merefleksikan jumlah yang 'visible',  
        // tetapi untuk kepraktisan kita pakai perhitungan langsung dari total akumulasi atau ambil dari database *Balance*.
        // Catatan: Model 'Balance' merepresentasikan saldo total bulanan.
        // Jika user dibatasi, idealnya Mutasi dihitung on-the-fly dari transaksi visible-nya.
        
        // Saldo Awal = Transaksi in - out (completed) sebelum $monthDate 
        // Atau ambil langsung dari table Balance jika user ini adalah admin.
        // Untuk menjaga konsistensi data yang visible (Role Visibility), kita bisa menghitung on-the-fly untuk "real begin balance" user tersebut.
        
        // 1. Calculate Begin Balance for visible users unconditionally before this month
        $beginIn = TransactionHeader::whereIn('created_by', $visibleUserIds)
                    ->where('status', 'completed')
                    ->where('trans_code', 1) // In
                    ->where('transaction_date', '<', $monthDate->startOfMonth()->format('Y-m-d'))
                    ->sum('amount');
        
        $beginOut = TransactionHeader::whereIn('created_by', $visibleUserIds)
                    ->where('status', 'completed')
                    ->where('trans_code', 2) // Out
                    ->where('transaction_date', '<', $monthDate->startOfMonth()->format('Y-m-d'))
                    ->sum('amount');
        
        $beginBalance = ($beginIn - $beginOut);

        // 2. Fetch all completed transactions for THIS month
        $transactions = TransactionHeader::with(['category', 'creator'])
            ->whereIn('created_by', $visibleUserIds)
            ->where('status', 'completed')
            ->whereYear('transaction_date', $monthDate->year)
            ->whereMonth('transaction_date', $monthDate->month)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Calculate running balance and summary
        $mutations = [];
        $runningBalance = $beginBalance;
        $totalIn = 0;
        $totalOut = 0;

        foreach ($transactions as $trx) {
            if ($trx->trans_code == 1) { // Pemasukan
                $runningBalance += $trx->amount;
                $totalIn += $trx->amount;
                $mutations[] = (object) [
                    'date' => $trx->transaction_date,
                    'description' => $trx->description,
                    'category' => $trx->category->name ?? 'Tanpa Kategori',
                    'color' => $trx->category->color ?? '#6c757d',
                    'debit' => $trx->amount,
                    'credit' => 0,
                    'balance' => $runningBalance,
                    'creator' => $trx->creator->name ?? 'Sistem'
                ];
            } else { // Pengeluaran
                $runningBalance -= $trx->amount;
                $totalOut += $trx->amount;
                $mutations[] = (object) [
                    'date' => $trx->transaction_date,
                    'description' => $trx->description,
                    'category' => $trx->category->name ?? 'Tanpa Kategori',
                    'color' => $trx->category->color ?? '#6c757d',
                    'debit' => 0,
                    'credit' => $trx->amount,
                    'balance' => $runningBalance,
                    'creator' => $trx->creator->name ?? 'Sistem'
                ];
            }
        }
        
        $endBalance = $runningBalance;

        // View context depending on route
        if (request()->routeIs('mutation.print')) {
            return view('transaction.mutation.print', compact('mutations', 'beginBalance', 'totalIn', 'totalOut', 'endBalance', 'monthDate'));
        }

        return view('transaction.mutation.index', compact('title', 'mutations', 'beginBalance', 'totalIn', 'totalOut', 'endBalance', 'month', 'monthDate', 'monthOptions'));
    }
}
