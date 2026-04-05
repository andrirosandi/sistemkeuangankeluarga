@extends('layouts.admin')

@section('title', 'Outstanding')

@section('content')
@php
    $totalCount = $requested->count() + $approvedDraft->count() + $partial->count();
    $totalAmount = $requested->sum('amount') + $approvedDraft->sum('amount') + $partial->sum('amount');
@endphp

{{-- Summary Cards --}}
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-orange-lt rounded p-2"><i class="ti ti-alert-circle text-orange"></i></span>
                    <div>
                        <div class="text-secondary" style="font-size:0.75rem">Total Outstanding</div>
                        <div class="fw-bold">{{ $totalCount }} item</div>
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
                        <div class="text-secondary" style="font-size:0.75rem">Approved, Belum Cair</div>
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

{{-- ============================================================
     Section 1: Menunggu Approval
     ============================================================ --}}
@if($requested->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-yellow-lt">
        <h3 class="card-title text-yellow"><i class="ti ti-clock me-1"></i> Menunggu Approval ({{ $requested->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th>Oleh</th>
                    <th class="text-end">Nominal</th>
                    <th>Usia</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requested as $r)
                @php
                    $typeKey = $r->trans_code == 1 ? 'in' : 'out';
                    $typeLabel = $r->trans_code == 1 ? 'Masuk' : 'Keluar';
                    $typeBg = $r->trans_code == 1 ? 'bg-green-lt text-green' : 'bg-red-lt text-red';
                    $days = now()->diffInDays($r->created_at);
                @endphp
                <tr>
                    <td><span class="badge {{ $typeBg }}">{{ $typeLabel }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>
                        <div class="font-weight-medium">{{ Str::limit($r->description, 40) }}</div>
                        @if($r->priority === 'high')
                            <span class="badge bg-danger-lt" style="font-size:0.65rem"><i class="ti ti-flame me-1"></i>High</span>
                        @endif
                    </td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end fw-bold">@uang($r->amount)</td>
                    <td>
                        <span class="badge {{ $days > 7 ? 'bg-red-lt text-red' : ($days > 3 ? 'bg-yellow-lt text-yellow' : 'bg-green-lt text-green') }}">
                            {{ $days }} hari
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            <a href="{{ route($typeKey . '.request.show', $r->id) }}" class="btn btn-icon btn-sm btn-ghost-info rounded-2" data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="ti ti-eye"></i>
                            </a>
                            @if($r->created_by !== auth()->id() || auth()->user()->can($typeKey . '.request.self-approve'))
                                @can($typeKey . '.request.approve')
                                <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-success rounded-start-2"
                                                onclick="approveRequest({{ $r->id }}, '{{ addslashes($r->description) }}', '{{ $typeKey }}')">
                                            <i class="ti ti-circle-check me-1"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split rounded-end-2" @click="open = !open" :class="{'show': open}" aria-expanded="false" aria-label="Toggle Dropdown">
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none;" x-transition>
                                            <button class="dropdown-item text-danger" @click="rejectRequest({{ $r->id }}, '{{ addslashes($r->description) }}', '{{ $typeKey }}'); open = false">
                                                <i class="ti ti-circle-x me-2"></i> Tolak Pengajuan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============================================================
     Section 2: Approved, Belum Cair
     ============================================================ --}}
@if($approvedDraft->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-blue-lt">
        <h3 class="card-title text-blue"><i class="ti ti-wallet me-1"></i> Approved, Belum Cair ({{ $approvedDraft->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th>Oleh</th>
                    <th class="text-end">Nominal</th>
                    <th>Approved</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvedDraft as $r)
                @php
                    $typeKey = $r->trans_code == 1 ? 'in' : 'out';
                    $typeLabel = $r->trans_code == 1 ? 'Masuk' : 'Keluar';
                    $typeBg = $r->trans_code == 1 ? 'bg-green-lt text-green' : 'bg-red-lt text-red';
                    $daysApproved = $r->approved_at ? now()->diffInDays($r->approved_at) : 0;
                @endphp
                <tr>
                    <td><span class="badge {{ $typeBg }}">{{ $typeLabel }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($r->description, 40) }}</td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end fw-bold">@uang($r->amount)</td>
                    <td>
                        <span class="badge {{ $daysApproved > 7 ? 'bg-red-lt text-red' : ($daysApproved > 3 ? 'bg-yellow-lt text-yellow' : 'bg-green-lt text-green') }}">
                            {{ $daysApproved }} hari lalu
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            <a href="{{ route($typeKey . '.request.show', $r->id) }}" class="btn btn-icon btn-sm btn-ghost-info rounded-2" data-bs-toggle="tooltip" title="Lihat Pengajuan">
                                <i class="ti ti-eye"></i>
                            </a>
                            @if($r->transaction)
                                @can($typeKey . '.transaction.edit')
                                <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                                    <div class="btn-group">
                                        <a href="{{ route($typeKey . '.transaction.edit', $r->transaction->id) }}" class="btn btn-sm btn-primary rounded-start-2">
                                            <i class="ti ti-pencil me-1"></i> Cairkan
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split rounded-end-2" @click="open = !open" :class="{'show': open}" aria-expanded="false" aria-label="Toggle Dropdown">
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none;" x-transition>
                                            <button class="dropdown-item text-danger" @click="deleteTransaction({{ $r->transaction->id }}, '{{ addslashes($r->description) }}', '{{ $typeKey }}'); open = false">
                                                <i class="ti ti-trash me-2"></i> Hapus Draft Realisasi
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============================================================
     Section 3: Realisasi Parsial
     ============================================================ --}}
@if($partial->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-purple-lt">
        <h3 class="card-title text-purple"><i class="ti ti-adjustments me-1"></i> Realisasi Parsial ({{ $partial->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th>Oleh</th>
                    <th class="text-end">Diajukan</th>
                    <th class="text-end">Terealisasi</th>
                    <th>Progress</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partial as $r)
                @php
                    $typeKey = $r->trans_code == 1 ? 'in' : 'out';
                    $typeLabel = $r->trans_code == 1 ? 'Masuk' : 'Keluar';
                    $typeBg = $r->trans_code == 1 ? 'bg-green-lt text-green' : 'bg-red-lt text-red';
                    $realizedAmount = $r->transaction?->amount ?? 0;
                    $totalItems = $r->details->count();
                    $realizedItems = $r->details->where('status', 'realized')->count();
                    $pct = $r->amount > 0 ? round($realizedAmount / $r->amount * 100) : 0;
                @endphp
                <tr>
                    <td><span class="badge {{ $typeBg }}">{{ $typeLabel }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($r->request_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ Str::limit($r->description, 40) }}</td>
                    <td><span class="badge" style="background:{{ $r->category->color ?? '#6c757d' }}20; color:{{ $r->category->color ?? '#6c757d' }}">{{ $r->category->name ?? '-' }}</span></td>
                    <td>{{ $r->creator->name ?? '-' }}</td>
                    <td class="text-end">@uang($r->amount)</td>
                    <td class="text-end fw-bold text-purple">@uang($realizedAmount)</td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                            <div class="progress flex-fill" style="height: 6px;">
                                <div class="progress-bar bg-purple" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="small text-muted">{{ $realizedItems }}/{{ $totalItems }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            <a href="{{ route($typeKey . '.request.show', $r->id) }}" class="btn btn-icon btn-sm btn-ghost-info rounded-2" data-bs-toggle="tooltip" title="Lihat Pengajuan">
                                <i class="ti ti-eye"></i>
                            </a>
                            @if($r->transaction)
                                <a href="{{ route($typeKey . '.transaction.show', $r->transaction->id) }}" class="btn btn-icon btn-sm btn-ghost-purple rounded-2" data-bs-toggle="tooltip" title="Lihat Realisasi">
                                    <i class="ti ti-receipt"></i>
                                </a>
                            @endif
                            @can($typeKey . '.request.approve')
                                <div class="dropdown ms-1" x-data="{ open: false }" @click.outside="open = false">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-danger rounded-start-2"
                                                onclick="writeoffRequest({{ $r->id }}, '{{ addslashes($r->description) }}', '{{ $typeKey }}')">
                                            <i class="ti ti-ban me-1" style="font-size: 0.8rem"></i> Batalkan Sisanya
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle dropdown-toggle-split rounded-end-2" @click="open = !open" :class="{'show': open}" aria-expanded="false" aria-label="Toggle Dropdown">
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none;" x-transition>
                                            @if($r->transaction && $r->transaction->status === 'completed')
                                            <button class="dropdown-item text-warning" @click="cancelTransaction({{ $r->transaction->id }}, '{{ addslashes($r->description) }}', '{{ $typeKey }}'); open = false">
                                                <i class="ti ti-rotate-2 me-2"></i> Batalkan Pencairan
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Empty State --}}
@if($totalCount === 0)
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ti ti-circle-check text-green" style="font-size:48px"></i>
        <h3 class="mt-3">Semua bersih!</h3>
        <div class="text-secondary">Tidak ada outstanding yang perlu ditindaklanjuti.</div>
    </div>
</div>
@endif

{{-- Modal Approve --}}
<div class="modal modal-blur fade" id="modal-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <span class="avatar avatar-xl rounded-circle bg-success-lt">
                        <i class="ti ti-circle-check text-success" style="font-size: 40px;"></i>
                    </span>
                </div>
                <h3 class="mb-1">Setujui Pengajuan</h3>
                <div class="text-secondary px-2">Apakah Anda yakin ingin menyetujui pengajuan <strong class="text-dark" id="approve-name"></strong>? Sistem akan segera membuat draf realisasi dana.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a href="#" class="btn btn-link link-secondary w-100" data-bs-dismiss="modal">
                                Batal
                            </a>
                        </div>
                        <div class="col">
                            <form id="form-approve" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-check me-1"></i> Ya, Setujui
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div class="modal modal-blur fade" id="modal-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <form id="form-reject" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <span class="avatar avatar-xl rounded-circle bg-danger-lt">
                                <i class="ti ti-circle-x text-danger" style="font-size: 40px;"></i>
                            </span>
                        </div>
                        <h3 class="mb-1">Tolak Pengajuan</h3>
                        <div class="text-secondary">Berikan alasan mengapa pengajuan <strong class="text-dark" id="reject-name"></strong> ini ditolak.</div>
                    </div>
                    <div class="card p-3 bg-light-lt border-dashed">
                        <label class="form-label required">Alasan Penolakan</label>
                        <textarea class="form-control border-0 bg-transparent p-0" name="rejection_reason" rows="3" placeholder="Contoh: Lampiran kurang lengkap atau nominal tidak sesuai..." style="resize: none;" required autofocus></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i> Tolak Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Writeoff --}}
<div class="modal modal-blur fade" id="modal-writeoff" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <span class="avatar avatar-xl rounded-circle bg-danger-lt">
                        <i class="ti ti-ban text-danger" style="font-size: 40px;"></i>
                    </span>
                </div>
                <h3 class="mb-1">Batalkan Sisa Pengajuan</h3>
                <div class="text-secondary px-2">Apakah Anda yakin ingin menutup sisa pengajuan <strong class="text-dark" id="writeoff-name"></strong> yang belum terealisasi?</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a href="#" class="btn btn-link link-secondary w-100" data-bs-dismiss="modal">
                                Batal
                            </a>
                        </div>
                        <div class="col">
                            <form id="form-writeoff" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ti ti-ban me-1"></i> Ya, Batalkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Cancel Transaction --}}
<div class="modal modal-blur fade" id="modal-cancel-trx" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-warning"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-rotate-2 text-warning icon-lg mb-2"></i>
                <h3>Batalkan Pencairan</h3>
                <div class="text-secondary px-2">Batalkan pencairan dana untuk <strong id="cancel-trx-name"></strong>? Realisasi akan kembali menjadi Draft.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Kembali</a></div>
                        <div class="col">
                            <form id="form-cancel-trx" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">Batalkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-datatable.delete-modal title="Hapus Draft Realisasi" message="Hapus draft realisasi untuk pengajuan <strong id='delete-trx-name'></strong>? Status pengajuan akan dikembalikan menjadi <strong>Requested</strong>." />
@endsection

@push('scripts')
<script>
    function approveRequest(id, name, typeKey) {
        var prefix = typeKey === 'in' ? 'kas-masuk' : 'kas-keluar';
        document.getElementById('form-approve').action = `/${prefix}/pengajuan/${id}/approve`;
        document.getElementById('approve-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-approve')).show();
    }
    
    function rejectRequest(id, name, typeKey) {
        var prefix = typeKey === 'in' ? 'kas-masuk' : 'kas-keluar';
        document.getElementById('form-reject').action = `/${prefix}/pengajuan/${id}/reject`;
        document.getElementById('reject-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-reject')).show();
    }
    
    function writeoffRequest(id, name, typeKey) {
        var prefix = typeKey === 'in' ? 'kas-masuk' : 'kas-keluar';
        document.getElementById('form-writeoff').action = `/${prefix}/pengajuan/${id}/writeoff`;
        document.getElementById('writeoff-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-writeoff')).show();
    }

    function cancelTransaction(id, name, typeKey) {
        var prefix = typeKey === 'in' ? 'kas-masuk' : 'kas-keluar';
        document.getElementById('form-cancel-trx').action = `/${prefix}/realisasi/${id}/cancel`;
        document.getElementById('cancel-trx-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-cancel-trx')).show();
    }

    function deleteTransaction(id, name, typeKey) {
        var prefix = typeKey === 'in' ? 'kas-masuk' : 'kas-keluar';
        document.getElementById('form-delete').action = `/${prefix}/realisasi/${id}`;
        document.getElementById('delete-trx-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }
</script>
@endpush
