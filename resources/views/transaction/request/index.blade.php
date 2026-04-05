@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <!-- Header -->
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h3 class="card-title">Daftar {{ $title }}</h3>
                <div class="card-actions">
                    @can($type . '.request.create')
                    <!-- Desktop View -->
                    <div class="btn-group d-none d-sm-inline-flex" x-data="{ open: false }" @click.outside="open = false" style="position: relative;">
                        <a href="{{ route($type . '.request.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Buat Pengajuan
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split border-start border-light" @click="open = !open" :class="{'show': open}" aria-expanded="false">
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.125rem; z-index: 1020;" x-transition>
                            @if(isset($templates) && $templates->count() > 0)
                                <h6 class="dropdown-header">Gunakan Template</h6>
                                @foreach($templates as $tmpl)
                                    <a class="dropdown-item" href="{{ route($type . '.request.create', ['template_id' => $tmpl->id]) }}">
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
                        <a href="{{ route($type . '.request.create') }}" class="btn btn-primary btn-icon" aria-label="Buat Pengajuan">
                            <i class="ti ti-plus"></i>
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split btn-icon border-start border-light" @click="open = !open" :class="{'show': open}" aria-expanded="false" style="padding-left: 5px; padding-right: 5px;">
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.125rem; z-index: 1020;" x-transition>
                            @if(isset($templates) && $templates->count() > 0)
                                <h6 class="dropdown-header">Gunakan Template</h6>
                                @foreach($templates as $tmpl)
                                    <a class="dropdown-item" href="{{ route($type . '.request.create', ['template_id' => $tmpl->id]) }}">
                                        <i class="ti ti-copy me-2 text-muted"></i> {{ $tmpl->description }}
                                    </a>
                                @endforeach
                            @else
                                <span class="dropdown-item text-muted">Belum ada template</span>
                            @endif
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Controls -->
            <x-datatable.controls :perPage="20" searchLabel="Search request" />

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all" id="select-all"></th>
                            <th class="sort" data-sort="sort-desc"><button class="table-sort" data-sort="sort-desc">Deskripsi</button></th>
                            <th class="sort" data-sort="sort-date"><button class="table-sort" data-sort="sort-date">Tanggal</button></th>
                            <th class="sort" data-sort="sort-amount"><button class="table-sort" data-sort="sort-amount">Nominal</button></th>
                            <th class="sort" data-sort="sort-priority"><button class="table-sort" data-sort="sort-priority">Prioritas</button></th>
                            <th class="sort" data-sort="sort-status"><button class="table-sort" data-sort="sort-status">Status</button></th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($requests as $req)
                        <tr>
                            <td><input class="form-check-input m-0 align-middle check-item" type="checkbox" value="{{ $req->id }}" aria-label="Select request"></td>
                            <td class="sort-desc" data-desc="{{ $req->description }}">
                                <div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $req->description }}</div>
                                        <div class="text-muted"><small>{{ $req->category->name ?? '-' }} (Oleh: {{ $req->creator->name ?? '-' }})</small></div>
                                    </div>
                                </div>
                            </td>
                            <td class="sort-date" data-date="{{ \Carbon\Carbon::parse($req->request_date)->timestamp }}">
                                {{ \Carbon\Carbon::parse($req->request_date)->format('d M Y') }}
                            </td>
                            <td class="sort-amount" data-amount="{{ $req->amount }}">
                                @uang($req->amount)
                            </td>
                            <td class="sort-priority" data-priority="{{ $req->priority }}">
                                @if($req->priority == 'high') <span class="badge bg-danger-lt">High</span>
                                @elseif($req->priority == 'normal') <span class="badge bg-blue-lt">Normal</span>
                                @else <span class="badge bg-secondary-lt">Low</span>
                                @endif
                            </td>
                            <td class="sort-status" data-status="{{ $req->status }}">
                                @if($req->status == 'draft') <span class="badge bg-secondary">Draft</span>
                                @elseif($req->status == 'requested') <span class="badge bg-warning">Requested</span>
                                @elseif($req->status == 'approved') <span class="badge bg-success">Approved</span>
                                @elseif($req->status == 'rejected') <span class="badge bg-danger">Rejected</span>
                                @elseif($req->status == 'canceled') <span class="badge bg-dark">Canceled</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-2" data-label="Aksi">
                                    <a href="{{ route($type . '.request.show', $req->id) }}" class="btn btn-icon btn-sm btn-ghost-info rounded-2" data-bs-toggle="tooltip" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    
                                    @if($req->status === 'draft')
                                        @can($type . '.request.edit')
                                        <a href="{{ route($type . '.request.edit', $req->id) }}" class="btn btn-icon btn-sm btn-ghost-primary rounded-2" data-bs-toggle="tooltip" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </a>

                                        <button class="btn btn-icon btn-sm btn-ghost-success rounded-2" 
                                                onclick="submitRequest({{ $req->id }}, '{{ addslashes($req->description) }}')" 
                                                data-bs-toggle="tooltip" 
                                                title="Ajukan">
                                            <i class="ti ti-send"></i>
                                        </button>
                                        @endcan
                                        
                                        @can($type . '.request.delete')
                                        <x-datatable.row-action 
                                            type="delete" 
                                            onclick="deleteRequest({{ $req->id }}, '{{ addslashes($req->description) }}')" 
                                            title="Hapus Draft" />
                                        @endcan
                                    @endif

                                    @if($req->status === 'requested')
                                        @if($req->created_by === auth()->id())
                                            <button class="btn btn-icon btn-sm btn-ghost-dark rounded-2"
                                                    onclick="cancelRequest({{ $req->id }}, '{{ addslashes($req->description) }}')"
                                                    data-bs-toggle="tooltip"
                                                    title="Batalkan">
                                                <i class="ti ti-ban"></i>
                                            </button>
                                        @endif
                                        @can($type . '.request.approve')
                                        <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-success rounded-start-2"
                                                        onclick="approveRequest({{ $req->id }}, '{{ addslashes($req->description) }}')">
                                                    <i class="ti ti-circle-check me-1"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split rounded-end-2" @click="open = !open" :class="{'show': open}" aria-expanded="false" aria-label="Toggle Dropdown">
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end shadow" :class="{'show': open}" x-show="open" style="display: none;" x-transition>
                                                    <button class="dropdown-item text-danger" @click="rejectRequest({{ $req->id }}, '{{ addslashes($req->description) }}'); open = false">
                                                        <i class="ti ti-circle-x me-2"></i> Reject Pengajuan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <x-datatable.empty 
                                title="Belum ada pengajuan" 
                                icon="ti-file-off" 
                                colspan="7" 
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Standardized Components -->
            <x-datatable.batch-bar targetModal="#modal-bulk-delete" />
            <x-datatable.pagination :total="$requests->count()" :perPage="20" />
        </div>
    </div>
</div>

<x-datatable.delete-modal message="Hapus draft <strong id='delete-name'></strong>? Data tidak bisa dikembalikan." />
<x-datatable.bulk-delete-modal title="Hapus Draft Terpilih" message="Anda yakin menghapus semua data draft terpilih?" route="#" />

{{-- Modal Submit --}}
<div class="modal modal-blur fade" id="modal-submit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-send text-success icon-lg mb-2"></i>
                <h3>Konfirmasi Pengajuan</h3>
                <div class="text-secondary">Kirim pengajuan <strong id="submit-name"></strong> ke Admin? Anda tidak akan bisa mengeditnya setelah ini.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form id="form-submit" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Ajukan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Approve --}}
<div class="modal modal-blur fade" id="modal-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-circle-check text-success icon-lg mb-2"></i>
                <h3>Setujui Pengajuan</h3>
                <div class="text-secondary">Setujui pengajuan <strong id="approve-name"></strong>? Sistem akan otomatis membuat draf realisasi dana.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form id="form-approve" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Setujui</button>
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
            <div class="modal-status bg-danger"></div>
            <form id="form-reject" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <i class="ti ti-circle-x text-danger icon-lg mb-2"></i>
                        <h3>Tolak Pengajuan</h3>
                        <div class="text-secondary">Tolak pengajuan <strong id="reject-name"></strong>?</div>
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

{{-- Modal Cancel --}}
<div class="modal modal-blur fade" id="modal-cancel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-dark"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-ban text-dark icon-lg mb-2"></i>
                <h3>Batalkan Pengajuan</h3>
                <div class="text-secondary">Batalkan pengajuan <strong id="cancel-name"></strong>? Data akan tetap tersimpan sebagai riwayat pembatalan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Tutup</a></div>
                        <div class="col">
                            <form id="form-cancel" method="POST">
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
@endsection

@push('scripts')
<script>
    var baseUrl = `{{ url('kas-' . ($type == 'in' ? 'masuk' : 'keluar') . '/pengajuan') }}`;

    function deleteRequest(id, name) {
        document.getElementById('form-delete').action = `${baseUrl}/${id}`;
        document.getElementById('delete-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    function submitRequest(id, name) {
        document.getElementById('form-submit').action = `${baseUrl}/${id}/submit`;
        document.getElementById('submit-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-submit')).show();
    }

    function approveRequest(id, name) {
        document.getElementById('form-approve').action = `${baseUrl}/${id}/approve`;
        document.getElementById('approve-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-approve')).show();
    }

    function rejectRequest(id, name) {
        document.getElementById('form-reject').action = `${baseUrl}/${id}/reject`;
        document.getElementById('reject-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-reject')).show();
    }

    function cancelRequest(id, name) {
        document.getElementById('form-cancel').action = `${baseUrl}/${id}/cancel`;
        document.getElementById('cancel-name').innerText = name;
        new bootstrap.Modal(document.getElementById('modal-cancel')).show();
    }
</script>
<x-datatable.list-init valueNames="[
    { name: 'sort-desc', attr: 'data-desc' },
    { name: 'sort-date', attr: 'data-date' },
    { name: 'sort-amount', attr: 'data-amount' },
    { name: 'sort-priority', attr: 'data-priority' },
    { name: 'sort-status', attr: 'data-status' }
]" :perPage="20" listVar="reqList" />
@endpush
