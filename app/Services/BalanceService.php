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
                // Ambil ending dari bulan sebelumnya sebagai begin
                $previousMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
                $previousBalance = Balance::where('month', $previousMonth)->lockForUpdate()->first();
                $begin = $previousBalance ? $previousBalance->ending : 0;

                $balance = Balance::create([
                    'month'     => $month,
                    'begin'     => $begin,
                    'total_in'  => 0,
                    'total_out' => 0,
                    'ending'    => $begin,
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

            // Update begin balance untuk bulan-bulan berikutnya
            $this->propagateBeginBalance($month);
        });
    }

    /**
     * Propagate begin balance ke bulan-bulan berikutnya.
     * Dipanggil setelah update balance untuk memastikan konsistensi.
     */
    private function propagateBeginBalance(string $fromMonth): void
    {
        $currentMonth = Carbon::parse($fromMonth . '-01');
        $previousBalance = Balance::where('month', $fromMonth)->first();

        if (!$previousBalance) {
            return;
        }

        $nextMonths = Balance::where('month', '>', $fromMonth)
            ->orderBy('month')
            ->get();

        $previousEnding = $previousBalance->ending;

        foreach ($nextMonths as $balance) {
            $oldBegin = $balance->begin;
            $balance->begin = $previousEnding;
            $balance->ending = $balance->begin + $balance->total_in - $balance->total_out;
            $balance->save();
            $previousEnding = $balance->ending;
        }
    }

    /**
     * Hitung ulang saldo dari bulan tertentu sampai bulan terbaru.
     * Berguna untuk memperbaiki data balance yang tidak konsisten.
     *
     * @param string $startMonth  Format: Y-m (e.g., "2026-01")
     */
    public function recalculateFromMonth(string $startMonth): void
    {
        DB::transaction(function () use ($startMonth) {
            $currentMonth = Carbon::parse($startMonth . '-01');

            // Ambil begin dari bulan sebelumnya jika ada
            $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
            $previousBalance = Balance::where('month', $previousMonth)->first();
            $currentBegin = $previousBalance ? $previousBalance->ending : 0;

            // Ambil semua bulan mulai dari startMonth
            $balances = Balance::where('month', '>=', $startMonth)
                ->orderBy('month')
                ->get();

            if ($balances->isEmpty()) {
                return;
            }

            // Hitung ulang per bulan
            foreach ($balances as $balance) {
                $balance->begin = $currentBegin;
                $balance->ending = $balance->begin + $balance->total_in - $balance->total_out;
                $balance->save();
                $currentBegin = $balance->ending;
            }
        });
    }
}
