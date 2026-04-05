@extends('layouts.admin')

@section('title', 'Template Transaksi')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card" id="table-default">
            <div class="card-header border-bottom">
                <h3 class="card-title">Daftar Template Rutin</h3>
                <div class="card-actions">
                    @can('template.create')
                    <a href="{{ route('master.templates.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="ti ti-plus"></i>
                        Tambah Template
                    </a>
                    <a href="{{ route('master.templates.create') }}" class="btn btn-primary d-sm-none btn-icon" aria-label="Tambah Template">
                        <i class="ti ti-plus"></i>
                    </a>
                    @endcan
                </div>
            </div>
            
            <x-datatable.controls :perPage="10" searchLabel="Cari template" />

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
                                    @uang($template->amount)
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
                                        @can('template.edit')
                                        <x-datatable.row-action 
                                            type="edit" 
                                            onclick="window.location.href='{{ route('master.templates.edit', $template->id) }}'"
                                            title="Edit Template" />
                                        @endcan
                                        
                                        @can('template.delete')
                                        <x-datatable.row-action 
                                            type="delete" 
                                            onclick="deleteTemplate({{ $template->id }}, '{{ $template->description }}')" 
                                            title="Hapus Template" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-datatable.empty 
                                title="Belum ada template" 
                                icon="ti-clipboard-off" 
                                colspan="7"
                                subtitle="Mulai buat template rutin Anda untuk mempercepat input transaksi."
                            />
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

<x-datatable.delete-modal message="Hapus template <strong id='delete-name'></strong>? Data rincian di dalamnya juga akan terhapus." />
<x-datatable.bulk-delete-modal message="Anda yakin menghapus semua template yang dipilih?" route="{{ route('master.templates.bulk-delete') }}" confirmId="confirm-bulk-delete" />

@endsection

@push('scripts')
<script>
    function deleteTemplate(id, name) {
        const form = document.getElementById('form-delete');
        const label = document.getElementById('delete-name');
        form.action = `{{ url('master/templates') }}/${id}`;
        label.innerText = name;
        new bootstrap.Modal(document.getElementById('modal-delete')).show();
    }
</script>
<x-datatable.list-init valueNames="[
    { name: 'sort-name', attr: 'data-name' },
    { name: 'sort-category', attr: 'data-category' },
    { name: 'sort-type', attr: 'data-type' },
    { name: 'sort-amount', attr: 'data-amount' }
]" :perPage="10" listVar="templateList" />
@endpush
