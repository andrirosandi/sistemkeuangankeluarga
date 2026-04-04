<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Kas Umum - {{ \Carbon\Carbon::parse($monthDate)->translatedFormat('F Y') }}</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; padding: 20px; font-size: 14px; }
        .print-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .table { border: 1px solid #dee2e6; }
        .table th, .table td { padding: 0.5rem; border: 1px solid #dee2e6; }
        .table th { background-color: #f8f9fa !important; font-weight: bold; }
        .bg-light { background-color: #f8f9fa !important; }
        
        @media print {
            body { padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="d-flex justify-content-between align-items-center print-header">
        <div>
            <h1 class="mb-0">Buku Mutasi Kas (Ledger)</h1>
            <div class="text-secondary fs-4">Periode: {{ \Carbon\Carbon::parse($monthDate)->translatedFormat('F Y') }}</div>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2"></path><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"></path><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z"></path></svg>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="row mb-4 text-center">
        <div class="col-3">
            <div class="border p-2 rounded">
                <div class="text-muted small">Saldo Awal</div>
                <div class="fw-bold">@uang($beginBalance)</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border p-2 rounded">
                <div class="text-muted small">Total Pemasukan</div>
                <div class="fw-bold text-success">+ @uang($totalIn)</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border p-2 rounded">
                <div class="text-muted small">Total Pengeluaran</div>
                <div class="fw-bold text-danger">- @uang($totalOut)</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border p-2 rounded bg-light">
                <div class="text-muted small">Saldo Akhir</div>
                <div class="fw-bold fs-3">@uang($endBalance)</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="w-1">No</th>
                <th class="w-1">Tanggal</th>
                <th>Keterangan / Kategori</th>
                <th class="text-end">Debet (Masuk)</th>
                <th class="text-end">Kredit (Keluar)</th>
                <th class="text-end">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <!-- Begin Balance Row -->
            <tr class="bg-light">
                <td></td>
                <td class="text-muted">{{ $monthDate->startOfMonth()->format('d/m/Y') }}</td>
                <td class="fw-bold" colspan="3">SALDO AWAL BULAN INI</td>
                <td class="text-end fw-bold">@uang($beginBalance)</td>
            </tr>

            <!-- Mutations -->
            @forelse($mutations as $index => $mut)
            <tr>
                <td class="text-muted">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($mut->date)->format('d/m/Y') }}</td>
                <td>
                    <div>{{ $mut->description }}</div>
                    <div class="text-muted" style="font-size: 11px;">
                        [{{ $mut->category }}] • Oleh: {{ $mut->creator }}
                    </div>
                </td>
                <td class="text-end text-success">
                    @if($mut->debit > 0)+ @uang($mut->debit)@else-@endif
                </td>
                <td class="text-end text-danger">
                    @if($mut->credit > 0)- @uang($mut->credit)@else-@endif
                </td>
                <td class="text-end font-monospace">
                    @uang($mut->balance)
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">Tidak ada transaksi yang diproses (cair) pada bulan ini.</div>
                </td>
            </tr>
            @endforelse

            <!-- End Balance Row (Footer Info) -->
            <tr class="bg-light">
                <td colspan="3" class="text-end fw-bold text-uppercase">TOTAL MUTASI / SALDO AKHIR</td>
                <td class="text-end fw-bold text-success">@uang($totalIn)</td>
                <td class="text-end fw-bold text-danger">@uang($totalOut)</td>
                <td class="text-end fw-bold fs-3">@uang($endBalance)</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-5 row text-center">
        <div class="col-4 offset-8">
            <p>Admin / Direktur Keuangan</p>
            <br><br><br>
            <p class="fw-bold text-decoration-underline mb-0">{{ auth()->user()->name }}</p>
            <small>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </div>

    <script>
        // Auto print when page opens if requested (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
