@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('page-header')
<div class="col">
    <h2 class="page-title">Dashboard</h2>
    <div class="text-secondary mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
</div>
@endsection

@section('content')

{{-- Welcome bar --}}
<div class="card mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="avatar avatar-lg bg-blue text-white rounded">
            <i class="ti ti-user" style="font-size:24px"></i>
        </span>
        <div>
            <h3 class="mb-0">Hai, {{ auth()->user()->name }}!</h3>
            <div class="text-secondary">Selamat datang di Sistem Keuangan Keluarga.</div>
        </div>
        @canany(['in.request.create', 'out.request.create'])
        <div class="ms-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-request">
                <i class="ti ti-plus me-1"></i> Buat Pengajuan Baru
            </button>
        </div>
        @endcanany
    </div>
</div>

{{-- Balance Cards --}}
@can('dashboard.system.balance')
    @include('dashboard.balance-cards')
@endcan

{{-- ═══ Row: Transaksi Terkini ═══ --}}
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.recent')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'recent',
        'title'              => 'Transaksi Terkini',
        'icon'               => 'history',
        'apiUrl'             => route('api.dashboard.recent'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => true,
        'categories'         => $categories,
        'fullWidth'          => true,
    ])
    @endcan
</div>

@endsection

{{-- Modal: Pilih Kas Masuk / Kas Keluar --}}
@canany(['in.request.create', 'out.request.create'])
<div class="modal modal-blur fade" id="modal-create-request" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Pengajuan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">Pilih jenis pengajuan:</div>
                <div class="row g-2">
                    @can('in.request.create')
                    <div class="col-12">
                        <a href="{{ route('in.request.create') }}" class="btn btn-success w-100">
                            <i class="ti ti-arrow-down-left me-1"></i> Kas Masuk
                        </a>
                    </div>
                    @endcan
                    @can('out.request.create')
                    <div class="col-12">
                        <a href="{{ route('out.request.create') }}" class="btn btn-danger w-100">
                            <i class="ti ti-arrow-up-right me-1"></i> Kas Keluar
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endcanany

@push('scripts')
<script>
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// ═══ Balance Cards Component ═══
function dashboardBalance() {
    return {
        data: { begin: 0, totalIn: 0, totalOut: 0, ending: 0 },
        loading: true,
        error: false,
        init() {
            fetch('{{ route('api.dashboard.balance') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(d => { this.data = d; this.loading = false; })
            .catch(() => { this.loading = false; this.error = true; });
        },
        formatRupiah
    };
}

// ═══ HTML Fragment Widget Component ═══
function dashboardWidget(config) {
    return {
        widgetId: config.widgetId,
        apiUrl: config.apiUrl,
        scope: config.defaultScope,
        categoryId: '',
        scopes: config.scopes,
        showCategoryFilter: config.showCategoryFilter,
        categories: config.categories,
        html: '',
        loading: true,
        error: false,
        init() {
            this.loadContent();
            this.$watch('scope', () => this.loadContent());
            this.$watch('categoryId', () => this.loadContent());
        },
        loadContent() {
            this.loading = true;
            this.error = false;
            let url = new URL(this.apiUrl);
            url.searchParams.set('scope', this.scope);
            if (this.categoryId) {
                url.searchParams.set('category_id', this.categoryId);
            } else {
                url.searchParams.delete('category_id');
            }
            fetch(url.toString(), {
                headers: { 'Accept': 'text/html' }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(html => { this.html = html; this.loading = false; })
            .catch(() => { this.html = ''; this.loading = false; this.error = true; });
        }
    };
}
</script>
@endpush
