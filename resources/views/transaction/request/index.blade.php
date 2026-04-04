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
                    <a href="{{ route($type . '.request.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <x-icon name="plus" />
                        Buat Pengajuan
                    </a>
                    <a href="{{ route($type . '.request.create') }}" class="btn btn-primary d-sm-none btn-icon" aria-label="Buat Pengajuan">
                        <x-icon name="plus" />
                    </a>
                </div>
            </div>

            <!-- Controls -->
            <div class="card-body border-bottom py-3">
                <div class="d-flex">
                    <div class="text-secondary">
                        Tampilkan
                        <div class="mx-2 d-inline-block">
                            <input type="text" class="form-control form-control-sm" value="20" size="3" aria-label="In page count" id="page-count-input">
                        </div>
                        data
                    </div>
                    <div class="ms-auto text-secondary">
                        Pencarian:
                        <div class="ms-2 d-inline-block">
                            <input type="search" class="search form-control form-control-sm" aria-label="Search request">
                        </div>
                    </div>
                </div>
            </div>

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
                                Rp {{ number_format($req->amount, 0, ',', '.') }}
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
                                        <x-icon name="eye" />
                                    </a>
                                    
                                    @if($req->status === 'draft')
                                        <a href="{{ route($type . '.request.edit', $req->id) }}" class="btn btn-icon btn-sm btn-ghost-primary rounded-2" data-bs-toggle="tooltip" title="Edit">
                                            <x-icon name="pencil" />
                                        </a>

                                        <button class="btn btn-icon btn-sm btn-ghost-success rounded-2" 
                                                onclick="submitRequest({{ $req->id }}, '{{ addslashes($req->description) }}')" 
                                                data-bs-toggle="tooltip" 
                                                title="Ajukan">
                                            <x-icon name="send" />
                                        </button>
                                        
                                        <x-datatable.row-action 
                                            type="delete" 
                                            onclick="deleteRequest({{ $req->id }}, '{{ addslashes($req->description) }}')" 
                                            title="Hapus Draft" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon text-secondary">
                                        <x-icon name="file-off" class="icon-lg" />
                                    </div>
                                    <p class="empty-title">Belum ada pengajuan</p>
                                </div>
                            </td>
                        </tr>
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

{{-- Modal Delete --}}
<div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="alert-triangle" class="text-danger icon-lg mb-2" />
                <h3>Konfirmasi Hapus</h3>
                <div class="text-secondary">Hapus draft <strong id="delete-name"></strong>? Data tidak bisa dikembalikan.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form id="form-delete" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Submit --}}
<div class="modal modal-blur fade" id="modal-submit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="send" class="text-success icon-lg mb-2" />
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

{{-- Modal Bulk Delete Placeholder (not fully wired for req yet but needed for component config) --}}
<div class="modal modal-blur fade" id="modal-bulk-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="alert-triangle" class="text-danger icon-lg mb-2" />
                <h3>Hapus Draft Terpilih</h3>
                <div class="text-secondary">Anda yakin menghapus semua data draft terpilih?</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col"><button type="button" class="btn btn-danger w-100" id="btn-bulk-delete-confirm">Hapus Semua</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script>
    function deleteRequest(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('kas-' . ($type == 'in' ? 'masuk' : 'keluar') . '/pengajuan') }}/${id}`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    function submitRequest(id, name) {
        const form = document.getElementById('form-submit');
        const label = document.getElementById('submit-name');
        form.action = `{{ url('kas-' . ($type == 'in' ? 'masuk' : 'keluar') . '/pengajuan') }}/${id}/submit`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-submit')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tableSelector = window.initSmartTableSelection({
            batchBarId: 'batch-action-bar',
            countId: 'selected-count'
        });

        const reqList = new List('table-default', {
            valueNames: [
                { name: 'sort-desc', attr: 'data-desc' },
                { name: 'sort-date', attr: 'data-date' },
                { name: 'sort-amount', attr: 'data-amount' },
                { name: 'sort-priority', attr: 'data-priority' },
                { name: 'sort-status', attr: 'data-status' }
            ],
            page: 20,
            pagination: { innerWindow: 2, outerWindow: 1 }
        });

        reqList.on('updated', function (list) {
            const paginationWrapper = document.getElementById('pagination-wrapper');
            if (list.items.length > 0) {
                paginationWrapper.classList.remove('d-none');
                const startNode = document.getElementById('pagination-info-start');
                if (startNode) startNode.innerText = list.i;
                const endNode = document.getElementById('pagination-info-end');
                if (endNode) endNode.innerText = Math.min(list.i + list.page - 1, list.items.length);
                const totalNode = document.getElementById('pagination-info-total');
                if (totalNode) totalNode.innerText = list.items.length;
            } else {
                paginationWrapper.classList.add('d-none');
            }
            tableSelector.syncCheckboxes();
        });

        document.getElementById('page-count-input')?.addEventListener('change', function(e) {
            reqList.show(1, parseInt(e.target.value) || 20);
        });

        document.getElementById('btn-bulk-delete-confirm')?.addEventListener('click', function() {
            // Placeholder logic if we decide to implement bulk delete for requests
            alert('Fitur hapus masal segera hadir.');
        });
    });
</script>
@endpush
