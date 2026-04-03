@extends('layouts.admin')

@section('title', 'Template Transaksi')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom">
                <h3 class="card-title">Daftar Template Rutin</h3>
                <div class="card-actions">
                    <a href="{{ route('master.templates.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <x-icon name="plus" />
                        Tambah Template
                    </a>
                    <a href="{{ route('master.templates.create') }}" class="btn btn-primary d-sm-none btn-icon" aria-label="Tambah Template">
                        <x-icon name="plus" />
                    </a>
                </div>
            </div>
            
            <div class="card-body border-bottom py-3">
                <div class="d-flex">
                    <div class="text-secondary">
                        Tampilkan
                        <div class="mx-2 d-inline-block">
                            <input type="text" class="form-control form-control-sm" value="10" size="3" id="page-count-input">
                        </div>
                        data
                    </div>
                    <div class="ms-auto text-secondary">
                        Pencarian:
                        <div class="ms-2 d-inline-block">
                            <input type="search" class="search form-control form-control-sm" aria-label="Cari template">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" id="select-all"></th>
                            <th class="sort" data-sort="sort-name"><button class="table-sort" data-sort="sort-name">Nama Template</button></th>
                            <th class="sort" data-sort="sort-category"><button class="table-sort" data-sort="sort-category">Kategori</button></th>
                            <th class="sort" data-sort="sort-type"><button class="table-sort" data-sort="sort-type">Jenis</button></th>
                            <th class="sort text-end" data-sort="sort-amount"><button class="table-sort" data-sort="sort-amount">Estimasi Total</button></th>
                            <th>Pembuat</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($templates as $template)
                            <tr>
                                <td><input class="form-check-input m-0 align-middle check-item" type="checkbox" value="{{ $template->id }}"></td>
                                <td class="sort-name" data-name="{{ $template->description }}">
                                    <div class="fw-bold text-body text-truncate" style="max-width: 250px;">{{ $template->description }}</div>
                                    <div class="text-secondary small">{{ $template->details()->count() }} item rincian</div>
                                </td>
                                <td class="sort-category" data-category="{{ $template->category->name ?? 'Lain-lain' }}">
                                    <div class="d-flex align-items-center">
                                        <span class="status-dot status-dot-animated me-2" style="background: {{ $template->category->color ?? '#616876' }}"></span>
                                        <span>{{ $template->category->name ?? 'Lain-lain' }}</span>
                                    </div>
                                </td>
                                <td class="sort-type" data-type="{{ $template->trans_code }}">
                                    @if($template->trans_code == 1)
                                        <span class="badge bg-green-lt">Pemasukan</span>
                                    @else
                                        <span class="badge bg-red-lt">Pengeluaran</span>
                                    @endif
                                </td>
                                <td class="sort-amount text-end fw-bold" data-amount="{{ $template->amount }}">
                                    Rp {{ number_format($template->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-xs rounded me-2" style="background: linear-gradient(135deg, #4263eb, #206bc4);">
                                            {{ strtoupper(substr($template->creator->name ?? '?', 0, 1)) }}
                                        </span>
                                        <div class="small">
                                            <div class="text-body">{{ $template->creator->name ?? 'System' }}</div>
                                            <div class="text-secondary small text-nowrap">{{ $template->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <x-datatable.row-action 
                                            type="edit" 
                                            href="{{ route('master.templates.edit', $template->id) }}"
                                            title="Edit Template" />
                                        
                                        <x-datatable.row-action 
                                            type="delete" 
                                            onclick="deleteTemplate({{ $template->id }}, '{{ $template->description }}')" 
                                            title="Hapus Template" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <x-icon name="clipboard-off" class="icon-lg" />
                                        </div>
                                        <p class="empty-title">Belum ada template</p>
                                        <p class="empty-subtitle text-secondary">Mulai buat template rutin Anda untuk mempercepat input transaksi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Smart Table Extras -->
            <x-datatable.batch-bar targetModal="#modal-bulk-delete" />
            <x-datatable.pagination :total="$templates->count()" :perPage="10" />
        </div>
    </div>
</div>

{{-- Delete Single Confirmation Modal --}}
<div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="alert-triangle" class="text-danger icon-lg mb-2" />
                <h3>Konfirmasi Hapus</h3>
                <div class="text-secondary">Hapus template <strong id="delete-name"></strong>? Data rincian di dalamnya juga akan terhapus.</div>
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

{{-- Bulk Delete Confirmation Modal --}}
<div class="modal modal-blur fade" id="modal-bulk-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="alert-triangle" class="text-danger icon-lg mb-2" />
                <h3>Hapus Terpilih</h3>
                <div class="text-secondary">Anda yakin menghapus semua template yang dipilih?</div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col"><button type="button" class="btn btn-danger w-100" id="confirm-bulk-delete">Hapus Semua</button></div>
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
    function deleteTemplate(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('master/templates') }}/${id}`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Initialize List.js
        const templateList = new List('table-default', {
            valueNames: [
                { name: 'sort-name', attr: 'data-name' },
                { name: 'sort-category', attr: 'data-category' },
                { name: 'sort-type', attr: 'data-type' },
                { name: 'sort-amount', attr: 'data-amount' }
            ],
            page: 10,
            pagination: { innerWindow: 2, outerWindow: 1 }
        });

        // 2. Initialize Selection Handler
        const tableSelector = window.initSmartTableSelection({
            batchBarId: 'batch-action-bar',
            countId: 'selected-count'
        });

        // 3. Sync events
        templateList.on('updated', function (list) {
            const paginationWrapper = document.getElementById('pagination-wrapper');
            if (list.items.length > 0) {
                paginationWrapper.classList.remove('d-none');
                document.getElementById('pagination-info-start').innerText = list.i;
                document.getElementById('pagination-info-end').innerText = Math.min(list.i + list.page - 1, list.items.length);
                document.getElementById('pagination-info-total').innerText = list.items.length;
            } else {
                paginationWrapper.classList.add('d-none');
            }
            tableSelector.syncCheckboxes();
        });

        document.getElementById('page-count-input')?.addEventListener('change', function(e) {
            templateList.show(1, parseInt(e.target.value) || 10);
        });

        // Bulk Delete Confirm
        document.getElementById('confirm-bulk-delete')?.addEventListener('click', function() {
            const ids = tableSelector.getSelectedIds();
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('master.templates.bulk-delete') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endpush
