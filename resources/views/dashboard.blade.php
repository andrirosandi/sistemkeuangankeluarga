@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('page-header')
<div class="col">
    <h2 class="page-title">Dashboard</h2>
    <div class="text-secondary mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
</div>
@endsection

@section('content')

@if(auth()->user()->hasRole('admin'))
{{-- ========= ADMIN VIEW ========= --}}

<div class="row row-deck row-cards mb-4">

    {{-- Saldo Akhir --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-blue text-white avatar">
                            <i class="ti ti-wallet"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Saldo Akhir</div>
                        <div class="text-secondary" style="font-size:0.75rem">Bulan ini</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold text-blue">
                    Rp 0
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
                    Rp 0
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
                    Rp 0
                </div>
            </div>
        </div>
    </div>

    {{-- Pengajuan Pending --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-yellow text-white avatar">
                            <i class="ti ti-clock"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Pending</div>
                        <div class="text-secondary" style="font-size:0.75rem">Menunggu review</div>
                    </div>
                </div>
                <div class="mt-3 h2 mb-0 fw-bold text-yellow">
                    0
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alert: ada pengajuan pending --}}
{{-- Akan diisi secara dinamis nanti --}}
<div class="card mb-4">
    <div class="card-body text-center py-5">
        <i class="ti ti-report-analytics text-secondary" style="font-size: 48px"></i>
        <h3 class="mt-3 text-secondary">Belum ada data transaksi</h3>
        <p class="text-secondary">Mulai tambahkan data keuangan melalui menu Kas Masuk atau Kas Keluar.</p>
    </div>
</div>

@else
{{-- ========= USER VIEW (Istri/Anak) ========= --}}

<div class="row row-deck row-cards mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-blue text-white rounded">
                        <i class="ti ti-user" style="font-size:24px"></i>
                    </span>
                    <div>
                        <h3 class="mb-0">Hai, {{ auth()->user()->name }}! 👋</h3>
                        <div class="text-secondary">Selamat datang di Sistem Keuangan Keluarga.</div>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('in.request.index') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Buat Pengajuan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="ti ti-clipboard-list text-secondary" style="font-size: 48px"></i>
        <h3 class="mt-3 text-secondary">Belum ada pengajuan</h3>
        <p class="text-secondary">Klik tombol di atas untuk membuat pengajuan pertama Anda.</p>
    </div>
</div>

@endif

@endsection
