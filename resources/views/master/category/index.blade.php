@extends('layouts.admin')

@section('title', 'Kategori Kas')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter table-mobile-md card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Nama Kategori</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td class="text-secondary" data-label="No">{{ $loop->iteration }}</td>
                            <td data-label="Nama Kategori">
                                <div class="d-flex py-1 align-items-center">
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-list justify-content-end flex-nowrap">
                                    <button class="btn btn-icon btn-sm btn-outline-primary"
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}')"
                                            title="Edit Kategori">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                    </button>
                                    <button class="btn btn-icon btn-sm btn-outline-danger"
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
        </div>
    </div>
</div>

{{-- Modals remain the same structure for form compatibility --}}
<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('master.categories.store') }}" method="POST">
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

@push('scripts')
<script>
    function editCategory(id, name) {
        const form = document.getElementById('form-edit');
        const input = document.getElementById('edit-name');
        form.action = `{{ url('master/categories') }}/${id}`;
        input.value = name;
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
    });
</script>
@endpush
