@extends('layouts.admin')

@section('title', 'Laporan & Analitik')
@section('page-title', 'Laporan & Analitik')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan & Analitik</h2>
    <div class="text-secondary mt-1">Pilih jenis laporan yang ingin dilihat</div>
</div>
@endsection

@section('content')
<div class="row row-deck row-cards">

    {{-- R1: Laporan Tahunan --}}
    @if($isAdmin)
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.annual') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-primary-lt text-primary rounded">
                        <i class="ti ti-chart-line" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Laporan Tahunan</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Trend pemasukan vs pengeluaran 12 bulan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- R2: Per Kategori --}}
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.category') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-green-lt text-green rounded">
                        <i class="ti ti-chart-donut-3" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Per Kategori</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Distribusi pengeluaran per kategori</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- R3: Mutasi Detail --}}
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.mutation') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-cyan-lt text-cyan rounded">
                        <i class="ti ti-list-details" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Mutasi Detail</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Rekening koran lengkap @if($isAdmin) + export @endif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- R4: Efisiensi --}}
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.efficiency') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-yellow-lt text-yellow rounded">
                        <i class="ti ti-scale" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Realisasi vs Pengajuan</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Rasio efisiensi & penghematan budget</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- R5: Outstanding --}}
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.outstanding') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-orange-lt text-orange rounded">
                        <i class="ti ti-hourglass" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Outstanding</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Pengajuan belum sepenuhnya terealisasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- R6: Per Anggota (Admin Only) --}}
    @if($isAdmin)
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.per-member') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-purple-lt text-purple rounded">
                        <i class="ti ti-users-group" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Per Anggota</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Ranking & detail per anggota keluarga</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- R7: Pemasukan --}}
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm card-link" onclick="location.href='{{ route('report.income') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-teal-lt text-teal rounded">
                        <i class="ti ti-cash" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">Pemasukan</h3>
                        <div class="text-secondary" style="font-size:0.85rem">Detail kas masuk: gaji, hutang dibayar, dll</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .card-link { cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; }
    .card-link:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>
@endpush
