@extends('layouts.admin')

@section('title', $title)

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title">{{ $title }}</h2>
        <div class="text-muted mt-1">Diproses oleh: <strong>{{ $transaction->creator->name ?? '-' }}</strong> pada {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') }}</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="ti ti-printer me-2"></i> Cetak
            </button>
            <a href="{{ route($type . '.transaction.index') }}" class="btn btn-primary d-none d-sm-inline-block">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row row-cards">
    <!-- Kolom Kiri: Info & Status -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card mb-3">
            <div class="card-status-top 
                @if($transaction->status == 'draft') bg-warning
                @elseif($transaction->status == 'completed') bg-success
                @endif
            "></div>
            <div class="card-header">
                <h3 class="card-title">Status Realisasi</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="h1 mb-0 me-3">
                        @if($transaction->status == 'draft') <i class="ti ti-clock text-warning"></i>
                        @elseif($transaction->status == 'completed') <i class="ti ti-circle-check text-success"></i>
                        @endif
                    </div>
                    <div>
                        <div class="h4 m-0">
                            @if($transaction->status == 'draft') Menunggu Pencairan
                            @elseif($transaction->status == 'completed') Dana Sudah Cair
                            @endif
                        </div>
                        <div class="text-muted">
                            <span class="badge bg-{{ $transaction->status == 'draft' ? 'warning' : 'success' }}">
                                {{ strtoupper($transaction->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($transaction->status === 'draft')
                    @can($type . '.transaction.edit')
                    <div class="mt-3 text-muted small d-none d-print-block">
                        Menunggu pencairan dana.
                    </div>
                    @endcan
                @endif
            </div>
        </div>

        <!-- Info Header Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Utama</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-vcenter card-table">
                    <tbody>
                        <tr>
                            <td class="text-muted w-50">Kategori</td>
                            <td class="font-weight-bold">{{ $transaction->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deskripsi</td>
                            <td class="font-weight-bold">{{ $transaction->description }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Catatan</td>
                            <td class="font-weight-bold">{{ $transaction->notes ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Realisasi</td>
                            <td class="font-weight-bold">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pengajuan Asal Card -->
        @if($transaction->request_id && $transaction->requestHeader)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Asal</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-sm bg-blue-lt rounded">
                            <i class="ti ti-file-text"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold">{{ $transaction->requestHeader->description }}</div>
                        <div class="text-muted small">Oleh: {{ $transaction->requestHeader->creator->name ?? '-' }}</div>
                    </div>
                    <a href="{{ route($type . '.request.show', $transaction->request_id) }}" class="btn btn-sm btn-outline-primary">
                        Lihat
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-4 bg-muted-lt">
                <i class="ti ti-edit-circle icon-lg text-secondary mb-2"></i>
                <p class="text-secondary mb-0">Realisasi ini merupakan input langsung (tanpa melalui tahapan pengajuan).</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Kolom Kanan: Rincian Item -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rincian Item</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                        <tr>
                            <th class="w-1">#</th>
                            <th>Deskripsi Item</th>
                            <th class="text-end" style="width: 200px;">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->details as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">@uang($item->amount)</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Floating Bottom Bar -->
<div class="position-sticky bottom-0 pb-3 mt-3 d-print-none" style="z-index: 1020;">
    <div class="card shadow-lg mb-0 border-primary border-opacity-25">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small fw-bold text-uppercase tracking-wide">Total Realisasi</div>
                <div class="h2 mb-0 text-primary">@uang($transaction->amount)</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($transaction->status == 'draft')
                    @can($type . '.transaction.edit')
                        <a href="{{ route($type . '.transaction.edit', $transaction->id) }}" class="btn btn-primary shadow-sm">
                            <i class="ti ti-pencil me-2"></i> Edit Realisasi Ini
                        </a>
                        <form action="{{ route($type . '.transaction.complete', $transaction->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success shadow-sm" onclick="return confirm('Cairkan dana ini? Saldo akan otomatis diperbarui.')">
                                <i class="ti ti-cash me-1"></i> Cairkan Dana
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
