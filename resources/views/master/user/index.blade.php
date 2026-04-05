@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h3 class="card-title">Daftar Anggota Keluarga</h3>
                <div class="card-actions">
                    @can('user.create')
                    <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus"></i>
                        Tambah Anggota
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Anggota">
                        <i class="ti ti-plus"></i>
                    </button>
                    @endcan
                </div>
            </div>

            <x-datatable.controls :perPage="20" searchLabel="Search user" />

            <div class="table-responsive">
                <table class="table table-vcenter card-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" aria-label="Select all" id="select-all"></th>
                            <th class="sort" data-sort="sort-name"><button class="table-sort" data-sort="sort-name">Nama Anggota</button></th>
                            <th class="sort" data-sort="sort-email"><button class="table-sort" data-sort="sort-email">Email</button></th>
                            <th class="sort" data-sort="sort-role"><button class="table-sort" data-sort="sort-role">Role</button></th>
                            <th class="sort" data-sort="sort-date"><button class="table-sort" data-sort="sort-date">Bergabung</button></th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($users as $user)
                        <tr>
                            <td><input class="form-check-input m-0 align-middle check-item" type="checkbox" value="{{ $user->id }}" aria-label="Select user"></td>
                            <td class="sort-name" data-name="{{ $user->name }}">
                                <div class="d-flex py-1 align-items-center">
                                    <span class="avatar avatar-sm me-2 rounded-circle">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <div class="text-secondary small">Ini Anda</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="sort-email text-secondary" data-email="{{ $user->email }}">{{ $user->email }}</td>
                            <td class="sort-role" data-role="{{ $user->roles->first()?->name ?? 'None' }}">
                                @foreach($user->roles as $role)
                                    <span class="badge {{ $role->name === 'admin' ? 'bg-primary-lt' : 'bg-secondary-lt' }} text-uppercase">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="sort-date text-secondary" data-date="{{ $user->created_at?->timestamp ?? 0 }}">
                                {{ $user->created_at?->format('d M Y') }}
                            </td>
                             <td>
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-2" data-label="Aksi">
                                    @can('user.edit')
                                    <x-datatable.row-action 
                                        type="edit" 
                                        onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->roles->first()?->name }}')" 
                                        title="Edit Profil" />
                                    @endcan
                                    
                                    @can('user.reset-password')
                                    <x-datatable.row-action 
                                        type="reset" 
                                        onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')" 
                                        title="Reset Password" />
                                    @endcan
                                    
                                    @if($user->id !== auth()->id())
                                    @can('user.delete')
                                    <x-datatable.row-action 
                                        type="delete" 
                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" 
                                        title="Hapus Anggota" />
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
                                        <i class="ti ti-users-off icon-lg"></i>
                                    </div>
                                    <p class="empty-title">Data Pengguna Kosong</p>
                                    <p class="empty-subtitle text-secondary">Ayo undang anggota keluarga Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Standardized Components -->
            <x-datatable.batch-bar targetModal="#modal-bulk-delete" />
            <x-datatable.pagination :total="$users->count()" :perPage="20" />
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Modal Add --}}
<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('master.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Keluarga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Role / Hak Akses</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ strtoupper($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Konfirmasi Password</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profil Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="edit-name" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="edit-email" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Role / Hak Akses</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role" id="edit-role">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

{{-- Modal Reset Password --}}
<div class="modal modal-blur fade" id="modal-reset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-reset" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password: <span id="reset-user-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">Password baru harus memiliki minimal 8 karakter.</p>
                    <div class="mb-3">
                        <label class="form-label required">Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-datatable.delete-modal message="Anda yakin menghapus <strong id='delete-name'></strong>? Data pengajuan dan transaksi miliknya juga akan dihapus." />
<x-datatable.bulk-delete-modal title="Hapus User Terpilih" message="Tindakan ini akan menghapus semua user terpilih berserta datanya. Lanjutkan?" route="{{ route('master.users.bulk-delete') }}" />

@endsection

@push('scripts')
<script>
    function editUser(id, name, email, role) {
        const form = document.getElementById('form-edit');
        const inputId = document.getElementById('edit-id');
        const inputName = document.getElementById('edit-name');
        const inputEmail = document.getElementById('edit-email');
        const selectRole = document.getElementById('edit-role');

        form.action = `{{ url('master/users') }}/${id}`;
        inputId.value = id;
        inputName.value = name;
        inputEmail.value = email;
        selectRole.value = role;

        new bootstrap.Modal(document.getElementById('modal-edit')).show();
    }

    function resetPassword(id, name) {
        const form = document.getElementById('form-reset');
        const spanName = document.getElementById('reset-user-name');
        form.action = `{{ url('master/users') }}/${id}/reset-password`;
        spanName.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-reset')).show();
    }

    function deleteUser(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('master/users') }}/${id}`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->any())
            @if(session('modal') === 'reset')
                new bootstrap.Modal(document.getElementById('modal-reset')).show();
            @elseif(session('modal') === 'edit' || old('_method') === 'PUT')
                const editModalId = "{{ old('id') }}";
                if(editModalId) {
                    const editForm = document.getElementById('form-edit');
                    editForm.action = `{{ url('master/users') }}/${editModalId}`;
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
    { name: 'sort-email', attr: 'data-email' },
    { name: 'sort-role', attr: 'data-role' },
    { name: 'sort-date', attr: 'data-date' }
]" :perPage="20" listVar="userList" />
@endpush
