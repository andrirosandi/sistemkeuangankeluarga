@extends('layouts.admin')

@section('title', 'Laporan Mutasi Detail')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan Mutasi Detail</h2>
    <div class="text-secondary mt-1">Rekap transaksi lengkap layaknya rekening koran</div>
</div>
<div class="col-auto d-flex gap-2">
    @can('report.export')
    <a href="{{ route('report.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
        <i class="ti ti-file-type-pdf me-1"></i> Export PDF
    </a>
    <a href="{{ route('report.export.excel', request()->query()) }}" class="btn btn-outline-success">
        <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
    </a>
    @endcan
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body d-flex gap-3 align-items-center flex-wrap">
        <div>
            <label class="form-label mb-0 small">Periode</label>
            <input type="month" class="form-control form-control-sm" value="{{ $month }}"
                   onchange="window.location.href='?month='+this.value+'&scope={{ $scope }}&trans_code={{ $transCode }}'">
        </div>
        <div>
            <label class="form-label mb-0 small">Jenis</label>
            <select class="form-select form-select-sm"
                    onchange="window.location.href='?month={{ $month }}&scope={{ $scope }}&trans_code='+this.value">
                <option value="" {{ !$transCode ? 'selected' : '' }}>Semua</option>
                <option value="1" {{ $transCode == 1 ? 'selected' : '' }}>Kas Masuk</option>
                <option value="2" {{ $transCode == 2 ? 'selected' : '' }}>Kas Keluar</option>
            </select>
        </div>
        @if(count($availableScopes) > 1)
        <div>
            <label class="form-label mb-0 small">Cakupan</label>
            <select class="form-select form-select-sm"
                    onchange="window.location.href='?month={{ $month }}&scope='+this.value+'&trans_code={{ $transCode }}'">
                @foreach($availableScopes as $s)
                <option value="{{ $s['value'] }}" {{ $scope === $s['value'] ? 'selected' : '' }}>{{ $s['label'] }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
</div>

{{-- Transaction table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-striped table-vcenter mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th>Oleh</th>
                    <th class="text-end">Masuk</th>
                    <th class="text-end">Keluar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $i => $trx)
                <tr>
                    <td class="text-secondary">{{ $transactions->firstItem() + $i }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($trx->description, 50) }}</td>
                    <td>
                        <span class="badge" style="background:{{ $trx->category->color ?? '#6c757d' }}20; color:{{ $trx->category->color ?? '#6c757d' }}; font-size:0.7rem">
                            {{ $trx->category->name ?? '-' }}
                        </span>
                    </td>
                    <td class="text-secondary" style="font-size:0.85rem">{{ $trx->creator->name ?? '-' }}</td>
                    <td class="text-end text-green fw-bold">
                        @if($trx->trans_code == 1)
                            +@uang($trx->amount)
                        @endif
                    </td>
                    <td class="text-end text-red fw-bold">
                        @if($trx->trans_code == 2)
                            -@uang($trx->amount)
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">Tidak ada transaksi pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer d-flex align-items-center">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
