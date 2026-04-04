@extends('layouts.admin')

@section('title', 'Realisasi vs Pengajuan')

@section('page-header')
<div class="col">
    <h2 class="page-title">Realisasi vs Pengajuan</h2>
    <div class="text-secondary mt-1">Rasio efisiensi & penghematan budget</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div x-data="efficiencyReport()">
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

    {{-- Summary Cards --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-blue-lt rounded p-2"><i class="ti ti-file-text text-blue"></i></span>
                        <div>
                            <div class="text-secondary" style="font-size:0.75rem">Total Diajukan</div>
                            <div class="fw-bold">@uang($data['totalRequested'])</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-green-lt rounded p-2"><i class="ti ti-check text-green"></i></span>
                        <div>
                            <div class="text-secondary" style="font-size:0.75rem">Total Terealisasi</div>
                            <div class="fw-bold text-green">@uang($data['totalRealized'])</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-teal-lt rounded p-2"><i class="ti ti-piggy-bank text-teal"></i></span>
                        <div>
                            <div class="text-secondary" style="font-size:0.75rem">Penghematan</div>
                            <div class="fw-bold text-teal">@uang($data['totalSavings'])</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-yellow-lt rounded p-2"><i class="ti ti-percentage text-yellow"></i></span>
                        <div>
                            <div class="text-secondary" style="font-size:0.75rem">Rasio Realisasi</div>
                            <div class="fw-bold">{{ $data['efficiencyRate'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards">
        {{-- Bar Chart --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-bar me-1"></i> Pengajuan vs Realisasi per Kategori</h3></div>
                <div class="card-body">
                    @if(empty($data['byCategory']))
                        <div class="text-center text-secondary py-4">Belum ada data</div>
                    @else
                        <div id="efficiency-chart" style="min-height:300px"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Table --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-table me-1"></i> Detail Efisiensi</h3></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-vcenter mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Diajukan</th>
                                <th class="text-end">Realisasi</th>
                                <th class="text-end">Hemat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['byCategory'] as $cat)
                            <tr>
                                <td>
                                    <span class="badge me-1" style="background:{{ $cat['color'] }}; width:12px; height:12px; padding:0"></span>
                                    {{ $cat['name'] }}
                                </td>
                                <td class="text-end">@uang($cat['requested'])</td>
                                <td class="text-end text-green">@uang($cat['realized'])</td>
                                <td class="text-end text-teal">@uang($cat['savings'])</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-secondary py-3">Belum ada data</td></tr>
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
function efficiencyReport() {
    return {
        init() {
            const cats = @json($data['byCategory']);
            if (!cats.length) return;
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            new ApexCharts(document.querySelector('#efficiency-chart'), {
                chart: { type: 'bar', height: 300, background: 'transparent', fontFamily: 'inherit', toolbar: { show: false } },
                series: [
                    { name: 'Diajukan', data: cats.map(c => c.requested) },
                    { name: 'Realisasi', data: cats.map(c => c.realized) },
                ],
                xaxis: { categories: cats.map(c => c.name), labels: { style: { colors: isDark ? '#c8d3e1' : '#333' } } },
                yaxis: { labels: { style: { colors: isDark ? '#c8d3e1' : '#333' }, formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                colors: ['#206bc4', '#2fb344'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                legend: { position: 'top', labels: { colors: isDark ? '#c8d3e1' : '#333' } },
                tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                grid: { borderColor: isDark ? '#2c3e50' : '#e9ecef' },
                theme: { mode: isDark ? 'dark' : 'light' }
            }).render();
        }
    };
}
</script>
@endpush
