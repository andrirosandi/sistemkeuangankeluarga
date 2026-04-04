{{-- Summary HTML fragment — returned by /api/dashboard/summary --}}
<div class="row g-3">
    <div class="col-6">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-green-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-trending-up text-green"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Pemasukan</div>
                <div class="fw-bold text-green text-truncate">@uang($data['totalIn'])</div>
                <div class="text-secondary" style="font-size:0.7rem">{{ $data['countIn'] }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="d-flex align-items-center gap-2" style="min-height: 60px">
            <span class="bg-red-lt rounded p-2 flex-shrink-0 align-self-center"><i class="ti ti-trending-down text-red"></i></span>
            <div class="text-truncate">
                <div class="text-secondary" style="font-size:0.75rem">Pengeluaran</div>
                <div class="fw-bold text-red text-truncate">@uang($data['totalOut'])</div>
                <div class="text-secondary" style="font-size:0.7rem">{{ $data['countOut'] }} transaksi</div>
            </div>
        </div>
    </div>
</div>
