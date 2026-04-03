@extends('layouts.admin')

@section('title', 'Daftar Template Transaksi')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Template Transaksi</h3>
                <div class="card-actions">
                    <a href="{{ route('master.templates.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i> Tambah Template
                    </a>
                </div>
            </div>
            
            <div class="card-body border-bottom py-3">
                <form action="{{ route('master.templates.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                    <div class="input-icon" style="min-width: 250px;">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari template...">
                        <span class="input-icon-addon">
                            <i class="ti ti-search"></i>
                        </span>
                    </div>
                    
                    <select name="type" class="form-select" style="max-width: 200px;">
                        <option value="">Semua Jenis</option>
                        <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Pemasukan (Masuk)</option>
                        <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Pengeluaran (Keluar)</option>
                    </select>

                    <button type="submit" class="btn btn-icon btn-outline-secondary" title="Cari">
                        <i class="ti ti-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'type']))
                        <a href="{{ route('master.templates.index') }}" class="btn btn-icon btn-outline-danger" title="Reset">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap datatable">
                    <thead>
                        <tr>
                            <th class="w-1">No.</th>
                            <th>Nama Template</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th class="text-end">Estimasi Total</th>
                            <th>Dibuat Oleh</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><span class="text-secondary">{{ ($templates->currentPage() - 1) * $templates->perPage() + $loop->iteration }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $template->description }}</div>
                                    <div class="text-secondary small">{{ $template->details_count ?? $template->details()->count() }} item rincian</div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $template->category->color ?? '#6c757d' }}"></span>
                                    {{ $template->category->name }}
                                </td>
                                <td>
                                    @if($template->trans_code == 1)
                                        <span class="badge bg-green-lt">Pemasukan</span>
                                    @else
                                        <span class="badge bg-red-lt">Pengeluaran</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    Rp {{ number_format($template->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-xs rounded me-2" style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
                                            {{ strtoupper(substr($template->creator->name ?? '?', 0, 1)) }}
                                        </span>
                                        <div class="small">
                                            <div class="fw-medium text-body">{{ $template->creator->name ?? 'System' }}</div>
                                            <div class="text-secondary">{{ $template->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('master.templates.edit', $template->id) }}" class="btn btn-icon btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('master.templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-outline-danger" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="ti ti-clipboard-off fs-1"></i>
                                        </div>
                                        <p class="empty-title">Tidak ada template ditemukan</p>
                                        <p class="empty-subtitle text-secondary">
                                            Mulailah dengan membuat template transaksi pertama Anda.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($templates->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary d-none d-lg-block">
                        Menampilkan <span>{{ $templates->firstItem() }}</span> sampai <span>{{ $templates->lastItem() }}</span> dari <span>{{ $templates->total() }}</span> entri
                    </p>
                    <div class="ms-auto">
                        {{ $templates->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
