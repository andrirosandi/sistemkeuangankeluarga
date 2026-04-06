@extends('layouts.admin')

@section('title', 'Laporan Outstanding')

@section('page-header')
<div class="col">
    <h2 class="page-title">Laporan Outstanding</h2>
    <div class="text-secondary mt-1">Pengajuan yang belum sepenuhnya terealisasi</div>
</div>
<div class="col-auto">
    <a href="{{ route('report.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')
{{-- Filter bar --}}
@if(count($availableScopes) > 1)
<div class="card mb-4">
    <div class="card-body d-flex gap-3 align-items-center">
        <div>
            <label class="form-label mb-0 small">Cakupan</label>
            <select class="form-select form-select-sm"
                    onchange="window.location.href='?scope='+this.value">
                @foreach($availableScopes as $s)
                <option value="{{ $s['value'] }}" {{ $scope === $s['value'] ? 'selected' : '' }}>{{ $s['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
@endif

{{-- Summary --}}
@php
    $totalCount = $requested->count() + $approvedDraft->count() + $partial->count();
    $totalAmount = $requested->sum('amount') + $approvedDraft->sum('amount') + $partial->sum('amount');
@endphp

<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-orange-lt rounded p-2"><i class="ti ti-hourglass text-orange"></i></span>
                    <div>
                        <div class="text-secondary" style="font-size:0.75rem">Total Outstanding</div>
                        <div class="fw-bold">{{ $totalCount }} request</div>
                        <div class="text-secondary" style="font-size:0.7rem">@uang($totalAmount)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-yellow-lt rounded p-2"><i class="ti ti-clock text-yellow"></i></span>
                    <div>
                        <div class="text-secondary" style="font-size:0.75rem">Menunggu Approval</div>
                        <div class="fw-bold text-yellow">{{ $requested->count() }}</div>
                        <div class="text-secondary" style="font-size:0.7rem">@uang($requested->sum('amount'))</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-blue-lt rounded p-2"><i class="ti ti-wallet text-blue"></i></span>
                    <div>
                        <div class="text-secondary" style="font-size:0.75rem">Approved, Belum Direalisasikan</div>
                        <div class="fw-bold text-blue">{{ $approvedDraft->count() }}</div>
                        <div class="text-secondary" style="font-size:0.7rem">@uang($approvedDraft->sum('amount'))</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-purple-lt rounded p-2"><i class="ti ti-adjustments text-purple"></i></span>
                    <div>
                        <div class="text-secondary" style="font-size:0.75rem">Realisasi Parsial</div>
                        <div class="fw-bold text-purple">{{ $partial->count() }}</div>
                        <div class="text-secondary" style="font-size:0.7rem">@uang($partial->sum('amount'))</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Menunggu Approval --}}
@if($requested->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-yellow-lt">
        <h3 class="card-title text-yellow"><i class="ti ti-clock me-1"></i> Menunggu Approval ({{ $requested->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Oleh</th><th class="text-end">Nominal</th><th>Usia</th></tr>
            </thead>
            <tbody>
                @foreach($requested as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($r->description, 40) }}</td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end fw-bold">@uang($r->amount)</td>
                    <td>
                        @php $days = now()->diffInDays($r->created_at) @endphp
                        <span class="badge {{ $days > 7 ? 'bg-red-lt text-red' : ($days > 3 ? 'bg-yellow-lt text-yellow' : 'bg-green-lt text-green') }}">
                            {{ $days }} hari
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Approved, Belum Direalisasikan --}}
@if($approvedDraft->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-blue-lt">
        <h3 class="card-title text-blue"><i class="ti ti-wallet me-1"></i> Approved, Belum Direalisasikan ({{ $approvedDraft->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Oleh</th><th class="text-end">Nominal</th></tr>
            </thead>
            <tbody>
                @foreach($approvedDraft as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($r->description, 40) }}</td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end fw-bold">@uang($r->amount)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Realisasi Parsial --}}
@if($partial->count() > 0)
<div class="card">
    <div class="card-header bg-purple-lt">
        <h3 class="card-title text-purple"><i class="ti ti-adjustments me-1"></i> Realisasi Parsial ({{ $partial->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Oleh</th><th class="text-end">Nominal</th></tr>
            </thead>
            <tbody>
                @foreach($partial as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($r->description, 40) }}</td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end fw-bold">@uang($r->amount)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Empty state --}}
@if($totalCount === 0)
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ti ti-circle-check text-green" style="font-size:48px"></i>
        <h3 class="mt-3">Semua bersih!</h3>
        <div class="text-secondary">Tidak ada outstanding yang perlu ditindaklanjuti.</div>
    </div>
</div>
@endif
@endsection
