@extends('layouts.admin')

@section('title', 'Laporan per Kategori')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan per Kategori</h2>
    <div class="text-secondary mt-1">Distribusi transaksi berdasarkan kategori</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div x-data="categoryReport()">
    {{-- Filter bar --}}
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
        </div>
    </div>

    <div class="row row-deck row-cards">
        {{-- Donut Chart --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-donut-3 me-1"></i> Proporsi</h3></div>
                <div class="card-body">
                    @if(empty($data))
                        <div class="text-center text-secondary py-4">Belum ada data</div>
                    @else
                        <div id="category-chart" style="min-height:300px"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Table --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-table me-1"></i> Detail per Kategori</h3></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-vcenter mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Masuk</th>
                                <th class="text-end">Keluar</th>
                                <th class="text-end">Jml Trx</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $cat)
                            <tr>
                                <td>
                                    <span class="badge me-1" style="background:{{ $cat['color'] }}; width:12px; height:12px; padding:0"></span>
                                    {{ $cat['name'] }}
                                </td>
                                <td class="text-end text-green">Rp {{ number_format($cat['totalIn'], 0, ',', '.') }}</td>
                                <td class="text-end text-red">Rp {{ number_format($cat['totalOut'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ $cat['countIn'] + $cat['countOut'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($data))
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end text-green">Rp {{ number_format(collect($data)->sum('totalIn'), 0, ',', '.') }}</td>
                                <td class="text-end text-red">Rp {{ number_format(collect($data)->sum('totalOut'), 0, ',', '.') }}</td>
                                <td class="text-end">{{ collect($data)->sum('countIn') + collect($data)->sum('countOut') }}</td>
                            </tr>
                        </tfoot>
                        @endif
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
function categoryReport() {
    return {
        init() {
            const data = @json(array_values($data));
            if (!data.length) return;
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            new ApexCharts(document.querySelector('#category-chart'), {
                chart: { type: 'donut', height: 300, background: 'transparent', fontFamily: 'inherit' },
                series: data.map(d => d.totalOut + d.totalIn),
                labels: data.map(d => d.name),
                colors: data.map(d => d.color || '#6c757d'),
                legend: { position: 'bottom', labels: { colors: isDark ? '#c8d3e1' : '#333' } },
                plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', color: isDark ? '#c8d3e1' : '#333', formatter: w => 'Rp ' + new Intl.NumberFormat('id-ID').format(w.globals.seriesTotals.reduce((a,b)=>a+b,0)) } } } } },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                theme: { mode: isDark ? 'dark' : 'light' }
            }).render();
        }
    };
}
</script>
@endpush
