@extends('layouts.admin')

@section('title', 'Laporan Pemasukan')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan Pemasukan</h2>
    <div class="text-secondary mt-1">Detail kas masuk: gaji, hutang dibayar, dll</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div x-data="incomeReport()">
    <div class="card mb-4">
        <div class="card-body d-flex gap-3 align-items-center flex-wrap">
            <div>
                <label class="form-label mb-0 small">Periode</label>
                <input type="month" class="form-control form-control-sm" value="{{ $month }}"
                       onchange="window.location.href='?month='+this.value+'&scope={{ $scope }}'">
            </div>
            @if(count($availableScopes) > 1)
            <div>
                <label class="form-label mb-0 small">Cakupan</label>
                <select class="form-select form-select-sm"
                        onchange="window.location.href='?month={{ $month }}&scope='+this.value">
                    @foreach($availableScopes as $s)
                    <option value="{{ $s['value'] }}" {{ $scope === $s['value'] ? 'selected' : '' }}>{{ $s['label'] }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="ms-auto">
                <div class="h2 mb-0 text-green">@uang($totalIncome)</div>
                <div class="text-secondary small">Total Pemasukan</div>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards mb-4">
        {{-- Donut by category --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-donut-3 me-1"></i> Per Kategori</h3></div>
                <div class="card-body">
                    @if($byCategory->isEmpty())
                        <div class="text-center text-secondary py-4">Belum ada data</div>
                    @else
                        <div id="income-chart" style="min-height:250px"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Category table --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-list me-1"></i> Rincian Pemasukan</h3></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-vcenter mb-0">
                        <thead><tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Oleh</th><th class="text-end">Nominal</th></tr></thead>
                        <tbody>
                            @forelse($transactions as $trx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->translatedFormat('d M Y') }}</td>
                                <td>{{ Str::limit($trx->description, 40) }}</td>
                                <td><span class="badge" style="background:{{ $trx->category->color ?? '#6c757d' }}20; color:{{ $trx->category->color ?? '#6c757d' }}">{{ $trx->category->name ?? '-' }}</span></td>
                                <td class="text-secondary">{{ $trx->creator->name ?? '-' }}</td>
                                <td class="text-end fw-bold text-green">+@uang($trx->amount)</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-secondary py-3">Belum ada pemasukan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function incomeReport() {
    return {
        init() {
            const cats = @json($byCategory->toArray());
            const keys = Object.keys(cats);
            if (!keys.length) return;
            const dk = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            new ApexCharts(document.querySelector('#income-chart'), {
                chart: { type: 'donut', height: 250, background: 'transparent', fontFamily: 'inherit' },
                series: keys.map(k => cats[k].total),
                labels: keys,
                colors: keys.map(k => cats[k].color || '#6c757d'),
                legend: { position: 'bottom', labels: { colors: dk ? '#c8d3e1' : '#333' } },
                plotOptions: { pie: { donut: { size: '60%' } } },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                theme: { mode: dk ? 'dark' : 'light' }
            }).render();
        }
    };
}
</script>
@endpush
