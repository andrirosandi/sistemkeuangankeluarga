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
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Kategori
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Kategori">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </button>
                </div>
            </div>
            <div class="card-body border-bottom py-3">
                <div class="d-flex">
                    <div class="text-secondary">
                        Tampilkan
                        <div class="mx-2 d-inline-block">
                            <input type="text" class="form-control form-control-sm" value="8" size="3" aria-label="In page count" id="page-count-input">
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
                <table class="table table-vcenter table-mobile-md card-table">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all invoice"></th>
                            <th class="sort" data-sort="sort-name"><button class="table-sort" data-sort="sort-name">Nama Kategori</button></th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($categories as $category)
                        <tr>
                            <td><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select category"></td>
                            <td class="sort-name" data-label="Nama Kategori" data-name="{{ $category->name }}">
                                <div class="d-flex py-1 align-items-center">
                                    <span class="status-dot status-dot-animated me-2" style="background: {{ $category->color ?? '#616876' }}"></span>
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-end gap-4">
                                    <button class="btn btn-icon btn-sm btn-ghost-primary"
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->color ?? '#616876' }}')"
                                            title="Edit Kategori">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-ghost-danger"
                                            onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                            title="Hapus Kategori">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.983 8.978c3.955 -.182 7.017 -1.446 7.017 -2.978c0 -1.657 -3.582 -3 -8 -3c-1.661 0 -3.204 .19 -4.483 .515m-2.783 1.228c-.471 .382 -.734 .808 -.734 1.257c0 1.22 1.944 2.271 4.734 2.74" /><path d="M4 6v6c0 1.657 3.582 3 8 3c.986 0 1.93 -.067 2.802 -.19m3.187 -.814c1.25 -.524 2.011 -1.223 2.011 -1.996v-6" /><path d="M4 12v6c0 1.657 3.582 3 8 3c3.217 0 5.991 -.712 7.231 -1.703" /><path d="M3 3l18 18" /></svg>
                                    </div>
                                    <p class="empty-title">Kategori Kosong</p>
                                    <p class="empty-subtitle text-secondary">Mulai tambahkan kategori pengeluaran/pemasukan Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center border-top">
                <p class="m-0 text-secondary d-none d-sm-block">Menampilkan <span id="pagination-info-start">1</span> sampai <span id="pagination-info-end">8</span> dari <span id="pagination-info-total">{{ $categories->count() }}</span> data</p>
                <div class="pagination m-0 ms-auto"></div>
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
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="edit-name" required>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.707 2.983h16.845a1.989 1.989 0 0 0 1.708 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.415 0z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
                <h3>Konfirmasi Hapus</h3>
                <div class="text-secondary">Hapus kategori <strong id="delete-name"></strong>? Data yang terkait mungkin akan error.</div>
            </div>
            <div class="modal-footer">
                <div class="w-100"><div class="row"><div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div><div class="col"><form id="form-delete" method="POST">@csrf @method('DELETE') <button type="submit" class="btn btn-danger w-100">Hapus</button></form></div></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styling List.js dynamic pagination to match Tabler */
    .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
    }
    .pagination li {
        margin: 0 2px;
    }
    .pagination li a {
        display: block;
        padding: 0.35rem 0.65rem;
        color: var(--tblr-secondary);
        text-decoration: none;
        background-color: var(--tblr-bg-surface);
        border: 1px solid var(--tblr-border-color);
        border-radius: var(--tblr-border-radius);
        cursor: pointer;
    }
    .pagination li.active a {
        color: var(--tblr-primary-fg);
        background-color: var(--tblr-primary);
        border-color: var(--tblr-primary);
    }
    .pagination li a:hover {
        background-color: var(--tblr-bg-surface-secondary);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script>
    function editCategory(id, name, color) {
        const form = document.getElementById('form-edit');
        const inputName = document.getElementById('edit-name');
        form.action = `{{ url('master/categories') }}/${id}`;
        inputName.value = name;
        
        const colorRadios = document.querySelectorAll('.edit-color-radio');
        colorRadios.forEach(radio => {
            radio.checked = (radio.value === color);
        });

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
        @if ($errors->any())
            var addModal = new bootstrap.Modal(document.getElementById('modal-add'));
            addModal.show();
        @endif

        // Initialize List.js
        const categoryList = new List('table-default', {
            valueNames: [
                { name: 'sort-name', attr: 'data-name' }
            ],
            page: 8,
            pagination: {
                innerWindow: 2,
                outerWindow: 1
            }
        });

        // Update info on list update
        categoryList.on('updated', function (list) {
            const start = list.i;
            const end = Math.min(list.i + list.page - 1, list.items.length);
            const total = list.items.length;
            
            document.getElementById('pagination-info-start').innerText = start;
            document.getElementById('pagination-info-end').innerText = end;
            document.getElementById('pagination-info-total').innerText = total;
        });

        // Handle page count changes
        document.getElementById('page-count-input')?.addEventListener('change', function(e) {
            const count = parseInt(e.target.value) || 8;
            categoryList.show(1, count);
        });
    });
</script>
@endpush
