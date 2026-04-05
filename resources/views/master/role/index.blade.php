@extends('layouts.admin')

@section('title', 'Peran dan Akses')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom">
                <h3 class="card-title">Daftar Peran dan Akses</h3>
                <div class="card-actions">
                    @can('role.create')
                    <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus"></i>
                        Tambah Grup
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Role">
                        <i class="ti ti-plus"></i>
                    </button>
                    @endcan
                </div>
            </div>

            <x-datatable.controls :perPage="20" searchLabel="Search role" />

            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all" id="select-all"></th>
                            <th class="sort" data-sort="sort-name"><button class="table-sort" data-sort="sort-name">Nama Grup</button></th>
                            <th class="sort" data-sort="sort-users"><button class="table-sort" data-sort="sort-users">Jumlah Pengguna</button></th>
                            <th class="sort" data-sort="sort-permissions"><button class="table-sort" data-sort="sort-permissions">Hak Akses</button></th>
                            <th>Visibilitas</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($roles as $role)
                        <tr>
                            <td>
                                @if($role->name !== 'admin')
                                    <input class="form-check-input m-0 align-middle check-item" type="checkbox" value="{{ $role->id }}" aria-label="Select role">
                                @endif
                            </td>
                            <td class="sort-name" data-name="{{ $role->name }}">
                                <div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                        <div class="font-weight-medium @if($role->name === 'admin') text-primary @endif">{{ strtoupper($role->name) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="sort-users" data-users="{{ $role->users_count }}">
                                <span class="badge bg-secondary-lt">{{ $role->users_count }} User</span>
                            </td>
                            <td class="sort-permissions" data-permissions="{{ $role->permissions_count }}">
                                <span class="badge bg-blue-lt">{{ $role->permissions_count }} Permissions</span>
                            </td>
                            <td>
                                @if($role->name === 'admin')
                                    <span class="badge bg-green-lt">Semua Data</span>
                                @elseif(isset($visibilityMap[$role->id]) && count($visibilityMap[$role->id]) > 0)
                                    @php
                                        $watchedNames = \Spatie\Permission\Models\Role::whereIn('id', $visibilityMap[$role->id])->pluck('name');
                                    @endphp
                                    @foreach($watchedNames as $wName)
                                        <span class="badge bg-cyan-lt">{{ strtoupper($wName) }}</span>
                                    @endforeach
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                             <td>
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-2" data-label="Aksi">
                                    @can('role.edit')
                                    <x-datatable.row-action 
                                        type="edit" 
                                        onclick="editRole({{ $role->id }}, '{{ $role->name }}', {{ json_encode($role->permissions->pluck('name')) }}, {{ json_encode($visibilityMap[$role->id] ?? []) }})" 
                                        title="Kelola Izin" />
                                    @endcan
                                    
                                    @if($role->name !== 'admin')
                                    @can('role.delete')
                                    <x-datatable.row-action 
                                        type="delete" 
                                        onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')" 
                                        title="Hapus Grup" />
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
                                        <i class="ti ti-key icon-lg"></i>
                                    </div>
                                    <p class="empty-title">Belum ada Grup Tambahan</p>
                                    <p class="empty-subtitle text-secondary">Ayo buat grup akses baru untuk keluarga Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Standardized Components -->
            <x-datatable.batch-bar targetModal="#modal-bulk-delete" />
            <x-datatable.pagination :total="$roles->count()" :perPage="20" />
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Modal Add --}}
<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('master.roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Buat Grup Akses Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Grup</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Contoh: ANAK_SULUNG" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-label">Salin Hak Akses Dari (Preset)</div>
                        <select class="form-select" name="copy_from_id">
                            <option value="">-- Tanpa Preset (Kosong) --</option>
                            @foreach($roleOptions as $opt)
                                <option value="{{ $opt->id }}">{{ strtoupper($opt->name) }} ({{ $opt->permissions_count }} Hak Akses)</option>
                            @endforeach
                        </select>
                        <small class="text-secondary mt-2 d-block">Pilihan ini akan menduplikasi seluruh izin dan visibilitas dari grup yang dipilih ke grup baru ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Grup</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Permissions + Visibility --}}
<div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Izin Grup: <span id="edit-title-name" class="text-primary text-uppercase"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Grup</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="edit-name" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="hr-text">DATA HAK AKSES (PERMISSIONS)</div>

                    <div class="row">
                        @foreach($allPermissions as $group => $permissions)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-primary-lt py-2 d-flex justify-content-between align-items-center">
                                    <h4 class="card-title text-capitalize m-0">{{ $group }}</h4>
                                    <button type="button" class="btn btn-ghost-primary btn-sm btn-icon border-0" onclick="toggleGroup('{{ $group }}')" title="Pilih Semua">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </div>
                                <div class="card-body p-3 scrollable" style="max-height: 250px;">
                                    @foreach($permissions as $perm)
                                    <label class="form-check mb-2">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" 
                                               class="form-check-input perm-checkbox group-{{ $group }}">
                                        <span class="form-check-label text-capitalize">
                                            {{ str_replace(['.', $group], [' ', ''], $perm->name) }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Section Visibilitas Data --}}
                    <div id="visibility-section">
                        <div class="hr-text">VISIBILITAS DATA</div>
                        <p class="text-secondary mb-3">Grup ini dapat melihat data milik grup berikut (selain datanya sendiri):</p>

                        <div class="row">
                            @foreach($allRoles as $visRole)
                            <div class="col-md-3 col-sm-4 col-6 mb-2">
                                <label class="form-check">
                                    <input type="checkbox" name="visibility[]" value="{{ $visRole->id }}" 
                                           class="form-check-input visibility-checkbox" data-role-id="{{ $visRole->id }}">
                                    <span class="form-check-label text-uppercase">{{ $visRole->name }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        @if($allRoles->isEmpty())
                            <p class="text-secondary text-center py-2">Belum ada grup lain selain Admin.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-datatable.delete-modal title="Hapus Grup?" message="Menghapus grup <strong id='delete-name'></strong> mungkin akan membatasi akses user terkait." />
<x-datatable.bulk-delete-modal title="Hapus Grup Terpilih?" message="Anda yakin menghapus grup yang dipilih? Operasi ini tidak dapat dibatalkan." route="{{ route('master.roles.bulk-delete') }}" />

@endsection

@push('scripts')
<script>
    function editRole(id, name, permissions, visibility) {
        const form = document.getElementById('form-edit');
        const inputId = document.getElementById('edit-id');
        const inputName = document.getElementById('edit-name');
        const titleName = document.getElementById('edit-title-name');
        const visSection = document.getElementById('visibility-section');

        form.action = `{{ url('master/roles') }}/${id}`;
        inputId.value = id;
        inputName.value = name;
        titleName.innerText = name;

        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = permissions.includes(cb.value);
        });

        document.querySelectorAll('.visibility-checkbox').forEach(cb => {
            const roleId = parseInt(cb.dataset.roleId);
            cb.checked = visibility.includes(roleId);
        });

        visSection.style.display = (name === 'admin') ? 'none' : '';
        inputName.disabled = (name === 'admin');

        new bootstrap.Modal(document.getElementById('modal-edit')).show();
    }

    function deleteRole(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('master/roles') }}/${id}`;
        label.innerText = name.toUpperCase();
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    function toggleGroup(group) {
        const checkboxes = document.querySelectorAll(`.group-${group}`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->any())
            @if(session('modal') === 'edit' || old('_method') === 'PUT')
                const editModalId = "{{ old('id') }}";
                if(editModalId) {
                    const editForm = document.getElementById('form-edit');
                    editForm.action = `{{ url('master/roles') }}/${editModalId}`;
                }
                new bootstrap.Modal(document.getElementById('modal-edit')).show();
            @else
                new bootstrap.Modal(document.getElementById('modal-add')).show();
            @endif
        @endif
    });
</script>
<x-datatable.list-init :valueNames="[
    { name: 'sort-name', attr: 'data-name' },
    { name: 'sort-users', attr: 'data-users' },
    { name: 'sort-permissions', attr: 'data-permissions' }
]" :perPage="20" listVar="roleList" />
@endpush
