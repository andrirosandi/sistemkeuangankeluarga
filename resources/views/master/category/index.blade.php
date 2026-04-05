@extends('layouts.admin')

@section('title', 'Kategori Kas')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom">
                <h3 class="card-title">Daftar Kategori</h3>
                <div class="card-actions">
                    @can('category.create')
                    <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus"></i>
                        Tambah Kategori
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Kategori">
                        <i class="ti ti-plus"></i>
                    </button>
                    @endcan
                </div>
            </div>
            <x-datatable.controls :perPage="20" searchLabel="Search category" />
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
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-2" data-label="Aksi">
                                    @can('category.edit')
                                    <x-datatable.row-action 
                                        type="edit" 
                                        onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->color ?? '#616876' }}')" 
                                        title="Edit Kategori" />
                                    @endcan
                                    
                                    @can('category.delete')
                                    <x-datatable.row-action 
                                        type="delete" 
                                        onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" 
                                        title="Hapus Kategori" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                            <x-datatable.empty 
                                title="Belum ada kategori" 
                                icon="ti-folder-plus" 
                                colspan="3"
                                subtitle="Ayo buat kategori pertama Anda."
                            />
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

<x-datatable.delete-modal message="Hapus kategori <strong id='delete-name'></strong>? Data yang terkait mungkin akan error." />

<x-datatable.bulk-delete-modal
    title="Hapus Terpilih"
    message="Anda yakin menghapus kategori terpilih? Tindakan ini tidak dapat dibatalkan."
    route="{{ route('master.categories.bulk-delete') }}"
/>
@endsection


@push('scripts')
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
    });
</script>
@endpush

<x-datatable.list-init
    valueNames="[{ name: 'sort-name', attr: 'data-name' }]"
    :perPage="20"
    listVar="categoryList"
/>
