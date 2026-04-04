<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Mutasi Detail</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; }
        .header p { margin: 5px 0 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #2fb344; }
        .text-red { color: #d63939; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Mutasi Detail</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px" class="text-center">No</th>
                <th style="width:60px">Tanggal</th>
                <th>Deskripsi / Kategori</th>
                <th>Oleh</th>
                <th class="text-right">Masuk (Rp)</th>
                <th class="text-right">Keluar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $trx)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</td>
                <td>
                    {{ $trx->description }}<br>
                    <small style="color: #666;">[{{ $trx->category->name ?? '-' }}]</small>
                </td>
                <td>{{ $trx->creator->name ?? '-' }}</td>
                <td class="text-right text-green">
                    {{ $trx->trans_code == 1 ? number_format($trx->amount, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right text-red">
                    {{ $trx->trans_code == 2 ? number_format($trx->amount, 0, ',', '.') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada transaksi pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
        @if($transactions->count() > 0)
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right text-green">{{ number_format($transactions->where('trans_code', 1)->sum('amount'), 0, ',', '.') }}</th>
                <th class="text-right text-red">{{ number_format($transactions->where('trans_code', 2)->sum('amount'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
