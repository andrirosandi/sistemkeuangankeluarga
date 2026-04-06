@extends('layouts.admin')

@section('title', $title)

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title">{{ $title }}</h2>
        <div class="text-secondary mt-1">Laporan histori pergerakan kas keuangan.</div>
    </div>
</div>
@endsection

@section('content')
<div class="row row-cards">
    <!-- Filter Section -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('mutation.index') }}" method="GET" class="d-flex align-items-end gap-3 flex-wrap">
                    <div>
                        <label class="form-label text-muted small mb-1">Periode Bulan</label>
                        <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" max="{{ date('Y-m') }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="ti ti-filter me-1"></i> Tampilkan
                        </button>
                        <a href="{{ route('mutation.index') }}" class="btn btn-link link-secondary btn-sm ms-2">Bulan Ini</a>
                    </div>
                    <div class="ms-auto mt-3 mt-md-0">
                        <a href="{{ route('mutation.print', ['month' => $month]) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-printer me-1"></i> Cetak Buku Kas
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="col-12">
        <div class="row row-cards">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-secondary text-white avatar"><i class="ti ti-wallet"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Saldo Awal</div>
                                <div class="text-secondary fw-bold">@uang($beginBalance)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar"><i class="ti ti-arrow-down-left"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Kas Masuk</div>
                                <div class="text-success fw-bold">+ @uang($totalIn)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar"><i class="ti ti-arrow-up-right"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Kas Keluar</div>
                                <div class="text-danger fw-bold">- @uang($totalOut)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm border-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar"><i class="ti ti-report-money"></i></span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Saldo Akhir</div>
                                <div class="text-primary fw-bold" style="font-size: 1.1em;">@uang($endBalance)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rincian Buku Kas Umum - {{ \Carbon\Carbon::parse($monthDate)->translatedFormat('F Y') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Tanggal</th>
                            <th>Keterangan Transaksi</th>
                            <th class="text-end">Debet (Masuk)</th>
                            <th class="text-end">Kredit (Keluar)</th>
                            <th class="text-end border-start border-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Begin Balance Row -->
                        <tr class="bg-primary-lt font-weight-bold">
                            <td></td>
                            <td class="text-muted">{{ $monthDate->startOfMonth()->format('d/m/Y') }}</td>
                            <td class="fw-bold" colspan="3">SALDO AWAL BULAN INI</td>
                            <td class="text-end fw-bold border-start border-end">@uang($beginBalance)</td>
                        </tr>

                        <!-- Mutations -->
                        @forelse($mutations as $index => $mut)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($mut->date)->format('d/m/Y') }}</td>
                            <td>
                                <div>{{ $mut->description }}</div>
                                <div class="text-muted small">
                                    <span class="badge badge-outline mt-1" style="color: {{ $mut->color }}; border-color: {{ $mut->color }};">{{ $mut->category }}</span>
                                    • Oleh: {{ $mut->creator }}
                                </div>
                            </td>
                            <td class="text-end text-success">
                                @if($mut->debit > 0)+ @uang($mut->debit)@else-@endif
                            </td>
                            <td class="text-end text-danger">
                                @if($mut->credit > 0)- @uang($mut->credit)@else-@endif
                            </td>
                            <td class="text-end font-monospace tracking-wide border-start border-end fw-medium">
                                @uang($mut->balance)
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">Tidak ada transaksi yang direalisasikan pada bulan ini.</div>
                            </td>
                        </tr>
                        @endforelse

                        <!-- End Balance Row (Footer Info) -->
                        <tr class="bg-blue-lt">
                            <td colspan="3" class="text-end fw-bold">TOTAL MUTASI</td>
                            <td class="text-end fw-bold text-success">@uang($totalIn)</td>
                            <td class="text-end fw-bold text-danger">@uang($totalOut)</td>
                            <td class="border-start border-end"></td>
                        </tr>
                        <tr class="bg-primary-lt">
                            <td colspan="5" class="text-end fw-bold text-uppercase">SALDO AKHIR BULAN INI</td>
                            <td class="text-end fw-bold fs-3 text-primary border-start border-end">@uang($endBalance)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
