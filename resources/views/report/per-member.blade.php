@extends('layouts.admin')

@section('title', 'Laporan per Anggota')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan per Anggota</h2>
    <div class="text-secondary mt-1">Ranking & detail per anggota keluarga</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div x-data="memberReport()">
    <div class="card mb-4">
        <div class="card-body">
            <input type="month" class="form-control form-control-sm" value="{{ $month }}" style="width:auto"
                   onchange="window.location.href='?month='+this.value">
        </div>
    </div>
    <div class="row row-deck row-cards">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-bar me-1"></i> Perbandingan</h3></div>
                <div class="card-body">
                    @if(empty($members))
                        <div class="text-center text-secondary py-4">Belum ada data</div>
                    @else
                        <div id="member-chart" style="min-height:300px"></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-trophy me-1"></i> Ranking</h3></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-vcenter mb-0">
                        <thead><tr><th>#</th><th>Nama</th><th>Grup</th><th class="text-end">Req</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th><th class="text-end">Net</th></tr></thead>
                        <tbody>
                            @forelse($members as $i => $m)
                            <tr>
                                <td><span class="badge {{ $i === 0 ? 'bg-red-lt' : ($i === count($members)-1 ? 'bg-green-lt' : '') }}">{{ $i+1 }}</span></td>
                                <td class="fw-medium">{{ $m['name'] }}</td>
                                <td><span class="badge bg-secondary-lt">{{ ucfirst($m['role']) }}</span></td>
                                <td class="text-end">{{ $m['requestCount'] }}</td>
                                <td class="text-end text-green">Rp {{ number_format($m['totalIn'], 0, ',', '.') }}</td>
                                <td class="text-end text-red">Rp {{ number_format($m['totalOut'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold {{ $m['net'] >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($m['net'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-secondary py-3">Belum ada data</td></tr>
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
function memberReport() {
    return {
        init() {
            const m = @json($members);
            if (!m.length) return;
            const dk = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            new ApexCharts(document.querySelector('#member-chart'), {
                chart: { type: 'bar', height: 300, background: 'transparent', fontFamily: 'inherit', toolbar: { show: false } },
                series: [{ name: 'Masuk', data: m.map(x=>x.totalIn) }, { name: 'Keluar', data: m.map(x=>x.totalOut) }],
                xaxis: { categories: m.map(x=>x.name), labels: { style: { colors: dk?'#c8d3e1':'#333' } } },
                yaxis: { labels: { style: { colors: dk?'#c8d3e1':'#333' }, formatter: v=>'Rp '+new Intl.NumberFormat('id-ID').format(v) } },
                colors: ['#2fb344','#d63939'], plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                dataLabels: { enabled: false }, legend: { position: 'top', labels: { colors: dk?'#c8d3e1':'#333' } },
                tooltip: { y: { formatter: v=>'Rp '+new Intl.NumberFormat('id-ID').format(v) } },
                grid: { borderColor: dk?'#2c3e50':'#e9ecef' }, theme: { mode: dk?'dark':'light' }
            }).render();
        }
    };
}
</script>
@endpush
