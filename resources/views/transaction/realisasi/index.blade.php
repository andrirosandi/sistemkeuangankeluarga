@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h3 class="card-title">Daftar {{ $title }}</h3>
                @can($type . '.transaction.create')
                <div class="card-actions">
                    <!-- Desktop View -->
                    <!-- Desktop View -->
                    <div class="btn-group d-none d-sm-inline-flex" x-data="{ open: false }" @click.outside="open = false" style="position: relative;">
                        <a href="{{ route($type . '.transaction.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Buat Realisasi Baru
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split border-start border-light" @click="open = !open" :class="{'show': open}" aria-expanded="false">
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.125rem; z-index: 1020;" x-transition>
                            @if(isset($templates) && $templates->count() > 0)
                                <h6 class="dropdown-header">Gunakan Template</h6>
                                @foreach($templates as $tmpl)
                                    <a class="dropdown-item" href="{{ route($type . '.transaction.create', ['template_id' => $tmpl->id]) }}">
                                        <i class="ti ti-copy me-2 text-muted"></i> {{ $tmpl->description }}
                                    </a>
                                @endforeach
                            @else
                                <span class="dropdown-item text-muted">Belum ada template</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Mobile View -->
                    <div class="btn-group d-sm-none" x-data="{ open: false }" @click.outside="open = false" style="position: relative;">
                        <a href="{{ route($type . '.transaction.create') }}" class="btn btn-primary btn-icon">
                            <i class="ti ti-plus"></i>
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split btn-icon border-start border-light" @click="open = !open" :class="{'show': open}" aria-expanded="false" style="padding-left: 5px; padding-right: 5px;">
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.125rem; z-index: 1020;" x-transition>
                            @if(isset($templates) && $templates->count() > 0)
                                <h6 class="dropdown-header">Gunakan Template</h6>
                                @foreach($templates as $tmpl)
                                    <a class="dropdown-item" href="{{ route($type . '.transaction.create', ['template_id' => $tmpl->id]) }}">
                                        <i class="ti ti-copy me-2 text-muted"></i> {{ $tmpl->description }}
                                    </a>
                                @endforeach
                            @else
                                <span class="dropdown-item text-muted">Belum ada template</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            <!-- Controls -->
            <x-datatable.controls :perPage="20" searchLabel="Search transaction" />

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="sort" data-sort="sort-desc"><button class="table-sort" data-sort="sort-desc">Deskripsi</button></th>
                            <th class="sort" data-sort="sort-date"><button class="table-sort" data-sort="sort-date">Tanggal</button></th>
                            <th class="sort text-end" data-sort="sort-amount"><button class="table-sort" data-sort="sort-amount">Nominal</button></th>
                            <th class="sort" data-sort="sort-status"><button class="table-sort" data-sort="sort-status">Status</button></th>
                            <th>Sumber</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($transactions as $trx)
                        <tr>
                            <td class="sort-desc" data-desc="{{ $trx->description }}">
                                <div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $trx->description }}</div>
                                        <div class="text-muted"><small>{{ $trx->category->name ?? '-' }} &middot; {{ $trx->creator->name ?? '-' }}</small></div>
                                    </div>
                                </div>
                            </td>
                            <td class="sort-date" data-date="{{ \Carbon\Carbon::parse($trx->transaction_date)->timestamp }}">
                                {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                            </td>
                            <td class="sort-amount text-end fw-bold" data-amount="{{ $trx->amount }}">
                                @uang($trx->amount)
                            </td>
                            <td class="sort-status" data-status="{{ $trx->status }}">
                                @if($trx->status == 'draft') <span class="badge bg-warning">Draft</span>
                                @elseif($trx->status == 'completed') <span class="badge bg-success">Completed</span>
                                @elseif($trx->status == 'canceled') <span class="badge bg-dark">Canceled</span>
                                @endif
                            </td>
                            <td>
                                @if($trx->request_id)
                                    <a href="{{ route($type . '.request.show', $trx->request_id) }}" class="badge bg-blue-lt text-decoration-none" data-bs-toggle="tooltip" title="Lihat Pengajuan Asal">
                                        <i class="ti ti-file-text me-1"></i>Pengajuan
                                    </a>
                                @else
                                    <span class="badge bg-secondary-lt">Langsung</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route($type . '.transaction.show', $trx->id) }}" class="btn btn-icon btn-sm btn-ghost-info rounded-2" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>

                                    @if($trx->status === 'draft')
                                        @can($type . '.transaction.edit')
                                        <a href="{{ route($type . '.transaction.edit', $trx->id) }}" class="btn btn-icon btn-sm btn-ghost-primary rounded-2" data-bs-toggle="tooltip" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </a>

                                        <button class="btn btn-icon btn-sm btn-ghost-success rounded-2"
                                                onclick="completeTransaction({{ $trx->id }}, '{{ addslashes($trx->description) }}')"
                                                data-bs-toggle="tooltip"
                                                title="Cairkan Dana">
                                            <i class="ti ti-cash"></i>
                                        </button>
                                        @endcan

                                        @can($type . '.transaction.delete')
                                        <button class="btn btn-icon btn-sm btn-ghost-danger rounded-2"
                                                onclick="deleteTransaction({{ $trx->id }}, '{{ addslashes($trx->description) }}')"
                                                data-bs-toggle="tooltip"
                                                title="Hapus Draft">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        @endcan
                                    @endif

                                    @if($trx->status === 'completed')
                                        @can($type . '.transaction.edit')
                                        <button class="btn btn-icon btn-sm btn-ghost-warning rounded-2"
                                                onclick="cancelTransaction({{ $trx->id }}, '{{ addslashes($trx->description) }}')"
                                                data-bs-toggle="tooltip"
                                                title="Batalkan (Kembali ke Draft)">
                                            <i class="ti ti-rotate-2"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon text-secondary">
                                        <i class="ti ti-wallet-off icon-lg"></i>
                                    </div>
                                    <p class="empty-title">Belum ada realisasi</p>
                                    <p class="empty-subtitle text-secondary">Realisasi otomatis dibuat saat pengajuan disetujui.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-datatable.pagination :total="$transactions->count()" :perPage="20" />
        </div>
    </div>
</div>

{{-- Modal Complete --}}
<div class="modal modal-blur fade" id="modal-complete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-cash text-success icon-lg mb-2"></i>
                <h3>Cairkan Dana</h3>
                <div class="text-secondary">Cairkan dana untuk <strong id="complete-name"></strong>? Saldo akan otomatis diperbarui.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form id="form-complete" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Cairkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cancel --}}
<div class="modal modal-blur fade" id="modal-cancel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-warning"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-rotate-2 text-warning icon-lg mb-2"></i>
                <h3>Batalkan Pencairan</h3>
                <div class="text-secondary">Batalkan pencairan dana <strong id="cancel-name"></strong>? Realisasi akan kembali menjadi Draft dan saldo akan dikembalikan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Kembali</a></div>
                        <div class="col">
                            <form id="form-cancel" method="POST">
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

<x-datatable.delete-modal title="Hapus Realisasi" message="Hapus draft realisasi <strong id='delete-name'></strong>? Jika berasal dari pengajuan, status akan dikembalikan menjadi <strong>Requested</strong>." />
@endsection

@push('scripts')
<script>
    const baseUrl = `{{ url('kas-' . ($type == 'in' ? 'masuk' : 'keluar') . '/realisasi') }}`;

    function completeTransaction(id, name) {
        document.getElementById('form-complete').action = `${baseUrl}/${id}/complete`;
        document.getElementById('complete-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-complete')).show();
    }

    function cancelTransaction(id, name) {
        document.getElementById('form-cancel').action = `${baseUrl}/${id}/cancel`;
        document.getElementById('cancel-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-cancel')).show();
    }

    function deleteTransaction(id, name) {
        document.getElementById('form-delete').action = `${baseUrl}/${id}`;
        document.getElementById('delete-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }
</script>
<x-datatable.list-init :valueNames="[
    { name: 'sort-desc', attr: 'data-desc' },
    { name: 'sort-date', attr: 'data-date' },
    { name: 'sort-amount', attr: 'data-amount' },
    { name: 'sort-status', attr: 'data-status' }
]" :perPage="20" listVar="trxList" />
@endpush
