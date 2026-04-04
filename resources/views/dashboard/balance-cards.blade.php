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
                <div class="mt-3 h2 mb-0 fw-bold">
                    <template x-if="loading"><span class="text-secondary" style="font-size:1rem">Memuat...</span></template>
                    <template x-if="error"><span class="text-danger" style="font-size:1rem">Error</span></template>
                    <template x-if="!loading && !error"><span x-text="formatRupiah(data.begin)"></span></template>
                </div>
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
                <div class="mt-3 h2 mb-0 fw-bold text-green">
                    <template x-if="loading"><span class="text-secondary" style="font-size:1rem">Memuat...</span></template>
                    <template x-if="error"><span class="text-danger" style="font-size:1rem">Error</span></template>
                    <template x-if="!loading && !error"><span x-text="formatRupiah(data.totalIn)"></span></template>
                </div>
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
                <div class="mt-3 h2 mb-0 fw-bold text-red">
                    <template x-if="loading"><span class="text-secondary" style="font-size:1rem">Memuat...</span></template>
                    <template x-if="error"><span class="text-danger" style="font-size:1rem">Error</span></template>
                    <template x-if="!loading && !error"><span x-text="formatRupiah(data.totalOut)"></span></template>
                </div>
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
                <div class="mt-3 h2 mb-0 fw-bold text-blue">
                    <template x-if="loading"><span class="text-secondary" style="font-size:1rem">Memuat...</span></template>
                    <template x-if="error"><span class="text-danger" style="font-size:1rem">Error</span></template>
                    <template x-if="!loading && !error"><span x-text="formatRupiah(data.ending)"></span></template>
                </div>
            </div>
        </div>
    </div>
</div>
