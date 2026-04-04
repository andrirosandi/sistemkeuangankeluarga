{{-- Summary HTML fragment — returned by /api/dashboard/summary --}}
<div class="row g-3">
    <div class="col-6">
        <div class="d-flex align-items-center gap-2">
            <span class="bg-green-lt rounded p-2"><i class="ti ti-trending-up text-green"></i></span>
            <div>
                <div class="text-secondary" style="font-size:0.75rem">Pemasukan</div>
                <div class="fw-bold text-green">Rp {{ number_format($data['totalIn'], 0, ',', '.') }}</div>
                <div class="text-secondary" style="font-size:0.7rem">{{ $data['countIn'] }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="d-flex align-items-center gap-2">
            <span class="bg-red-lt rounded p-2"><i class="ti ti-trending-down text-red"></i></span>
            <div>
                <div class="text-secondary" style="font-size:0.75rem">Pengeluaran</div>
                <div class="fw-bold text-red">Rp {{ number_format($data['totalOut'], 0, ',', '.') }}</div>
                <div class="text-secondary" style="font-size:0.7rem">{{ $data['countOut'] }} transaksi</div>
            </div>
        </div>
    </div>
</div>
