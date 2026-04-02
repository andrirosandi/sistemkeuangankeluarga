@extends('layouts.admin')

@section('title', 'Kategori Kas')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom">
                <h3 class="card-title">Daftar Kategori</h3>
                <div class="card-actions">
                    <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <x-icon name="plus" />
                        Tambah Kategori
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Kategori">
                        <x-icon name="plus" />
                    </button>
                </div>
            </div>
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
                            <input type="search" class="search form-control form-control-sm" aria-label="Search category">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all" id="select-all"></th>
                            <th class="sort" data-sort="sort-name"><button class="table-sort" data-sort="sort-name">Nama Kategori</button></th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($categories as $category)
                        <tr>
                            <td><input class="form-check-input m-0 align-middle check-item" type="checkbox" value="{{ $category->id }}" aria-label="Select category"></td>
                            <td class="sort-name" data-label="Nama Kategori" data-name="{{ $category->name }}">
                                <div class="d-flex py-1 align-items-center">
                                    <span class="status-dot status-dot-animated me-2" style="background: {{ $category->color ?? '#616876' }}"></span>
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $category->name }}</div>
                                    </div>
                                </div>
                            </td>
                             <td>
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-4" data-label="Aksi">
                                    <button class="btn btn-icon btn-sm btn-ghost-primary"
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->color ?? '#616876' }}')"
                                            title="Edit Kategori">
                                        <x-icon name="pencil" />
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-ghost-danger"
                                            onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                            title="Hapus Kategori">
                                        <x-icon name="trash" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon text-secondary">
                                        <x-icon name="folder-plus" class="icon-lg" />
                                    </div>
                                    <p class="empty-title">Belum ada kategori</p>
                                    <p class="empty-subtitle text-secondary">Ayo buat kategori pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Batch Action Bar -->
            <x-datatable.batch-bar />

            <!-- Pagination Footer -->
            <x-datatable.pagination :total="$categories->count()" :perPage="20" />
        </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('master.categories.store') }}" method="POST" id="form-add">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Contoh: Gaji, Belanja Bulanan" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Identitas</label>
                        <div class="row g-2">
                            @php
                                $palette = [
                                    '#206bc4' => 'blue', '#4299e1' => 'azure', '#4263eb' => 'indigo', '#ae3ec9' => 'purple',
                                    '#d6336c' => 'pink', '#d63939' => 'red', '#f76707' => 'orange', '#f59f00' => 'yellow',
                                    '#74b816' => 'lime', '#2fb344' => 'green', '#0ca678' => 'teal', '#17a2b8' => 'cyan',
                                    '#616876' => 'secondary'
                                ];
                            @endphp
                            @foreach($palette as $hex => $name)
                            <div class="col-auto">
                                <label class="form-colorinput">
                                    <input name="color" type="radio" value="{{ $hex }}" class="form-colorinput-input" {{ $hex === '#616876' ? 'checked' : '' }}>
                                    <span class="form-colorinput-color bg-{{ $name }}"></span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-secondary d-block mt-2">Pilih warna penanda untuk kategori ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="edit-name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Identitas</label>
                        <div class="row g-2">
                            @foreach($palette as $hex => $name)
                            <div class="col-auto">
                                <label class="form-colorinput">
                                    <input name="color" type="radio" value="{{ $hex }}" class="form-colorinput-input edit-color-radio">
                                    <span class="form-colorinput-color bg-{{ $name }}"></span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-secondary d-block mt-2">Gunakan warna kontras agar mudah dibedakan di grafik.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="alert-triangle" class="text-danger icon-lg mb-2" />
                <h3>Konfirmasi Hapus</h3>
                <div class="text-secondary">Hapus kategori <strong id="delete-name"></strong>? Data yang terkait mungkin akan error.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100"><div class="row"><div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div><div class="col"><form id="form-delete" method="POST">@csrf @method('DELETE') <button type="submit" class="btn btn-danger w-100">Hapus</button></form></div></div></div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-bulk-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.707 2.983h16.845a1.989 1.989 0 0 0 1.708 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.415 0z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
                <h3>Hapus Terpilih</h3>
                <div class="text-secondary">Anda yakin menghapus kategori terpilih? Tindakan ini tidak dapat dibatalkan.</div>
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

    function editCategory(id, name, color) {
        const form = document.getElementById('form-edit');
        const inputId = document.getElementById('edit-id');
        const inputName = document.getElementById('edit-name');
        
        form.action = `{{ url('master/categories') }}/${id}`;
        inputId.value = id;
        inputName.value = name;
        
        const colorRadios = document.querySelectorAll('.edit-color-radio');
        colorRadios.forEach(radio => { radio.checked = (radio.value === color); });
        new bootstrap.Modal(document.getElementById('modal-edit')).show();
    }
    function deleteCategory(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('master/categories') }}/${id}`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Handle Validation Errors Show Modals
        @if ($errors->any())
            @if(old('_method') === 'PUT')
                const editModalId = "{{ old('id') }}";
                if(editModalId) {
                    const editForm = document.getElementById('form-edit');
                    editForm.action = `{{ url('master/categories') }}/${editModalId}`;
                }
                const editModal = new bootstrap.Modal(document.getElementById('modal-edit'));
                editModal.show();
            @else
                const addModal = new bootstrap.Modal(document.getElementById('modal-add'));
                addModal.show();
            @endif
        @endif

        // 1. Initialize Smart Table Selection Handler
        const tableSelector = window.initSmartTableSelection({
            batchBarId: 'batch-action-bar',
            countId: 'selected-count'
        });

        // 2. Initialize List.js
        const categoryList = new List('table-default', {
            valueNames: [{ name: 'sort-name', attr: 'data-name' }],
            page: 20,
            pagination: { innerWindow: 2, outerWindow: 1 }
        });

        // 3. Sync List.js with Selection & Pagination Info
        categoryList.on('updated', function (list) {
            const paginationWrapper = document.getElementById('pagination-wrapper');
            if (list.items.length > 0) {
                paginationWrapper.classList.remove('d-none');
                document.getElementById('pagination-info-start').innerText = list.i;
                document.getElementById('pagination-info-end').innerText = Math.min(list.i + list.page - 1, list.items.length);
                document.getElementById('pagination-info-total').innerText = list.items.length;
            } else {
                paginationWrapper.classList.add('d-none');
            }
            
            // Critical: Re-sync checkmarks when page changes
            tableSelector.syncCheckboxes();
        });

        // Handler for page count items
        document.getElementById('page-count-input')?.addEventListener('change', function(e) {
            categoryList.show(1, parseInt(e.target.value) || 20);
        });

        // Bulk Delete Action
        document.getElementById('btn-bulk-delete-confirm')?.addEventListener('click', function() {
            const ids = tableSelector.getSelectedIds();
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('master.categories.bulk-delete') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endpush
