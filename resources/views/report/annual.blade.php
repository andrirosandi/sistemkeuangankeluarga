@extends('layouts.admin')

@section('title', 'Laporan Tahunan')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan Tahunan</h2>
    <div class="text-secondary mt-1">Trend pemasukan vs pengeluaran selama 12 bulan</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div class="card" x-data="annualReport()">
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-chart-line me-1"></i> Trend Tahunan</h3>
        <div class="card-actions">
            <select class="form-select form-select-sm" x-model="year" @change="window.location.href='?year='+year" style="width:auto">
                @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="annual-chart" style="min-height:350px"></div>
    </div>

    {{-- Summary table --}}
    <div class="table-responsive">
        <table class="table table-sm table-striped table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="text-end text-green">Pemasukan</th>
                    <th class="text-end text-red">Pengeluaran</th>
                    <th class="text-end text-blue">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $m)
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $m['month'])->translatedFormat('F') }}</td>
                    <td class="text-end text-green">Rp {{ number_format($m['in'], 0, ',', '.') }}</td>
                    <td class="text-end text-red">Rp {{ number_format($m['out'], 0, ',', '.') }}</td>
                    <td class="text-end text-blue fw-bold">Rp {{ number_format($m['ending'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td class="text-end text-green">Rp {{ number_format(collect($months)->sum('in'), 0, ',', '.') }}</td>
                    <td class="text-end text-red">Rp {{ number_format(collect($months)->sum('out'), 0, ',', '.') }}</td>
                    <td class="text-end text-blue">Rp {{ number_format(collect($months)->last()['ending'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function annualReport() {
    return {
        year: '{{ $year }}',
        init() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const months = @json($months);
            new ApexCharts(document.querySelector('#annual-chart'), {
                chart: { type: 'area', height: 350, background: 'transparent', fontFamily: 'inherit', toolbar: { show: false } },
                series: [
                    { name: 'Pemasukan', data: months.map(m => m.in) },
                    { name: 'Pengeluaran', data: months.map(m => m.out) },
                    { name: 'Saldo Akhir', data: months.map(m => m.ending) },
                ],
                xaxis: { categories: months.map(m => m.label), labels: { style: { colors: isDark ? '#c8d3e1' : '#333' } } },
                yaxis: { labels: { style: { colors: isDark ? '#c8d3e1' : '#333' }, formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                colors: ['#2fb344', '#d63939', '#206bc4'],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
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
