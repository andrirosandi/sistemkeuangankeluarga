{{-- Balance Cards — guarded by @can('dashboard.system.balance') in parent --}}
<div class="row row-deck row-cards mb-4" x-data="dashboardBalance()">
    {{-- Saldo Awal --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <i class="ti ti-wallet"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Saldo Awal</div>
                        <div class="text-secondary" style="font-size:0.75rem">Bulan ini</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold" x-text="loading ? '...' : formatRupiah(data.begin)"></div>
            </div>
        </div>
    </div>

    {{-- Total Masuk --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-green text-white avatar">
                            <i class="ti ti-trending-up"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Total Masuk</div>
                        <div class="text-secondary" style="font-size:0.75rem">Bulan ini</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold text-green" x-text="loading ? '...' : formatRupiah(data.totalIn)"></div>
            </div>
        </div>
    </div>

    {{-- Total Keluar --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-red text-white avatar">
                            <i class="ti ti-trending-down"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Total Keluar</div>
                        <div class="text-secondary" style="font-size:0.75rem">Bulan ini</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold text-red" x-text="loading ? '...' : formatRupiah(data.totalOut)"></div>
            </div>
        </div>
    </div>

    {{-- Saldo Akhir --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-blue text-white avatar">
                            <i class="ti ti-cash-register"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Saldo Akhir</div>
                        <div class="text-secondary" style="font-size:0.75rem">Bulan ini</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold text-blue" x-text="loading ? '...' : formatRupiah(data.ending)"></div>
            </div>
        </div>
    </div>
</div>
