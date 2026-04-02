@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h3 class="card-title">Daftar Anggota Keluarga</h3>
                <div class="card-actions">
                    <button class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Anggota
                    </button>
                    <button class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add" aria-label="Tambah Anggota">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
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
                            <input type="search" class="search form-control form-control-sm" aria-label="Search user">
                        </div>
                    </div>
                </div>
            </div>

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
                                <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-3" data-label="Aksi">
                                    <button class="btn btn-icon btn-sm btn-ghost-primary"
                                            onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->roles->first()?->name }}')"
                                            title="Edit Profil">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3z" /><path d="M16 5l3 3" /><path d="M9 7.07a7 7 0 0 1 1 13.93a7 7 0 0 1 -1 -13.93z" /></svg>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-ghost-warning"
                                            onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')"
                                            title="Reset Password">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M8 11a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M21 21l-6 -6" /><path d="M5 10c0 -3 2.5 -5 5 -5" /></svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <button class="btn btn-icon btn-sm btn-ghost-danger"
                                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            title="Hapus Anggota">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7a4 4 0 1 0 4 4" /><path d="M3 21v-2c0 -.637 .126 -1.246 .356 -1.801m1.582 -2.398c.326 -.18 .679 -.335 1.054 -.457" /><path d="M12 11a4 4 0 0 0 4 4m2 -2a4 4 0 0 0 -2 -2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M16 19h6" /><path d="M3 3l18 18" /></svg>
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
                        <select class="form-select" name="role" id="edit-role">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                            @endforeach
                        </select>
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
                        <input type="password" class="form-control" name="password" required>
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

{{-- Modal Delete --}}
<div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.707 2.983h16.845a1.989 1.989 0 0 0 1.708 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.415 0z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
                <h3>Konfirmasi Hapus</h3>
                <div class="text-secondary">Anda yakin menghapus <strong id="delete-name"></strong>? Data pengajuan dan transaksi miliknya juga akan dihapus.</div>
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

{{-- Modal Bulk Delete --}}
<div class="modal modal-blur fade" id="modal-bulk-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.707 2.983h16.845a1.989 1.989 0 0 0 1.708 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.415 0z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
                <h3>Hapus User Terpilih</h3>
                <div class="text-secondary">Tindakan ini akan menghapus semua user terpilih berserta datanya. Lanjutkan?</div>
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
    // 1. Helper functions for modals
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
        // Handle Validation Errors Show Modals
        @if ($errors->any())
            @if(old('_method') === 'PUT')
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

        // 2. Initialize Smart Selection
        const tableSelector = window.initSmartTableSelection({
            batchBarId: 'batch-action-bar',
            countId: 'selected-count'
        });

        // 3. Initialize List.js
        const userList = new List('table-default', {
            valueNames: [
                { name: 'sort-name', attr: 'data-name' },
                { name: 'sort-email', attr: 'data-email' },
                { name: 'sort-role', attr: 'data-role' },
                { name: 'sort-date', attr: 'data-date' }
            ],
            page: 20,
            pagination: { innerWindow: 2, outerWindow: 1 }
        });

        // 4. Interface Updates
        userList.on('updated', function (list) {
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
            userList.show(1, parseInt(e.target.value) || 20);
        });

        // 5. Bulk Action Implementation
        document.getElementById('btn-bulk-delete-confirm')?.addEventListener('click', function() {
            const ids = tableSelector.getSelectedIds();
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('master.users.bulk-delete') }}";
            
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
