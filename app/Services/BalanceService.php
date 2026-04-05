<?php

namespace App\Services;

use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Update saldo bulanan berdasarkan transaksi.
     * Menggunakan row-level locking untuk mencegah race condition.
     *
     * @param string $transactionDate  Tanggal transaksi (Y-m-d)
     * @param int    $transCode        1 = kas masuk, 2 = kas keluar
     * @param float  $amount           Nominal transaksi
     * @param string $operation        'add' untuk menambah, 'remove' untuk membatalkan
     */
    public function updateBalance(string $transactionDate, int $transCode, float $amount, string $operation = 'add'): void
    {
        $month = Carbon::parse($transactionDate)->format('Y-m');
        $sign = $operation === 'add' ? 1 : -1;

        DB::transaction(function () use ($month, $transCode, $amount, $sign) {
            $balance = Balance::where('month', $month)->lockForUpdate()->first();

            if (!$balance) {
                $balance = Balance::create([
                    'month'     => $month,
                    'begin'     => 0,
                    'total_in'  => 0,
                    'total_out' => 0,
                    'ending'    => 0,
                ]);
                $balance = Balance::where('month', $month)->lockForUpdate()->first();
            }

            if ($transCode == 1) {
                $balance->total_in += $sign * $amount;
            } else {
                $balance->total_out += $sign * $amount;
            }

            $balance->ending = $balance->begin + $balance->total_in - $balance->total_out;
            $balance->save();
        });
    }
}
