@extends('layouts.admin')

@section('title', $title)

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title">{{ $title }}</h2>
        <div class="text-muted mt-1">Diajukan oleh: <strong>{{ $requestData->creator->name ?? '-' }}</strong> pada {{ \Carbon\Carbon::parse($requestData->request_date)->format('d F Y') }}</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="ti ti-printer me-2"></i> Cetak
            </button>
            <a href="{{ route($type . '.request.index') }}" class="btn btn-primary d-none d-sm-inline-block">
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
                @if($requestData->status == 'draft') bg-secondary
                @elseif($requestData->status == 'requested') bg-warning
                @elseif($requestData->status == 'approved') bg-success
                @elseif($requestData->status == 'rejected') bg-danger
                @elseif($requestData->status == 'canceled') bg-dark
                @endif
            "></div>
            <div class="card-header">
                <h3 class="card-title">Status Pengajuan</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="h1 mb-0 me-3">
                        @if($requestData->status == 'draft') <i class="ti ti-file-pencil text-secondary"></i>
                        @elseif($requestData->status == 'requested') <i class="ti ti-clock text-warning"></i>
                        @elseif($requestData->status == 'approved') <i class="ti ti-circle-check text-success"></i>
                        @elseif($requestData->status == 'rejected') <i class="ti ti-circle-x text-danger"></i>
                        @elseif($requestData->status == 'canceled') <i class="ti ti-ban text-dark"></i>
                        @endif
                    </div>
                    <div>
                        <div class="h4 m-0">
                            {{ strtoupper($requestData->status) }}
                        </div>
                        <div class="text-muted">
                            @if($requestData->priority == 'high') <span class="badge bg-danger-lt mt-1">High Priority</span>
                            @elseif($requestData->priority == 'normal') <span class="badge bg-blue-lt mt-1">Normal Priority</span>
                            @else <span class="badge bg-secondary-lt mt-1">Low Priority</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($requestData->status == 'rejected' && $requestData->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0">
                        <strong>Alasan Penolakan:</strong><br>
                        {{ $requestData->rejection_reason }}
                        <div class="mt-2 small text-muted">Aksi oleh: {{ $requestData->approver->name ?? '-' }}</div>
                    </div>
                @endif
                
                @if($requestData->status == 'approved')
                    <div class="alert alert-success mt-3 mb-0">
                        Pengajuan telah disetujui pada {{ \Carbon\Carbon::parse($requestData->approved_at)->format('d/m/Y H:i') }}
                        oleh {{ $requestData->approver->name ?? '-' }}
                    </div>
                @endif

                @if($requestData->status == 'requested')
                    <div class="mt-3 text-muted small d-none d-print-block">
                        Menunggu persetujuan.
                    </div>
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
                            <td class="font-weight-bold">{{ $requestData->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deskripsi</td>
                            <td class="font-weight-bold">{{ $requestData->description }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Catatan Tambahan</td>
                            <td class="font-weight-bold">{{ $requestData->notes ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Lampiran Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lampiran Bukti</h3>
            </div>
            <div class="card-body">
                @if($requestData->hasMedia('requests'))
                    <div class="list-group list-group-flush">
                        @foreach($requestData->getMedia('requests') as $mediaItem)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div class="text-truncate me-2" style="max-width: 200px;" title="{{ $mediaItem->file_name }}">
                                    <i class="ti ti-paperclip me-2 text-muted"></i>
                                    {{ $mediaItem->file_name }}
                                </div>
                                <div>
                                    <a href="{{ $mediaItem->getUrl() }}" target="_blank" class="btn btn-sm btn-icon btn-outline-info" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ $mediaItem->getUrl() }}" download="{{ $mediaItem->file_name }}" class="btn btn-sm btn-icon btn-primary ms-1" title="Download">
                                        <i class="ti ti-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted text-center py-3">
                        <i class="ti ti-file-off h1 mb-2"></i><br>
                        Tidak ada berkas yang dilampirkan.
                    </div>
                @endif
            </div>
        </div>
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
                        @foreach($requestData->details as $index => $item)
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
                <div class="text-secondary small fw-bold text-uppercase tracking-wide">Total Pengajuan</div>
                <div class="h2 mb-0 text-primary">@uang($requestData->amount)</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($requestData->status == 'requested')
                    @if($requestData->created_by === auth()->id())
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modal-cancel-show">
                            <i class="ti ti-ban me-1"></i> Batalkan Pengajuan
                        </button>
                    @endif
                    @can($type . '.request.approve')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-reject-show">
                            <i class="ti ti-circle-x me-1"></i> Tolak
                        </button>
                        <form action="{{ route($type . '.request.approve', $requestData->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success shadow-sm" onclick="return confirm('Setujui pengajuan ini? Draf realisasi akan otomatis dibuat.')">
                                <i class="ti ti-circle-check me-1"></i> Setujui
                            </button>
                        </form>
                    @endcan
                @endif
                @if($requestData->status == 'draft')
                    <a href="{{ route($type . '.request.edit', $requestData->id) }}" class="btn btn-primary shadow-sm">
                        <i class="ti ti-pencil me-2"></i> Edit Pengajuan Ini
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@if($requestData->status == 'requested')
{{-- Modal Reject (Show Page) --}}
<div class="modal modal-blur fade" id="modal-reject-show" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <form action="{{ route($type . '.request.reject', $requestData->id) }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <i class="ti ti-circle-x text-danger icon-lg mb-2"></i>
                        <h3>Tolak Pengajuan</h3>
                        <div class="text-secondary">Tolak pengajuan <strong>{{ $requestData->description }}</strong>?</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Alasan Penolakan</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</a>
                    <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cancel (Show Page) --}}
<div class="modal modal-blur fade" id="modal-cancel-show" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-dark"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-ban text-dark icon-lg mb-2"></i>
                <h3>Batalkan Pengajuan</h3>
                <div class="text-secondary">Batalkan pengajuan <strong>{{ $requestData->description }}</strong>? Data akan tetap tersimpan sebagai riwayat pembatalan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Tutup</a></div>
                        <div class="col">
                            <form action="{{ route($type . '.request.cancel', $requestData->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-dark w-100">Batalkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
